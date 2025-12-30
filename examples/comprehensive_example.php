<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HWX\Geodata\Converter\GeoDataConverter;

echo "=== GeoData PHP Library - Comprehensive Example ===\n\n";

// Create converter instance
$converter = new GeoDataConverter();

// Test files available in the examples directory
$testFiles = [
    'GPX' => __DIR__ . '/test.gpx'
];

// Create output directory
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "Created output directory: $outputDir\n\n";
}

// Test all format conversions
foreach ($testFiles as $inputFormat => $inputFile) {
    echo "=== Testing $inputFormat Input Format ===\n";
    
    if (!file_exists($inputFile)) {
        echo "  ❌ Input file not found: $inputFile\n\n";
        continue;
    }

    try {
        // Step 1: Read file to unified model
        echo "  1. Reading $inputFormat file to unified model... ";
        $geoData = $converter->convertFileToModel($inputFile);
        echo "✅\n";
        
        echo "     - Original format: $geoData->originalFormat\n";
        echo "     - Metadata present: " . ($geoData->metadata ? "✅" : "❌") . "\n";
        echo "     - Points: " . count($geoData->points) . "\n";
        echo "     - Linestrings: " . count($geoData->linestrings) . "\n";
        echo "     - Features: " . count($geoData->features) . "\n";

        // Step 2: Convert to all other formats
        echo "  2. Converting to other formats...\n";
        
        foreach (['GPX', 'KML', 'GeoJSON'] as $outputFormat) {
            if ($inputFormat === $outputFormat) continue;
            
            $outputFile = "$outputDir/" . basename($inputFile, pathinfo($inputFile, PATHINFO_EXTENSION)) . ".{$outputFormat}";
            
            echo "     - Converting to $outputFormat: ";
            $result = $converter->convertModelToFile($geoData, $outputFile, $outputFormat);
            echo $result ? "✅ ($outputFile)\n" : "❌\n";
        }

        echo "\n";
        
    } catch (Exception $e) {
        echo "  ❌ Error processing $inputFormat file: " . $e->getMessage() . "\n\n";
    }
}

// Test direct string conversion
echo "=== Testing Direct String Conversion ===\n";

try {
    // Sample GPX content
    $sampleGpx = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx creator="Test" version="1.1" xmlns="http://www.topografix.com/GPX/1/1">
  <wpt lat="50.0583" lon="19.8006">
    <name>Test Point</name>
  </wpt>
</gpx>
GPX;

    echo "  Converting GPX string to GeoJSON... ";
    $geoJsonResult = $converter->convert($sampleGpx, 'GPX', 'GEOJSON');
    echo "✅\n";
    
    echo "  GeoJSON result preview: " . substr($geoJsonResult, 0, 100) . "...\n\n";

} catch (Exception $e) {
    echo "  ❌ Error in string conversion: " . $e->getMessage() . "\n\n";
}

echo "=== Example Complete ===\n";
echo "All conversion results can be found in: $outputDir\n";

?>
