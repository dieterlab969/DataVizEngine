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

    public function handle(): void
    {
        $this->pageRequest->update(['status' => 'processing']);

        try {
            $html = $this->fetchHtml($this->pageRequest->url);

            $tables = $this->extractTables($html);

            if ($tables->count() === 0) {
                \Log::warning("No tables found for PageRequest ID {$this->pageRequest->id}");
                $this->pageRequest->update(['status' => 'failed']);
                return;
            }

            // Clear previous data
            $this->pageRequest->tableData()->delete();
            $this->pageRequest->visualizations()->delete();

            $visualizationsCreated = 0;
            $errors = [];

            $tableIndex = 0;
            foreach ($tables as $tableElement) {
                $tableIndex++;
                try {
                    $processed = $this->processTable($tableElement, $tableIndex);
                    if ($processed) {
                        $visualizationsCreated++;
                    }
                } catch (Exception $e) {
                    \Log::error(
                        "Error processing table #{$tableIndex} for PageRequest ID {$this->pageRequest->id}: {$e->getMessage()}"
                    );
                    $errors[] = "Table #{$tableIndex}: " . $e->getMessage();
                    // Continue processing next tables
                }
            }

            if ($visualizationsCreated > 0) {
                $this->pageRequest->update(['status' => 'completed']);
            } else {
                $this->pageRequest->update(['status' => 'failed']);
            }

            if (!empty($errors)) {
                \Log::warning('Some tables failed to process for PageRequest ID ' . $this->pageRequest->id . ': ' . implode('; ', $errors));
            }
        } catch (Exception $e) {
            \Log::error('PageRequestJob failed for PageRequest ID ' . $this->pageRequest->id . ': ' . $e->getMessage());
            $this->pageRequest->update(['status' => 'failed']);
        }
    }


    /**
     * Fetch HTML content from the URL.
     *
     * @param string $url
     * @return string
     * @throws Exception
     */
    private function fetchHtml(string $url): string
    {
        $client = new Client([
            'timeout' => 100,
            'headers' => ['User-Agent' => 'PageRequestBot/1.0(+https://yourdomain.com/)'],
        ]);

        try {
            $response = $client->get($url);
            return (string) $response->getBody();
        } catch (Exception $e) {
            \Log::error("HTML request failed for URL {$url}: {$e->getMessage()}");
            $this->pageRequest->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Extract tables to process from HTML content.
     *
     * @param string $html
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    private function extractTables(string $html)
    {
        $crawler = new Crawler($html);

        $tables = $crawler->filter('#mw-content-text table.wikitable');
        if ($tables->count() === 0) {
            $tables = $crawler->filter('#mw-content-text table');
        }

        return $tables;
    }

    /**
     * Processes a single table element: parses, saves data, generates visualization.
     *
     * @param \DOMElement $tableElement
     * @param int $tableIndex
     * @return bool True if visualization created, false otherwise.
     * @throws Exception
     */
    private function processTable(\DOMElement $tableElement, int $tableIndex): bool
    {
        $tableCrawler = new Crawler($tableElement);

        // Extract headers
        $headerCells = $tableCrawler->filter('tr')->first()->filter('th');
        $headers = [];
        foreach ($headerCells as $cell) {
            $headers[] = trim($cell->textContent);
        }

        if (empty($headers)) {
            \Log::info("Table #{$tableIndex} skipped due to missing headers for PageRequest ID {$this->pageRequest->id}");
            return false;
        }

        // Extract rows
        $rows = $tableCrawler->filter('tr')->slice(1);
        $rowsData = [];
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
            \Log::info("Table #{$tableIndex} skipped due to no row data for PageRequest ID {$this->pageRequest->id}");
            return false;
        }

        // Determine numeric column (>=80% numeric)
        $numericColumn = $this->determineNumericColumn($headers, $rowsData);
        if ($numericColumn === null) {
            \Log::info("Table #{$tableIndex} skipped: no suitable numeric column found for PageRequest ID {$this->pageRequest->id}");
            return false;
        }

        // Determine label column (>=80% non-numeric)
        $labelColumn = $this->determineLabelColumn($headers, $rowsData, $numericColumn);
        if ($labelColumn === null) {
            $labelColumn = 'Index';
        }

        // Save table data & prepare arrays for visualization
        [$labels, $values] = $this->saveTableDataAndPrepareVisualization($rowsData, $tableIndex, $numericColumn, $labelColumn);

        // Generate visualization image
        $this->generateVisualization($labels, $values, $labelColumn, $numericColumn, $tableIndex);

        return true;
    }

    /**
     * Determine the numeric column based on >=80% numeric values.
     *
     * @param array $headers
     * @param array $rowsData
     * @return string|null
     */
    private function determineNumericColumn(array $headers, array $rowsData): ?string
    {
        foreach ($headers as $header) {
            $numericCount = 0;
            $totalCount = 0;
            foreach ($rowsData as $row) {
                if (!isset($row[$header])) {
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
                return $header;
            }
        }
        return null;
    }

    /**
     * Determine the label column based on >=80% non-numeric values, excluding numeric column.
     *
     * @param array $headers
     * @param array $rowsData
     * @param string $numericColumn
     * @return string|null
     */
    private function determineLabelColumn(array $headers, array $rowsData, string $numericColumn): ?string
    {
        foreach ($headers as $header) {
            if ($header === $numericColumn) {
                continue;
            }
            $nonNumericCount = 0;
            $totalCount = 0;
            foreach ($rowsData as $row) {
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
                return $header;
            }
        }
        return null;
    }

    /**
     * Saves table data records and prepares label and value arrays for visualization.
     *
     * @param array $rowsData
     * @param int $tableIndex
     * @param string $numericColumn
     * @param string $labelColumn
     * @return array [$labels, $values]
     */
    private function saveTableDataAndPrepareVisualization(array $rowsData, int $tableIndex, string $numericColumn, string $labelColumn): array
    {
        $labels = [];
        $values = [];

        foreach ($rowsData as $index => $row) {
            $labelValue = $labelColumn === 'Index' ? (string)($index + 1) : ($row[$labelColumn] ?? '');
            $numericValueRaw = $row[$numericColumn] ?? '';
            $numericValue = $this->extractFirstNumber($numericValueRaw);

            $labels[] = $labelValue;
            $values[] = $numericValue !== null ? $numericValue : 0;

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

        return [$labels, $values];
    }

    /**
     * Generates a visualization PNG image by calling the Python script.
     *
     * @param array $labels
     * @param array $values
     * @param string $labelColumn
     * @param string $numericColumn
     * @param int $tableIndex
     * @throws Exception
     */
    private function generateVisualization(array $labels, array $values, string $labelColumn, string $numericColumn, int $tableIndex): void
    {
        $plotData = [
            'labels' => $labels,
            'values' => $values,
            'label_column' => $labelColumn,
            'numeric_column' => $numericColumn,
            'title' => "Visualization for Table #{$tableIndex}",
        ];

        $tempDir = storage_path('app/temp');
        $publicVisDir = storage_path('app/public/visualizations');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (!file_exists($publicVisDir)) {
            mkdir($publicVisDir, 0755, true);
        }

        $tempInputPath = "{$tempDir}/page_request_{$this->pageRequest->id}_table_{$tableIndex}_input.json";
        $tempOutputPath = "{$publicVisDir}/page_request_{$this->pageRequest->id}_table_{$tableIndex}.png";
        $relativePath = "visualizations/page_request_{$this->pageRequest->id}_table_{$tableIndex}.png";

        file_put_contents($tempInputPath, json_encode($plotData));

        $pythonScriptPath = base_path('scripts/generate_visualization.py');
        $command = escapeshellcmd("python3 {$pythonScriptPath} --input {$tempInputPath} --output {$tempOutputPath}");

        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($tempOutputPath)) {
            throw new Exception("Visualization script failed for table {$tableIndex}");
        }

        // Correct saving of the PNG file instead of JSON input
        Storage::disk('public')->put($relativePath, file_get_contents($tempOutputPath));

        // Clean up temp files
        @unlink($tempInputPath);
        @unlink($tempOutputPath);

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
    }
}
