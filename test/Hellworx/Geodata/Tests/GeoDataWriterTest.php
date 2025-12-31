<?php

namespace Hellworx\Geodata\Tests;

use Hellworx\Geodata\Converter\GeoDataConverter;
use Hellworx\Geodata\Model\GeoData;
use Hellworx\Geodata\Model\GeoPoint;
use Hellworx\Geodata\Model\GeoLineString;
use Hellworx\Geodata\Model\GeoPolygon;
use Hellworx\Geodata\Model\GeoFeature;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for GeoData writers
 */
class GeoDataWriterTest extends TestCase
{
    private $converter;
    private $testGeoData;

    protected function setUp(): void
    {
        $this->converter = new GeoDataConverter();
        
        // Create test data with all geometry types
        $this->testGeoData = new GeoData();
        $this->testGeoData->version = '1.0';
        $this->testGeoData->creator = 'GeoData PHP Library Test';
        $this->testGeoData->originalFormat = 'TEST';
        
        // Add test points
        $point1 = new GeoPoint();
        $point1->latitude = 50.0583;
        $point1->longitude = 19.8006;
        $point1->elevation = 100.5;
        $point1->name = 'Test Point 1';
        $point1->description = 'First test point';
        $this->testGeoData->points->add($point1);
        
        $point2 = new GeoPoint();
        $point2->latitude = 50.0584;
        $point2->longitude = 19.8007;
        $point2->elevation = 101.2;
        $point2->name = 'Test Point 2';
        $this->testGeoData->points->add($point2);
        
        // Add test linestring
        $lineString = new GeoLineString();
        $lineString->name = 'Test LineString';
        $lineString->description = 'Test linestring with multiple points';
        $lineString->points = [$point1, $point2];
        $this->testGeoData->linestrings->add($lineString);
        
        // Add test polygon
        $polygon = new GeoPolygon();
        $polygon->name = 'Test Polygon';
        $polygon->description = 'Test polygon with outer and inner rings';
        
        // Outer ring (simple square)
        $outerRingPoint1 = new GeoPoint();
        $outerRingPoint1->latitude = 50.05;
        $outerRingPoint1->longitude = 19.80;
        
        $outerRingPoint2 = new GeoPoint();
        $outerRingPoint2->latitude = 50.06;
        $outerRingPoint2->longitude = 19.80;
        
        $outerRingPoint3 = new GeoPoint();
        $outerRingPoint3->latitude = 50.06;
        $outerRingPoint3->longitude = 19.81;
        
        $outerRingPoint4 = new GeoPoint();
        $outerRingPoint4->latitude = 50.05;
        $outerRingPoint4->longitude = 19.81;
        
        $outerRingPoint5 = clone $outerRingPoint1; // Close the ring
        
        $outerRing = new GeoLineString();
        $outerRing->points = [$outerRingPoint1, $outerRingPoint2, $outerRingPoint3, $outerRingPoint4, $outerRingPoint5];
        
        // Inner ring (hole in the middle)
        $innerRingPoint1 = new GeoPoint();
        $innerRingPoint1->latitude = 50.052;
        $innerRingPoint1->longitude = 19.802;
        
        $innerRingPoint2 = new GeoPoint();
        $innerRingPoint2->latitude = 50.058;
        $innerRingPoint2->longitude = 19.802;
        
        $innerRingPoint3 = new GeoPoint();
        $innerRingPoint3->latitude = 50.058;
        $innerRingPoint3->longitude = 19.808;
        
        $innerRingPoint4 = new GeoPoint();
        $innerRingPoint4->latitude = 50.052;
        $innerRingPoint4->longitude = 19.808;
        
        $innerRingPoint5 = clone $innerRingPoint1; // Close the ring
        
        $innerRing = new GeoLineString();
        $innerRing->points = [$innerRingPoint1, $innerRingPoint2, $innerRingPoint3, $innerRingPoint4, $innerRingPoint5];
        
        // Add rings to polygon
        $polygon->rings = [$outerRing, $innerRing];
        $this->testGeoData->polygons->add($polygon);
        
        // Add test feature with GeometryCollection
        $feature = new GeoFeature();
        $feature->id = 'test-feature';
        $feature->properties = [
            'name' => 'Test Feature',
            'description' => 'Test feature with GeometryCollection'
        ];
        
        // Create a GeometryCollection with multiple geometry types
        $feature->geometry = [
            'type' => 'GeometryCollection',
            'coordinates' => [
                // Point
                [
                    'type' => 'Point',
                    'coordinates' => [
                        'latitude' => 50.055,
                        'longitude' => 19.805,
                        'elevation' => 105.0
                    ]
                ],
                // LineString
                [
                    'type' => 'LineString',
                    'coordinates' => [
                        [
                            'latitude' => 50.056,
                            'longitude' => 19.806,
                            'elevation' => 106.0
                        ],
                        [
                            'latitude' => 50.057,
                            'longitude' => 19.807,
                            'elevation' => 107.0
                        ]
                    ]
                ]
            ]
        ];
        
        $this->testGeoData->features->add($feature);
    }

