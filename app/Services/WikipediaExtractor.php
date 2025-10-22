<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class WikipediaExtractor
{
    public function extractTable(string $url): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'DataVizEngine/1.0 (Educational Project; Replit)',
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch Wikipedia page');
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $tables = $crawler->filter('table.wikitable');

        if ($tables->count() === 0) {
            throw new \Exception('No tables found on this Wikipedia page');
        }

        $table = $tables->first();

        $headers = [];
        $hasTheadHeaders = false;

        $table->filter('thead tr th, thead tr td')->each(function (Crawler $node) use (&$headers) {
            $headers[] = trim($node->text());
        });

        if (!empty($headers)) {
            $hasTheadHeaders = true;
        }

        if (empty($headers)) {
            $table->filter('tbody tr')->first()->filter('th, td')->each(function (Crawler $node) use (&$headers) {
                $headers[] = trim($node->text());
            });
        }

        $rows = [];
        $table->filter('tbody tr')->each(function (Crawler $row) use (&$rows, $headers) {
            $cells = [];
            $row->filter('th, td')->each(function (Crawler $cell) use (&$cells) {
                $cells[] = $this->cleanText($cell->text());
            });

            if (!empty($cells) && count($cells) > 0) {
                $rows[] = $cells;
            }
        });

        if (!$hasTheadHeaders && count($rows) > 0) {
            array_shift($rows);
        }

        $numericColumns = $this->identifyNumericColumns($headers, $rows);

        return [
            'headers' => $headers,
            'rows' => $rows,
            'numericColumns' => $numericColumns
        ];
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace([
            '/\.mw-parser-output\s*\{[^}]*\}/',  // Remove entire CSS block
            '/class="[^"]*"/',  // Remove class attributes
            '/\.[a-zA-Z-]+\s*\{[^}]*\}/',  // Remove additional CSS blocks
            '/\s*\.[a-zA-Z-]+\s*/', // Remove additional class references
        ], '', $text);
        $text = preg_replace('/\[\d+\]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function identifyNumericColumns(array $headers, array $rows): array
    {
        $numericColumns = [];

        foreach ($headers as $index => $header) {
            $numericCount = 0;
            $totalCount = 0;

            foreach ($rows as $row) {
                if (isset($row[$index])) {
                    $totalCount++;
                    $value = $this->extractNumericValue($row[$index]);
                    if ($value !== null) {
                        $numericCount++;
                    }
                }
            }

            if ($totalCount > 0 && ($numericCount / $totalCount) >= 0.7) {
                $numericColumns[] = $header;
            }
        }

        return $numericColumns;
    }

    private function extractNumericValue($value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', $value);

        if (is_numeric($cleaned) && $cleaned !== '') {
            return (float) $cleaned;
        }

        return null;
    }

    public function prepareVisualizationData(array $tableData, array $selectedColumns): array
    {
        $headers = $tableData['headers'];
        $rows = $tableData['rows'];

        $labelColumnIndex = 0;
        $dataColumnIndex = array_search($selectedColumns[0], $headers);

        if ($dataColumnIndex === false) {
            throw new \Exception('Selected column not found in table data');
        }

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            if (isset($row[$labelColumnIndex]) && isset($row[$dataColumnIndex])) {
                $labels[] = $row[$labelColumnIndex];
                $numericValue = $this->extractNumericValue($row[$dataColumnIndex]);
                $values[] = $numericValue ?? 0;
            }
        }

        return [
            'labels' => array_slice($labels, 0, 20),
            'values' => array_slice($values, 0, 20),
            'label_column' => $headers[$labelColumnIndex],
            'numeric_column' => $headers[$dataColumnIndex],
            'title' => "{$headers[$dataColumnIndex]} by {$headers[$labelColumnIndex]}"
        ];
    }
}
