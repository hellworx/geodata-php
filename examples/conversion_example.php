<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hellworx\Geodata\Converter\GeoDataConverter;

echo "=== GeoData PHP Library - Conversion Example ===\n\n";

// Create converter instance
$converter = new GeoDataConverter();

// Test files (using local test file)
$testFiles = [
    'gpx' => __DIR__ . '/test.gpx'
];

// Test all available conversions
foreach ($testFiles as $format => $filename) {
    echo "Testing $format file: $filename\n";
    
    if (!file_exists($filename)) {
        echo "  ❌ File not found\n";
        continue;
    }

    try {
        // Convert to unified model first
        echo "  Converting to unified model... ";
        $geoData = $converter->convertFileToModel($filename);
        echo "✅\n";
        
        echo "  Original format: " . $geoData->originalFormat . "\n";
        echo "  Points: " . count($geoData->points) . "\n";
        echo "  Linestrings: " . count($geoData->linestrings) . "\n";
        echo "  Features: " . count($geoData->features) . "\n";
        
        // Convert to other formats
        $outputDir = __DIR__ . '/output';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Convert to all other formats
        foreach (['gpx', 'kml', 'geojson'] as $targetFormat) {
            if ($format === $targetFormat) continue;
            
            $outputFile = "$outputDir/" . basename($filename, pathinfo($filename, PATHINFO_EXTENSION)) . "." . $targetFormat;
            
            echo "  Converting to $targetFormat... ";
            $result = $converter->convertModelToFile($geoData, $outputFile, $targetFormat);
            echo $result ? "✅\n" : "❌\n";
        }
        
        echo "\n";
        
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Example Complete ===\n";
echo "Output files can be found in: " . __DIR__ . '/output' . "\n";

?>