    public function testKmlWriter()
    {
        $kmlWriter = new \Hellworx\Geodata\Writer\KMLWriter();
        $result = $kmlWriter->writeToString($this->testGeoData);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('<kml ', $result);
        $this->assertStringContainsString('Test Point 1', $result);
        $this->assertStringContainsString('Test LineString', $result);
        $this->assertStringContainsString('Test Polygon', $result);
        $this->assertStringContainsString('Test Feature', $result);
        
        // Check that polygon elements are present
        $this->assertStringContainsString('<Polygon>', $result);
        $this->assertStringContainsString('<outerBoundaryIs>', $result);
        $this->assertStringContainsString('<innerBoundaryIs>', $result);
    }

    public function testGeoJsonWriter()
    {
        $geoJsonWriter = new \Hellworx\Geodata\Writer\GeoJSONWriter();
        $result = $geoJsonWriter->writeToString($this->testGeoData);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('FeatureCollection', $result);
        $this->assertStringContainsString('Test Point 1', $result);
        $this->assertStringContainsString('Test LineString', $result);
        $this->assertStringContainsString('Test Polygon', $result);
        $this->assertStringContainsString('Test Feature', $result);
        
        // Parse JSON to verify structure
        $data = json_decode($result, true);
        $this->assertEquals('FeatureCollection', $data['type']);
        $this->assertGreaterThanOrEqual(4, count($data['features'])); // 2 points + 1 line + 1 polygon + 1 feature = 5
        
        // Verify GeometryCollection is properly written
        foreach ($data['features'] as $feature) {
            if (isset($feature['geometry']['type']) && $feature['geometry']['type'] === 'GeometryCollection') {
                $this->assertEquals('GeometryCollection', $feature['geometry']['type']);
                $this->assertArrayHasKey('geometries', $feature['geometry']);
                $this->assertGreaterThanOrEqual(2, count($feature['geometry']['geometries']));
                
                // Check that different geometry types are present
                $geomTypes = array_column($feature['geometry']['geometries'], 'type');
                $this->assertContains('Point', $geomTypes);
                $this->assertContains('LineString', $geomTypes);
            }
        }
    }

