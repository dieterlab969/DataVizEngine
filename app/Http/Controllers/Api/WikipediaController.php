<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WikipediaExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;

class WikipediaController extends Controller
{
    protected $extractor;

    public function __construct(WikipediaExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    public function extractTable(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $data = $this->extractor->extractTable($request->url);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function generateVisualization(Request $request)
    {
        $request->validate([
            'tableData' => 'required|array',
            'selectedColumns' => 'required|array',
            'chartType' => 'required|string|in:bar,line,scatter'
        ]);

        try {
            $vizData = $this->extractor->prepareVisualizationData(
                $request->tableData,
                $request->selectedColumns
            );

            $inputFile = storage_path('app/temp_input_' . uniqid() . '.json');
            $outputFile = storage_path('app/public/viz_' . uniqid() . '.png');

            file_put_contents($inputFile, json_encode($vizData));

            $pythonScript = base_path('scripts/generate_visualization.py');
            $chartType = $request->chartType;

            // Use Replit's Python path if available, otherwise fallback to system python3
            $pythonPath = file_exists(base_path('.pythonlibs/bin/python3')) 
                ? base_path('.pythonlibs/bin/python3')
                : 'python3';

            // Set PYTHONPATH to include Replit's Python packages
            $pythonLibPath = base_path('.pythonlibs/lib/python3.11/site-packages');
            $command = "PYTHONPATH={$pythonLibPath}:\$PYTHONPATH {$pythonPath} {$pythonScript} --input {$inputFile} --output {$outputFile} --type {$chartType} --dpi 150";

            $result = Process::run($command);

            if (!$result->successful()) {
                throw new \Exception('Failed to generate visualization: ' . $result->errorOutput());
            }

            @unlink($inputFile);

            $publicUrl = '/storage/' . basename($outputFile);

            return response()->json([
                'id' => time(),
                'imageUrl' => $publicUrl,
                'chartType' => $chartType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
