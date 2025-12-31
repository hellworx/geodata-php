<?php

namespace Hellworx\Geodata\Writer;

use Hellworx\Geodata\Model\GeoData;
use Hellworx\Geodata\Model\GeoFeature;
use Hellworx\Geodata\Model\GeoPoint;
use Hellworx\Geodata\Model\GeoLineString;

/**
 * GeoJSON format writer that converts from unified GeoData model
 */
class GeoJSONWriter
{
    public function writeToFile(GeoData $geoData, string $filename)
    {
        $content = $this->writeToString($geoData);
        file_put_contents($filename, $content);
    }

    public function writeToString(GeoData $geoData): string
    {
        $featureCollection = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        // Add metadata as feature properties if available
        $globalProperties = new \stdClass();
        if ($geoData->metadata) {
            $globalProperties->metadata = [
                'name' => $geoData->metadata->name,
                'description' => $geoData->metadata->description,
                'author' => $geoData->metadata->author ? $geoData->metadata->author->name : null,
                'time' => $geoData->metadata->time ? $geoData->metadata->time->format(\DateTime::ISO8601) : null
            ];
        }

        // Convert points to features
        foreach ($geoData->points as $point) {
            $geoJSONFeature = [
                'type' => 'Feature',
                'properties' => (object) array_merge((array)$globalProperties, [
                    'name' => $point->name,
                    'description' => $point->description,
                    'type' => 'Point'
                ])
            ];

            if ($point->name) {
                $geoJSONFeature['id'] = $point->name;
            }

            $geoJSONFeature['geometry'] = $this->convertPointToGeoJSONGeometry($point);
            $featureCollection['features'][] = $geoJSONFeature;
        }

        // Convert linestrings to features
        foreach ($geoData->linestrings as $lineString) {
            $geoJSONFeature = [
                'type' => 'Feature',
                'properties' => (object) array_merge((array)$globalProperties, [
                    'name' => $lineString->name,
                    'description' => $lineString->description,
                    'type' => 'LineString'
                ])
            ];

            if ($lineString->name) {
                $geoJSONFeature['id'] = $lineString->name;
            }

            $geoJSONFeature['geometry'] = $this->convertLineStringToGeoJSONGeometry($lineString);
            $featureCollection['features'][] = $geoJSONFeature;
        }

        // Convert polygons to features
        foreach ($geoData->polygons as $polygon) {
            $geoJSONFeature = [
                'type' => 'Feature',
                'properties' => (object) array_merge((array)$globalProperties, [
                    'name' => $polygon->name,
                    'description' => $polygon->description,
                    'type' => 'Polygon'
                ])
            ];

            if ($polygon->name) {
                $geoJSONFeature['id'] = $polygon->name;
            }

            $geoJSONFeature['geometry'] = $this->convertPolygonToGeoJSONGeometry($polygon);
            $featureCollection['features'][] = $geoJSONFeature;
        }

        // Convert features to GeoJSON format
        foreach ($geoData->features as $feature) {
            $geoJSONFeature = [
                'type' => 'Feature',
                'properties' => (object) array_merge((array)$globalProperties, $feature->properties ?? [])
            ];

            if ($feature->id) {
                $geoJSONFeature['id'] = $feature->id;
            }

            if ($feature->geometry) {
                $geoJSONFeature['geometry'] = $this->convertToGeoJSONGeometry($feature->geometry);
            }

            $featureCollection['features'][] = $geoJSONFeature;
        }

        return json_encode($featureCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function convertPointToGeoJSONGeometry(GeoPoint $point): array
    {
        return [
            'type' => 'Point',
            'coordinates' => [
                $point->longitude,
                $point->latitude,
                $point->elevation ?? null
            ]
        ];
    }

    protected function convertLineStringToGeoJSONGeometry(GeoLineString $lineString): array
    {
        $coordinates = [];
        foreach ($lineString->points as $point) {
            $coordinates[] = [
                $point->longitude,
                $point->latitude,
                $point->elevation ?? null
            ];
        }
        return [
            'type' => 'LineString',
            'coordinates' => $coordinates
        ];
    }

    protected function convertPolygonToGeoJSONGeometry(\Hellworx\Geodata\Model\GeoPolygon $polygon): array
    {
        $coordinates = [];
        
        foreach ($polygon->rings as $ring) {
            $ringCoords = [];
            foreach ($ring->points as $point) {
                $ringCoords[] = [
                    $point->longitude,
                    $point->latitude,
                    $point->elevation ?? null
                ];
            }
            $coordinates[] = $ringCoords;
        }
        
        return [
            'type' => 'Polygon',
            'coordinates' => $coordinates
        ];
    }

    protected function convertToGeoJSONGeometry(array $geometry): array
    {
        $geoJSONGeometry = [
            'type' => $geometry['type'],
            'coordinates' => []
        ];

        switch ($geometry['type']) {
            case 'Point':
                $coords = $geometry['coordinates'];
                $geoJSONGeometry['coordinates'] = [
                    $coords['longitude'],
                    $coords['latitude'],
                    $coords['elevation'] ?? null
                ];
                // Remove null values from coordinates array
                $geoJSONGeometry['coordinates'] = array_filter($geoJSONGeometry['coordinates'], function($value) {
                    return $value !== null;
                });
                break;

            case 'LineString':
                foreach ($geometry['coordinates'] as $point) {
                    $geoJSONGeometry['coordinates'][] = [
                        $point['longitude'],
                        $point['latitude'],
                        $point['elevation'] ?? null
                    ];
                }
                break;

            case 'Polygon':
                foreach ($geometry['coordinates'] as $ring) {
                    $ringCoords = [];
                    foreach ($ring as $point) {
                        $ringCoords[] = [
                            $point['longitude'],
                            $point['latitude'],
                            $point['elevation'] ?? null
                        ];
                    }
                    $geoJSONGeometry['coordinates'][] = $ringCoords;
                }
                break;

            case 'MultiPoint':
                foreach ($geometry['coordinates'] as $point) {
                    $geoJSONGeometry['coordinates'][] = [
                        $point['longitude'],
                        $point['latitude'],
                        $point['elevation'] ?? null
                    ];
                }
                break;

            case 'MultiLineString':
                foreach ($geometry['coordinates'] as $lineString) {
                    $lineCoords = [];
                    foreach ($lineString as $point) {
                        $lineCoords[] = [
                            $point['longitude'],
                            $point['latitude'],
                            $point['elevation'] ?? null
                        ];
                    }
                    $geoJSONGeometry['coordinates'][] = $lineCoords;
                }
                break;

            case 'MultiPolygon':
                foreach ($geometry['coordinates'] as $polygon) {
                    $polygonCoords = [];
                    foreach ($polygon as $ring) {
                        $ringCoords = [];
                        foreach ($ring as $point) {
                            $ringCoords[] = [
                                $point['longitude'],
                                $point['latitude'],
                                $point['elevation'] ?? null
                            ];
                        }
                        $polygonCoords[] = $ringCoords;
                    }
                    $geoJSONGeometry['coordinates'][] = $polygonCoords;
                }
                break;

            case 'GeometryCollection':
                $geoJSONGeometry['geometries'] = [];
                foreach ($geometry['coordinates'] as $geom) {
                    $geoJSONGeometry['geometries'][] = $this->convertToGeoJSONGeometry($geom);
                }
                unset($geoJSONGeometry['coordinates']);
                break;

            default:
                throw new \Exception('Unsupported geometry type: ' . $geometry['type']);
        }

        return $geoJSONGeometry;
    }
}