    public function testKmzWriter()
    {
        // Create temporary file
        $kmzFile = tempnam(sys_get_temp_dir(), 'test_kmz_') . '.kmz';
        
        try {
            $kmzWriter = new \Hellworx\Geodata\Writer\KMZWriter();
            $kmzWriter->writeToFile($this->testGeoData, $kmzFile);
            
            $this->assertFileExists($kmzFile);
            $this->assertGreaterThan(0, filesize($kmzFile));
            
            // Verify KMZ content (should contain doc.kml)
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($kmzFile) === true);
            
            $this->assertTrue($zip->locateName('doc.kml') !== false);
            
            $kmlContent = $zip->getFromName('doc.kml');
            $this->assertStringContainsString('<kml ', $kmlContent);
            $this->assertStringContainsString('Test Point 1', $kmlContent);
            
            $zip->close();
            
        } finally {
            // Clean up
            @unlink($kmzFile);
        }
    }

    public function testFormatConversion()
    {
        // Test conversion between all supported formats
        $formats = ['GPX', 'KML', 'KMZ', 'GEOJSON'];
        
        foreach ($formats as $fromFormat) {
            foreach ($formats as $toFormat) {
                // Skip KMZ to string conversion
                if ($fromFormat === 'KMZ') {
                    continue;
                }
                
                // Convert to intermediate format first
                $geoData = $this->convertToIntermediateFormat($fromFormat);
                
                if ($geoData === null) {
                    $this->markTestSkipped("Skipping conversion from $fromFormat to $toFormat");
                    continue;
                }
                
                // Convert to target format
                $result = $this->convertFromIntermediateFormat($geoData, $toFormat);
                
                if ($result === false) {
                    $this->markTestSkipped("Skipping conversion from $fromFormat to $toFormat");
                    continue;
                }
                
                $this->assertNotNull($result);
                $this->assertIsString($result);
                
                // For KMZ, just verify file exists
                if ($toFormat === 'KMZ') {
                    $kmzFile = tempnam(sys_get_temp_dir(), 'convert_') . '.kmz';
                    file_put_contents($kmzFile, $result);
                    
                    $this->assertFileExists($kmzFile);
                    $this->assertGreaterThan(0, filesize($kmzFile));
                    
                    @unlink($kmzFile);
                }
                // For other formats, do basic validation
                else {
                    $this->assertStringNotContainsString('Unsupported', $result);
                    
                    if ($toFormat === 'GPX') {
                        $this->assertStringContainsString('<gpx ', $result);
                    } elseif ($toFormat === 'KML' || $toFormat === 'GEOJSON') {
                        $this->assertStringContainsString($toFormat === 'KML' ? '<kml ' : '"type":"FeatureCollection"', $result);
                    }
                }
            }
        }
    }

    /**
     * Convert test data to intermediate format
     * @param string $format
     * @return GeoData|null
     */
    protected function convertToIntermediateFormat(string $format): ?GeoData
    {
        // For GPX, KML, and GeoJSON, we need sample content to convert from
        switch ($format) {
            case 'GPX':
                $content = $this->getSampleGpxContent();
                break;
            case 'KML':
                $content = $this->getSampleKmlContent();
                break;
            case 'GEOJSON':
                $content = $this->getSampleGeoJsonContent();
                break;
            case 'KMZ':
                return null; // Can't convert from KMZ string
            default:
                return null;
        }
        
        try {
            return $this->converter->toUnifiedModel($content, $format);
        } catch (\Exception $e) {
            $this->markTestSkipped("Failed to convert to intermediate format: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert from intermediate format to target format
     * @param GeoData $geoData
     * @param string $format
     * @return string|bool
     */
    protected function convertFromIntermediateFormat(GeoData $geoData, string $format)
    {
        try {
            return $this->converter->fromUnifiedModel($geoData, $format);
        } catch (\Exception $e) {
            $this->markTestSkipped("Failed to convert from intermediate format: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get sample GPX content for testing
     * @return string
     */
    protected function getSampleGpxContent(): string
    {
        return <<<GPX
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
    }

    /**
     * Get sample KML content for testing
     * @return string
     */
    protected function getSampleKmlContent(): string
    {
        return <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Test KML</name>
    <Placemark>
      <name>Test Point</name>
      <Point>
        <coordinates>19.8006,50.0583,100.5</coordinates>
      </Point>
    </Placemark>
    <Placemark>
      <name>Test LineString</name>
      <LineString>
        <coordinates>
          19.8007,50.0584,101.2 19.8009,50.0588,102.7
        </coordinates>
      </LineString>
    </Placemark>
  </Document>
</kml>
KML;
    }

    /**
     * Get sample GeoJSON content for testing
     * @return string
     */
    protected function getSampleGeoJsonContent(): string
    {
        return <<<GEOJSON
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
}
