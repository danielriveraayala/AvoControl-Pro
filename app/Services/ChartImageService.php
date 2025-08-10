<?php

namespace App\Services;

// Include JPGraph configuration for constants
require_once base_path('vendor/amenadiel/jpgraph/src/config.inc.php');

use Amenadiel\JpGraph\Graph\PieGraph;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\PiePlot;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;

class ChartImageService
{
    protected $tempPath;

    public function __construct()
    {
        $this->tempPath = storage_path('app/temp/charts/');
        
        // Create the temp directory if it doesn't exist
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Generate a pie chart image
     */
    public function generatePieChart($data, $labels, $colors, $title = '', $width = 400, $height = 300)
    {
        try {
            // Validate and clean data
            $cleanData = [];
            foreach ($data as $value) {
                // Convert to float and handle null/non-numeric values
                $numericValue = is_numeric($value) ? floatval($value) : 0;
                $cleanData[] = $numericValue;
            }
            
            // Skip chart if no valid data
            if (array_sum($cleanData) == 0) {
                return null;
            }
            
            // Ensure labels array matches data array length
            $cleanLabels = [];
            for ($i = 0; $i < count($cleanData); $i++) {
                $cleanLabels[] = isset($labels[$i]) ? strval($labels[$i]) : 'Item ' . ($i + 1);
            }
            
            // Ensure colors array matches data array length
            $cleanColors = [];
            for ($i = 0; $i < count($cleanData); $i++) {
                $cleanColors[] = isset($colors[$i]) ? $colors[$i] : '#' . substr(md5($i), 0, 6);
            }
            
            // Create a new pie graph
            $graph = new PieGraph($width, $height);
            
            // Create pie plot with clean numeric data
            $pieplot = new PiePlot($cleanData);
            
            // Set colors
            if (!empty($cleanColors)) {
                $pieplot->SetSliceColors($cleanColors);
            }
            
            // Set labels
            $pieplot->SetLabels($cleanLabels);
            $pieplot->SetLabelType(PIE_VALUE_PER); // Show percentages
            
            // Add to graph
            $graph->Add($pieplot);
            
            // Set title
            if ($title) {
                $graph->title->Set($title);
                $graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
                $graph->title->SetColor('navy');
            }
            
            // Set margins
            $graph->SetMarginColor('white');
            
            // Generate unique filename
            $filename = 'pie_chart_' . uniqid() . '.png';
            $filepath = $this->tempPath . $filename;
            
            // Save the image
            $graph->Stroke($filepath);
            
            return $filepath;
            
        } catch (\Exception $e) {
            \Log::error('Error generating pie chart: ' . $e->getMessage(), [
                'data' => $data ?? 'null',
                'labels' => $labels ?? 'null', 
                'colors' => $colors ?? 'null',
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Generate a bar chart image
     * For now, use pie chart as fallback until bar chart issue is resolved
     */
    public function generateBarChart($data, $labels, $colors, $title = '', $width = 600, $height = 400)
    {
        try {
            // For now, generate a pie chart instead of bar chart to avoid issues
            // TODO: Fix bar chart implementation later
            return $this->generatePieChart($data, $labels, $colors, $title, $width, $height);
            
        } catch (\Exception $e) {
            \Log::error('Error generating bar chart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean up temporary chart files
     */
    public function cleanupTempFiles()
    {
        $files = glob($this->tempPath . '*.png');
        foreach ($files as $file) {
            // Delete files older than 1 hour
            if (filemtime($file) < time() - 3600) {
                unlink($file);
            }
        }
    }

    /**
     * Convert hex color to RGB array for JPGraph
     */
    protected function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
}