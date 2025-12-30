<?php

namespace HWX\Geodata\Reader;

use HWX\Geodata\Model\GeoData;
use HWX\Geodata\Model\GeoPoint;
use HWX\Geodata\Model\GeoLineString;
use HWX\Geodata\Model\GeoPolygon;
use HWX\Geodata\Model\GeoFeature;
use HWX\Geodata\Model\GeoFeatureCollection;
use HWX\Geodata\Model\GeoMetadata;
use XMLReader;

/**
 * KML format reader that converts to unified GeoData model
 */
class KMLReader
{
    public function readFromFile(string $filename): GeoData
    {
        return $this->read(XMLReader::open($filename));
    }

    public function readFromString(string $content): GeoData
    {
        return $this->read(XMLReader::XML($content));
    }

    protected function read(XMLReader $xml): GeoData
    {
        $geoData = new GeoData();
        $geoData->originalFormat = 'KML';

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->localName) {
                    case 'kml':
                        // Handle namespaces
                        $this->handleNamespaces($xml);
                        break;

                    case 'Document':
                    case 'Folder':
                        $this->readFolder($xml, $geoData);
                        break;

                    case 'Placemark':
                        $this->readPlacemark($xml, $geoData);
                        break;

                    // TODO: Handle more KML elements
                    default:
                        // Skip or handle other elements as needed
                        break;
                }
            }
        }

        $xml->close();

        return $geoData;
    }

    protected function handleNamespaces(XMLReader $xml): void
    {
        $xml->setParserProperty(XMLReader::SUBST_ENTITIES, true);
        
        // Note: XMLReader namespace handling is limited in PHP
        // We'll handle namespaces by using localName property instead
    }

    protected function readFolder(XMLReader $xml, GeoData $geoData): void
    {
        if ($xml->isEmptyElement) return;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && in_array($xml->localName, ['Document', 'Folder'])) {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->localName) {
                    case 'Placemark':
                        $this->readPlacemark($xml, $geoData);
                        break;

                    // TODO: Handle other folder contents
                    default:
                        break;
                }
            }
        }
    }

    protected function readPlacemark(XMLReader $xml, GeoData $geoData): void
    {
        $feature = new GeoFeature();
        $geometry = null;

        if ($xml->isEmptyElement) {
            $geoData->features->add($feature);
            return;
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->localName == 'Placemark') {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->localName) {
                    case 'name':
                        $xml->read();
                        $feature->properties['name'] = $xml->value;
                        break;

                    case 'desc':
                        $xml->read();
                        $feature->properties['description'] = $xml->value;
                        break;

                    case 'Point':
                        $geometry = $this->readPoint($xml);
                        break;

                    case 'LineString':
                        $geometry = $this->readLineString($xml);
                        break;

                    case 'Polygon':
                        $geometry = $this->readPolygon($xml);
                        break;

                    // TODO: Handle other geometry types
                    default:
                        break;
                }
            }
        }

        if ($geometry) {
            $feature->geometry = $geometry;
        }

        $geoData->features->add($feature);
    }

    protected function readPoint(XMLReader $xml): array
    {
        $coordinates = null;

        if ($xml->isEmptyElement) {
            return ['type' => 'Point', 'coordinates' => []];
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->localName == 'Point') {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'coordinates') {
                $xml->read();
                $coordinates = $xml->value;
                break;
            }
        }

        if (!$coordinates) {
            return ['type' => 'Point', 'coordinates' => []];
        }

        // KML coordinates format: longitude,latitude,elevation
        $parts = array_map('trim', explode(',', $coordinates));
        
        return [
            'type' => 'Point',
            'coordinates' => [
                'longitude' => isset($parts[0]) ? (float)$parts[0] : null,
                'latitude' => isset($parts[1]) ? (float)$parts[1] : null,
                'elevation' => isset($parts[2]) ? (float)$parts[2] : null
            ]
        ];
    }

    protected function readLineString(XMLReader $xml): array
    {
        $coordinates = [];

        if ($xml->isEmptyElement) {
            return ['type' => 'LineString', 'coordinates' => []];
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->localName == 'LineString') {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'coordinates') {
                $xml->read();
                $coordString = $xml->value;
                
                // KML coordinates can be multiple space-separated points
                $pointStrings = array_map('trim', preg_split('/\s+/', $coordString));
                
                foreach ($pointStrings as $pointString) {
                    $parts = array_map('trim', explode(',', $pointString));
                    $coordinates[] = [
                        'longitude' => isset($parts[0]) ? (float)$parts[0] : null,
                        'latitude' => isset($parts[1]) ? (float)$parts[1] : null,
                        'elevation' => isset($parts[2]) ? (float)$parts[2] : null
                    ];
                }
                
                break;
            }
        }

        return [
            'type' => 'LineString',
            'coordinates' => $coordinates
        ];
    }

    protected function readPolygon(XMLReader $xml): array
    {
        $rings = [];

        if ($xml->isEmptyElement) {
            return ['type' => 'Polygon', 'coordinates' => []];
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->localName == 'Polygon') {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'outerBoundaryIs') {
                $this->readBoundary($xml, $rings);
            } elseif ($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'innerBoundaryIs') {
                $this->readBoundary($xml, $rings);
            }
        }

        return [
            'type' => 'Polygon',
            'coordinates' => $rings
        ];
    }

    protected function readBoundary(XMLReader $xml, array &$rings): void
    {
        if ($xml->isEmptyElement) return;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && in_array($xml->localName, ['outerBoundaryIs', 'innerBoundaryIs'])) {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'LinearRing') {
                $this->readLinearRing($xml, $rings);
            }
        }
    }

    protected function readLinearRing(XMLReader $xml, array &$rings): void
    {
        $ring = [];

        if ($xml->isEmptyElement) {
            $rings[] = $ring;
            return;
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->localName == 'LinearRing') {
                break;
            }

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->localName == 'coordinates') {
                $xml->read();
                $coordString = $xml->value;
                
                // KML coordinates can be multiple space-separated points
                $pointStrings = array_map('trim', preg_split('/\s+/', $coordString));
                
                foreach ($pointStrings as $pointString) {
                    $parts = array_map('trim', explode(',', $pointString));
                    $ring[] = [
                        'longitude' => isset($parts[0]) ? (float)$parts[0] : null,
                        'latitude' => isset($parts[1]) ? (float)$parts[1] : null,
                        'elevation' => isset($parts[2]) ? (float)$parts[2] : null
                    ];
                }
                
                break;
            }
        }

        $rings[] = $ring;
    }
}
