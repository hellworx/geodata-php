<?php

namespace Hellworx\Geodata\Tests;

use Hellworx\Geodata\Converter\GeoDataConverter;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for GeoDataConverter
 */
class GeoDataConverterTest extends TestCase
{
    private $converter;
    private $testGpxContent;
    private $testGeoJsonContent;

    protected function setUp(): void
    {
        $this->converter = new GeoDataConverter();
        
        // Sample GPX content for testing
        $this->testGpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx creator="GeoData PHP Library" version="1.1" xmlns="http://www.topografix.com/GPX/1/1">
  <metadata>
    <name>Test Track</name>
    <desc>A test track for conversion</desc>
    <author>
      <name>Test Author</name>
    </author>
    <time>2023-01-01T12:00:00Z</time>
  </metadata>
  <wpt lat="50.0583" lon="19.8006">
    <ele>100.5</ele>
    <time>2023-01-01T12:00:00Z</time>
    <name>Start Point</name>
    <desc>Starting point of the track</desc>
  </wpt>
  <trk>
    <name>Main Track</name>
    <trkseg>
      <trkpt lat="50.0584" lon="19.8007">
        <ele>101.2</ele>
        <time>2023-01-01T12:01:00Z</time>
      </trkpt>
      <trkpt lat="50.0588" lon="19.8009">
        <ele>102.7</ele>
        <time>2023-01-01T12:02:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
GPX;

        // Sample GeoJSON content for testing
        $this->testGeoJsonContent = <<<GEOJSON
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "name": "Test Point",
        "description": "A test point"
      },
      "geometry": {
        "type": "Point",
        "coordinates": [19.8006, 50.0583, 100.5]
      }
    },
    {
      "type": "Feature",
      "properties": {
        "name": "Test Line",
        "description": "A test line"
      },
      "geometry": {
        "type": "LineString",
        "coordinates": [
          [19.8007, 50.0584, 101.2],
          [19.8009, 50.0588, 102.7]
        ]
      }
    }
  ]
}
GEOJSON;
    }

    public function testGpxToGeoJsonConversion()
    {
        $result = $this->converter->convert(
            $this->testGpxContent,
            'GPX',
            'GEOJSON'
        );
        
        $this->assertIsString($result);
        $this->assertStringContainsString('FeatureCollection', $result);
        $this->assertStringContainsString('Test Track', $result);
    }

    public function testGeoJsonToGpxConversion()
    {
        $result = $this->converter->convert(
            $this->testGeoJsonContent,
            'GEOJSON',
            'GPX'
        );
        
        $this->assertIsString($result);
        $this->assertStringContainsString('<gpx ', $result);
        $this->assertStringContainsString('Test Point', $result);
    }

    public function testUnifiedModelConversion()
    {
        // Test GPX to unified model
        $geoDataFromGpx = $this->converter->toUnifiedModel($this->testGpxContent, 'GPX');
        
        $this->assertNotNull($geoDataFromGpx);
        $this->assertEquals('GPX', $geoDataFromGpx->originalFormat);
        $this->assertNotNull($geoDataFromGpx->metadata);
        $this->assertGreaterThan(0, count($geoDataFromGpx->points));
        $this->assertGreaterThan(0, count($geoDataFromGpx->linestrings));

        // Test GeoJSON to unified model  
        $geoDataFromGeoJson = $this->converter->toUnifiedModel($this->testGeoJsonContent, 'GEOJSON');
        
        $this->assertNotNull($geoDataFromGeoJson);
        $this->assertEquals('GEOJSON', $geoDataFromGeoJson->originalFormat);
        $this->assertGreaterThan(0, count($geoDataFromGeoJson->features));

        // Test unified model to GPX
        $gpxResult = $this->converter->fromUnifiedModel($geoDataFromGeoJson, 'GPX');
        $this->assertStringContainsString('<gpx ', $gpxResult);

        // Test unified model to GeoJSON
        $geoJsonResult = $this->converter->fromUnifiedModel($geoDataFromGpx, 'GEOJSON');
        $this->assertStringContainsString('FeatureCollection', $geoJsonResult);
    }

    public function testInvalidFormatConversion()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported input format: INVALID');
        
        $this->converter->convert('invalid content', 'INVALID', 'GPX');
    }

    public function testFileConversion()
    {
        // Create temporary files with proper extensions
        $gpxFile = tempnam(sys_get_temp_dir(), 'test_gpx_') . '.gpx';
        $geoJsonFile = tempnam(sys_get_temp_dir(), 'test_geojson_') . '.json';
        
        file_put_contents($gpxFile, $this->testGpxContent);
        
        try {
            // Test file to file conversion
            $result = $this->converter->convertFile($gpxFile, $geoJsonFile);
            
            $this->assertTrue($result);
            $this->assertFileExists($geoJsonFile);
            
            // Verify converted content
            $content = file_get_contents($geoJsonFile);
            $this->assertStringContainsString('FeatureCollection', $content);
            
        } finally {
            // Clean up
            @unlink($gpxFile);
            @unlink($geoJsonFile);
        }
    }
}

?>
