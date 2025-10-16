<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
# use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Queueable;
use App\Models\PageRequest;
use App\Models\TableData;
use App\Models\Visualization;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;

class ProcessPageRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The PageRequest instance.
     * @var \App\Models\PageRequest
     */
    protected $pageRequest;

    /**
     * Create a new job instance.
     * @param \App\Models\PageRequest $pageRequest
     * @return void
     */
    public function __construct(PageRequest $pageRequest)
    {
        $this->pageRequest = $pageRequest;
    }

    /**
     * Extract the first floating point number from a string.
     * Examples:
     *  - "1.46 m (4 ft 9+1⁄4 in)" => 1.46
     *  - "3,200%" => 3200
     *  - "N/A" => null
     *
     * @param string $value
     * @return float|null
     */
    private function extractFirstNumber(string $value): ?float
    {
        // Remove commas for thousands separator
        $cleaned = str_replace(',', '', $value);

        // Use regex to find first number (integer or float), possibly with decimal point
        if (preg_match('/[-+]?\d*\.?\d+/', $cleaned, $matches)) {
            return floatval($matches[0]);
        }

        return null;
    }

    /**
     * Execute the job.
     *
     * Workflow:
     * 1. Update status to 'processing'
     * 2. Fetch Wikipedia page content
     * 3. Parse tables and identify the numeric column
     * 4. Extract table data and save as TableData records
     * 5. Generate visualizations (via python script or internal logic)
     * 6. Store visualization images and create Visualization records
     * 7. Update status to 'completed' or 'failed' in case of exception
     *
     * @return void
     */
    public function handle(): void
    {
        $this->pageRequest->update(['status' => 'processing']);

        try {
            $client = new Client([
                'timeout' => 100,
                'headers' => ['User-Agent' => 'PageRequestBot/1.0(+https://yourdomain.com/)'],
            ]);

            try {
                $response = $client->get($this->pageRequest->url);
                $html = (string) $response->getBody();
            } catch (Exception $e) {
                \Log::error("HTML request failed for PageRequest ID" . $this->pageRequest->id. ': ' . $e->getMessage());
                $this->pageRequest->update(['status' => 'failed']);
                return;
            }

            $crawler = new Crawler($html);

            $tables = $crawler->filter('#mw-content-text table.wikitable');
            if ($tables->count() === 0) {
                $tables = $crawler->filter('#mw-content-text table');
            }

            if ($tables->count() === 0) {
                \Log::warning("No tables found for PageRequest ID {$this->pageRequest->id}");
                $this->pageRequest->update(['status' => 'failed']);
                return;
            }

            $this->pageRequest->tableData()->delete();
            $this->pageRequest->visualizations()->delete();


            $tableIndex = 0;
            $visualizationsCreated = 0;
            $errors = [];

            foreach ($tables as $tableElement) {
                $tableIndex++;
                try {
                    $tableCrawler = new Crawler($tableElement);

                    $headers = [];
                    $headerCells = $tableCrawler->filter('tr')->first()->filter('th');

                    foreach ($headerCells as $cell) {
                        $headers[] = trim($cell->textContent);
                    }

                    if (empty($headers)) {
                        continue;
                    }

                    $rowsData = [];

                    $rows = $tableCrawler->filter('tr')->slice(1);

                    foreach ($rows as $rowElement) {
                        $rowCrawler = new Crawler($rowElement);
                        $cells = $rowCrawler->filter('td');

                        $row = [];
                        foreach ($cells as $i => $cellElement) {
                            $cellText = trim($cellElement->textContent);
                            $header = $headers[$i] ?? "Column{$i}";
                            $row[$header] = $cellText;
                        }

                        if ($row) {
                            $rowsData[] = $row;
                        }
                    }

                    if (empty($rowsData)) {
                        continue;
                    }

                    $numericColumn = null;
                    foreach($headers as $header) {
                        $numericCount = 0;
                        $totalCount = 0;
                        foreach($rowsData as $row) {
                            if(!isset($row[$header])) {
                                continue;
                            }
                            $value = $row[$header];
                            $number = $this->extractFirstNumber($value);
                            if ($number !== null) {
                                $numericCount++;
                            }
                            $totalCount++;
                        }

                        if ($totalCount > 0 && ($numericCount / $totalCount) >= 0.8) {
                            $numericColumn = $header;
                            break;
                        }
                    }

                    if ($numericColumn === null) {
                        continue;
                    }

                    $labelColumn = null;
                    foreach($headers as $header) {
                        if ($header === $numericColumn) {
                            continue;
                        }

                        $nonNumericCount = 0;
                        $totalCount = 0;
                        foreach($rowsData as $row) {
                            if (!isset($row[$header])) {
                                continue;
                            }
                            $value = $row[$header];
                            $number = $this->extractFirstNumber($value);
                            if ($number === null) {
                                $nonNumericCount++;
                            }
                            $totalCount++;
                        }

                        if ($totalCount > 0 && ($nonNumericCount / $totalCount) >= 0.8) {
                            $labelColumn = $header;
                            break;
                        }
                    }

                    // Fallback: if no label column found, use row index as label
                    if ($labelColumn === null) {
                        $labelColumn = 'Index';
                    }

                    // Prepare arrays for visualization
                    $labels = [];
                    $values = [];

                    // Save table data records
                    foreach ($rowsData as $index => $row) {
                        $labelValue = $labelColumn === 'Index' ? (string)($index + 1) : ($row[$labelColumn] ?? '');
                        $numericValueRaw = $row[$numericColumn] ?? '';
                        $numericValue = $this->extractFirstNumber($numericValueRaw);

                        $labels[] = $labelValue;
                        $values[] = $numericValue !== null ? $numericValue: 0;



                        // Save TableData record
                        $this->pageRequest->tableData()->create([
                            'table_index' => $tableIndex,
                            'row_index' => $index,
                            'label' => $labelValue,
                            'numeric_value' => $numericValue,
                            'numeric_column' => $numericColumn,
                            'label_column' => $labelColumn,
                            'raw_label_value' => $labelValue,
                            'raw_numeric_value' => $numericValue,
                            'full_row_data' => json_encode($row),
                        ]);
                    }

                    // Save Visualization record
                    $this->pageRequest->visualizations()->create([
                        'table_index' => $tableIndex,
                        'numeric_column' => $numericColumn,
                        'label_column' => $labelColumn,
                        'labels' => json_encode($labels),
                        'values' => json_encode($values),
                        'title' => "Visualization for Table #{$tableIndex}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $data = [
                        'labels' => $labels,
                        'values' => $values,
                        'label_column' => $labelColumn,
                        'numeric_column' => $numericColumn,
                        'title' => "Visualization for Table #{$tableIndex}",
                    ];

                    // Save JSON data temporarily
                    $jsonFilePath = storage_path("app/temp_data_table_{$tableIndex}.json}");
                    if (false === file_put_contents($jsonFilePath, json_encode($data))) {
                        throw new Exception("Unable to write to file temp_data_table_{$tableIndex}.json");
                    }

                    $outputImagePath = storage_path("app/public/visualization_table_{$tableIndex}.png");
                    $outputDir = dirname($outputImagePath);

                    if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
                      throw new Exception("Unable to create output directory $outputDir");
                    }

                    $pythonScriptPath = base_path('scripts/generate_visualization.py');

                    $command = [
                        'python3',
                        $pythonScriptPath,
                        '--input',
                        $jsonFilePath,
                        '--output',
                        $outputImagePath,
                        '--type',
                        'auto',
                        '--dpi',
                        '150'
                    ];

                    $process = new Process($command);
                    $process->setTimeout(60);

                    try {
                        $process->mustRun();
                    } catch (ProcessFailedException $exception) {
                        throw new Exception("Visualization generation failed for table #{$tableIndex}: {$exception->getMessage()}: {$exception->getTraceAsString()}");
                    }

                    $imageUrl = asset("storage/visualization_table_{$tableIndex}.png");
                    $visualizationsCreated++;
                } catch (Exception $tableException) {
                    \Log::error('Error processing table #' . $tableIndex . '  for PageRequest ID ' . $this->pageRequest->id. ': ' . $tableException->getMessage());
                    $errors[] = "Table #{$tableIndex}: " . $tableException->getMessage();
                    // Continue processing next tables despite errors
                }
            }

            if ($visualizationsCreated > 0) {
                $this->pageRequest->update(['status' => 'completed']);
            } else {
                $this->pageRequest->update(['status' => 'failed']);
            }

            if (!empty($errors)) {
                \Log::warning('Some tables failed to process for PageRequest ID ' . $this->pageRequest->id. ': ' . implode('; ', $errors));
            }

        } catch (Exception $e) {
            \Log::error('PageRequestJob failed for PageRequest ID '.$this->pageRequest->id.': '.$e->getMessage());
            $this->pageRequest->update(['status' => 'failed']);
        }
    }
}
