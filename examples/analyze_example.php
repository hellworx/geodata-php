<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hellworx\Geodata\Converter\GeoDataConverter;

echo "=== GeoData PHP Library - File Analysis Example ===\n\n";

// Create converter instance
$converter = new GeoDataConverter();

// File to analyze
$filename = __DIR__ . '/test.gpx';

if (!file_exists($filename)) {
    echo "❌ File not found: $filename\n";
    exit(1);
}

echo "📁 Analyzing file: $filename\n";
echo "📄 File size: " . number_format(filesize($filename)) . " bytes\n\n";

try {
    // Read file to unified model
    echo "🔍 Reading file to unified model... ";
    $geoData = $converter->convertFileToModel($filename);
    echo "✅\n\n";
    
    // Basic information
    echo "📊 FILE SUMMARY\n";
    echo "===============\n";
    echo "Format: " . $geoData->originalFormat . "\n";
    echo "File: " . basename($filename) . "\n\n";
    
    // Content statistics
    echo "🗺️  CONTENT STATISTICS\n";
    echo "======================\n";
    echo "Points: " . count($geoData->points) . "\n";
    echo "Linestrings: " . count($geoData->linestrings) . "\n";
    echo "Polygons: " . count($geoData->polygons) . "\n";
    echo "Features: " . count($geoData->features) . "\n";
    echo "Total geometries: " . (count($geoData->points) + count($geoData->linestrings) + count($geoData->polygons) + count($geoData->features)) . "\n\n";
    
    // Detailed point analysis
    if (count($geoData->points) > 0) {
        echo "📍 POINT DETAILS\n";
        echo "================\n";
        
        $pointNames = [];
        $pointTypes = [];
        $elevations = [];
        $times = [];
        
        foreach ($geoData->points as $point) {
            if ($point->name) $pointNames[] = $point->name;
            if ($point->type) $pointTypes[] = $point->type;
            if ($point->elevation !== null) $elevations[] = $point->elevation;
            if ($point->time) $times[] = $point->time;
        }
        
        echo "Named points: " . count(array_filter($pointNames)) . "\n";
        echo "Points with elevation: " . count($elevations) . "\n";
        if (count($elevations) > 0) {
            echo "  Min elevation: " . min($elevations) . "m\n";
            echo "  Max elevation: " . max($elevations) . "m\n";
            echo "  Avg elevation: " . round(array_sum($elevations) / count($elevations), 1) . "m\n";
        }
        echo "Points with timestamp: " . count($times) . "\n";
        if (count($times) > 0) {
            echo "  Time range: " . min($times)->format('Y-m-d H:i:s') . " to " . max($times)->format('Y-m-d H:i:s') . "\n";
        }
        echo "\n";
    }
    
    // Detailed linestring analysis
    if (count($geoData->linestrings) > 0) {
        echo "🛤️  LINESTRING DETAILS\n";
        echo "======================\n";
        echo "Total linestrings: " . count($geoData->linestrings) . "\n";
        
        $totalLinePoints = 0;
        $lineNames = [];
        
        foreach ($geoData->linestrings as $index => $linestring) {
            $pointCount = count($linestring->points);
            $totalLinePoints += $pointCount;
            
            if ($linestring->name) {
                $lineNames[] = $linestring->name;
            }
            
            echo "  Linestring " . ($index + 1) . ": $pointCount points";
            if ($linestring->name) echo " (Name: {$linestring->name})";
            echo "\n";
        }
        
        echo "Total points in all linestrings: $totalLinePoints\n";
        echo "Named linestrings: " . count(array_filter($lineNames)) . "\n";
        echo "Average points per linestring: " . round($totalLinePoints / count($geoData->linestrings), 1) . "\n\n";
    }
    
    // Detailed feature analysis
    if (count($geoData->features) > 0) {
        echo "🏷️  FEATURE DETAILS\n";
        echo "===================\n";
        echo "Total features: " . count($geoData->features) . "\n";
        
        $featureNames = [];
        $geometryTypes = [];
        
        foreach ($geoData->features as $index => $feature) {
            if ($feature->name) {
                $featureNames[] = $feature->name;
            }
            
            $geometryType = 'Unknown';
            if ($feature->point) $geometryType = 'Point';
            elseif ($feature->lineString) $geometryType = 'LineString';
            elseif ($feature->polygon) $geometryType = 'Polygon';
            
            $geometryTypes[] = $geometryType;
            
            echo "  Feature " . ($index + 1) . ": $geometryType";
            if ($feature->name) echo " (Name: {$feature->name})";
            echo "\n";
        }
        
        echo "Named features: " . count(array_filter($featureNames)) . "\n";
        echo "Geometry types: " . implode(', ', array_unique($geometryTypes)) . "\n\n";
    }
    
    // Metadata analysis
    if ($geoData->metadata) {
        echo "📋 METADATA\n";
        echo "===========\n";
        $metadata = $geoData->metadata;
        
        if ($metadata->name) echo "Name: {$metadata->name}\n";
        if ($metadata->description) echo "Description: {$metadata->description}\n";
        if ($metadata->author) {
            echo "Author: ";
            if ($metadata->author->name) echo $metadata->author->name;
            if ($metadata->author->email) echo " ({$metadata->author->email})";
            echo "\n";
        }
        if ($metadata->copyright) {
            echo "Copyright: {$metadata->copyright->year}";
            if ($metadata->copyright->license) echo " - {$metadata->copyright->license}";
            echo "\n";
        }
        if ($metadata->bounds) {
            $bounds = $metadata->bounds;
            echo "Bounds: ";
            echo "Min lat: {$bounds->minLatitude}, Max lat: {$bounds->maxLatitude}, ";
            echo "Min lon: {$bounds->minLongitude}, Max lon: {$bounds->maxLongitude}\n";
        }
        echo "\n";
    }
    
    // Calculate overall bounds if not in metadata
    if (!$geoData->metadata || !$geoData->metadata->bounds) {
        echo "🌍 GEOGRAPHIC EXTENT\n";
        echo "====================\n";
        
        $allLatitudes = [];
        $allLongitudes = [];
        
        // Collect all coordinates
        foreach ($geoData->points as $point) {
            if ($point->latitude !== null && $point->longitude !== null) {
                $allLatitudes[] = $point->latitude;
                $allLongitudes[] = $point->longitude;
            }
        }
        
        foreach ($geoData->linestrings as $linestring) {
            foreach ($linestring->points as $point) {
                if ($point->latitude !== null && $point->longitude !== null) {
                    $allLatitudes[] = $point->latitude;
                    $allLongitudes[] = $point->longitude;
                }
            }
        }
        
        if (count($allLatitudes) > 0) {
            echo "Calculated bounds from all coordinates:\n";
            echo "Latitude range: " . min($allLatitudes) . " to " . max($allLatitudes) . "\n";
            echo "Longitude range: " . min($allLongitudes) . " to " . max($allLongitudes) . "\n";
        } else {
            echo "No coordinate data available for bounds calculation\n";
        }
        echo "\n";
    }
    
    echo "✅ Analysis complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error analyzing file: " . $e->getMessage() . "\n";
}

?>
