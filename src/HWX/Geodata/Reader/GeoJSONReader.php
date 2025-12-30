<?php

namespace HWX\Geodata\Reader;

use HWX\Geodata\Model\GeoData;
use HWX\Geodata\Model\GeoPoint;
use HWX\Geodata\Model\GeoLineString;
use HWX\Geodata\Model\GeoPolygon;
use HWX\Geodata\Model\GeoFeature;
use HWX\Geodata\Model\GeoFeatureCollection;

/**
 * GeoJSON format reader that converts to unified GeoData model
 */
class GeoJSONReader
{
    public function readFromFile(string $filename): GeoData
    {
        $content = file_get_contents($filename);
        return $this->readFromString($content);
    }

    public function readFromString(string $content): GeoData
    {
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON: ' . json_last_error_msg());
        }

        $geoData = new GeoData();
        $geoData->originalFormat = 'GEOJSON';

        if (!isset($data['type']) || $data['type'] !== 'FeatureCollection') {
            throw new \Exception('Invalid GeoJSON: Must be a FeatureCollection');
        }

        foreach ($data['features'] as $feature) {
            $geoFeature = new GeoFeature();
            
            // Parse geometry
            if (isset($feature['geometry']) && isset($feature['geometry']['type'])) {
                $geoFeature->geometry = $this->parseGeometry($feature['geometry']);
            }

            // Parse properties
            if (isset($feature['properties'])) {
                $geoFeature->properties = $feature['properties'];
            }

            // Parse ID
            if (isset($feature['id'])) {
                $geoFeature->id = $feature['id'];
            }

            $geoData->features->add($geoFeature);
        }

        return $geoData;
    }

    protected function parseGeometry(array $geometry): array
    {
        $result = [
            'type' => $geometry['type'],
            'coordinates' => []
        ];

        switch ($geometry['type']) {
            case 'Point':
                $result['coordinates'] = [
                    'latitude' => $geometry['coordinates'][1],
                    'longitude' => $geometry['coordinates'][0],
                    'elevation' => isset($geometry['coordinates'][2]) ? $geometry['coordinates'][2] : null
                ];
                break;

            case 'LineString':
                foreach ($geometry['coordinates'] as $point) {
                    $result['coordinates'][] = [
                        'latitude' => $point[1],
                        'longitude' => $point[0],
                        'elevation' => isset($point[2]) ? $point[2] : null
                    ];
                }
                break;

            case 'Polygon':
                foreach ($geometry['coordinates'] as $ring) {
                    $ringPoints = [];
                    foreach ($ring as $point) {
                        $ringPoints[] = [
                            'latitude' => $point[1],
                            'longitude' => $point[0],
                            'elevation' => isset($point[2]) ? $point[2] : null
                        ];
                    }
                    $result['coordinates'][] = $ringPoints;
                }
                break;

            case 'MultiPoint':
                foreach ($geometry['coordinates'] as $point) {
                    $result['coordinates'][] = [
                        'latitude' => $point[1],
                        'longitude' => $point[0],
                        'elevation' => isset($point[2]) ? $point[2] : null
                    ];
                }
                break;
                
            case 'MultiLineString':
                foreach ($geometry['coordinates'] as $lineString) {
                    $linePoints = [];
                    foreach ($lineString as $point) {
                        $linePoints[] = [
                            'latitude' => $point[1],
                            'longitude' => $point[0],
                            'elevation' => isset($point[2]) ? $point[2] : null
                        ];
                    }
                    $result['coordinates'][] = $linePoints;
                }
                break;
                
            case 'MultiPolygon':
                foreach ($geometry['coordinates'] as $polygon) {
                    $polygonRings = [];
                    foreach ($polygon as $ring) {
                        $ringPoints = [];
                        foreach ($ring as $point) {
                            $ringPoints[] = [
                                'latitude' => $point[1],
                                'longitude' => $point[0],
                                'elevation' => isset($point[2]) ? $point[2] : null
                            ];
                        }
                        $polygonRings[] = $ringPoints;
                    }
                    $result['coordinates'][] = $polygonRings;
                }
                break;
                
            case 'GeometryCollection':
                foreach ($geometry['geometries'] as $geom) {
                    $result['coordinates'][] = $this->parseGeometry($geom);
                }
                break;

            default:
                throw new \Exception('Unsupported geometry type: ' . $geometry['type']);
        }

        return $result;
    }
}
