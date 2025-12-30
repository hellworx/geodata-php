<?php

namespace HWX\Geodata\Writer;

use HWX\Geodata\Model\GeoData;
use HWX\Geodata\Model\GeoFeature;
use HWX\Geodata\Model\GeoPoint;
use HWX\Geodata\Model\GeoLineString;
use XMLWriter;

/**
 * KML format writer that converts from unified GeoData model
 */
class KMLWriter
{
    const KML_XMLNS = 'http://www.opengis.net/kml/2.2';
    const GX_XMLNS = 'http://www.google.com/kml/ext/2.2';
    const ATOM_XMLNS = 'http://www.w3.org/2005/Atom';

    public function writeToFile(GeoData $geoData, string $filename)
    {
        $xml = new XMLWriter();
        $xml->openUri($filename);
        $this->write($geoData, $xml);
        $xml->flush();
    }

    public function writeToString(GeoData $geoData): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $this->write($geoData, $xml);
        return $xml->flush();
    }

    protected function write(GeoData $geoData, XMLWriter $xml)
    {
        $xml->startDocument('1.0', 'utf-8');
        $xml->setIndent(true);
        $xml->setIndentString('  ');

        $xml->startElement('kml');
        $xml->writeAttribute('xmlns', self::KML_XMLNS);
        $xml->writeAttribute('xmlns:gx', self::GX_XMLNS);
        $xml->writeAttribute('xmlns:atom', self::ATOM_XMLNS);

        // Create Document element
        $xml->startElement('Document');
        
        // Add metadata if available
        if ($geoData->metadata) {
            $this->writeMetadata($geoData->metadata, $xml);
        }

        // Convert features to KML Placemarks
        foreach ($geoData->features as $feature) {
            $this->writeFeature($feature, $xml);
        }

        // Convert points to KML Placemarks
        foreach ($geoData->points as $point) {
            $this->writePoint($point, $xml);
        }

        // Convert linestrings to KML Placemarks
        foreach ($geoData->linestrings as $lineString) {
            $this->writeLineString($lineString, $xml);
        }

        // Convert polygons to KML Placemarks
        foreach ($geoData->polygons as $polygon) {
            $this->writePolygon($polygon, $xml);
        }

        $xml->endElement(); // Document
        $xml->endElement(); // kml
    }

    protected function writeMetadata(\HWX\Geodata\Model\GeoMetadata $metadata, XMLWriter $xml): void
    {
        $xml->startElement('Schema');
        $xml->writeAttribute('name', 'Metadata');
        
        // TODO: Implement metadata writing
        // This is a simplified version - KML metadata handling is more complex
        
        $xml->endElement();
    }

    protected function writeFeature(GeoFeature $feature, XMLWriter $xml): void
    {
        $xml->startElement('Placemark');

        // Write feature properties as name/desc
        if (!empty($feature->properties)) {
            if (isset($feature->properties['name'])) {
                $xml->writeElement('name', $feature->properties['name']);
            }
            if (isset($feature->properties['description'])) {
                $xml->writeElement('desc', $feature->properties['description']);
            }
        }

        // Write geometry if available
        if ($feature->geometry) {
            $this->writeGeometry($feature->geometry, $xml);
        }

        $xml->endElement(); // Placemark
    }

    protected function writePoint($pointData, XMLWriter $xml): void
    {
        $xml->startElement('Placemark');

        // Handle both array and GeoPoint object inputs
        $point = new \stdClass();
        
        if ($pointData instanceof GeoPoint) {
            $point->latitude = $pointData->latitude;
            $point->longitude = $pointData->longitude;
            $point->elevation = $pointData->elevation;
            $point->name = $pointData->name;
            $point->description = $pointData->description;
        } else {
            $point->latitude = $pointData['latitude'] ?? null;
            $point->longitude = $pointData['longitude'] ?? null;
            $point->elevation = $pointData['elevation'] ?? null;
            $point->name = $pointData['name'] ?? null;
            $point->description = $pointData['description'] ?? null;
        }

        if ($point->name) {
            $xml->writeElement('name', $point->name);
        }
        if ($point->description) {
            $xml->writeElement('desc', $point->description);
        }

        // Write Point geometry
        $xml->startElement('Point');
        
        // KML coordinates format: longitude,latitude,elevation
        $coordinates = [];
        if ($point->longitude) $coordinates[] = $point->longitude;
        if ($point->latitude) $coordinates[] = $point->latitude;
        if ($point->elevation) $coordinates[] = $point->elevation;

        if (!empty($coordinates)) {
            $xml->writeElement('coordinates', implode(',', $coordinates));
        }

        $xml->endElement(); // Point
        $xml->endElement(); // Placemark
    }

    protected function writeLineString($lineStringData, XMLWriter $xml): void
    {
        $xml->startElement('Placemark');

        // Write line string properties
        $lineString = new \stdClass();
        
        if ($lineStringData instanceof GeoLineString) {
            $lineString->name = $lineStringData->name;
            $lineString->description = $lineStringData->description;
            $lineString->points = $lineStringData->points;
        } else {
            $lineString->name = $lineStringData['name'] ?? null;
            $lineString->description = $lineStringData['description'] ?? null;
            $lineString->points = $lineStringData['points'] ?? [];
        }

        if ($lineString->name) {
            $xml->writeElement('name', $lineString->name);
        }
        if ($lineString->description) {
            $xml->writeElement('desc', $lineString->description);
        }

        // Write LineString geometry
        $xml->startElement('LineString');

        // Build coordinates string
        $coordinateStrings = [];
        foreach ($lineString->points as $point) {
            $coords = [];
            if ($point instanceof GeoPoint) {
                if ($point->longitude) $coords[] = $point->longitude;
                if ($point->latitude) $coords[] = $point->latitude;
                if ($point->elevation) $coords[] = $point->elevation;
            } else {
                if (isset($point['longitude'])) $coords[] = $point['longitude'];
                if (isset($point['latitude'])) $coords[] = $point['latitude'];
                if (isset($point['elevation'])) $coords[] = $point['elevation'];
            }
            
            if (!empty($coords)) {
                $coordinateStrings[] = implode(',', $coords);
            }
        }

        if (!empty($coordinateStrings)) {
            $xml->writeElement('coordinates', implode(' ', $coordinateStrings));
        }

        $xml->endElement(); // LineString
        $xml->endElement(); // Placemark
    }

    protected function writeGeometry(array $geometry, XMLWriter $xml): void
    {
        switch ($geometry['type']) {
            case 'Point':
                $this->writePointGeometry($geometry['coordinates'], $xml);
                break;

            case 'LineString':
                $this->writeLineStringGeometry($geometry['coordinates'], $xml);
                break;

            case 'Polygon':
                $this->writePolygonGeometry($geometry['coordinates'], $xml);
                break;

            case 'GeometryCollection':
                $this->writeGeometryCollection($geometry['coordinates'], $xml);
                break;

            // TODO: Implement other geometry types (MultiPoint, MultiLineString, MultiPolygon)
            default:
                throw new \Exception('Unsupported geometry type for KML: ' . $geometry['type']);
        }
    }

    protected function writeGeometryCollection(array $geometries, XMLWriter $xml): void
    {
        $xml->startElement('MultiGeometry');
        
        foreach ($geometries as $geometry) {
            // Handle each geometry type individually
            switch ($geometry['type']) {
                case 'Point':
                    $this->writePointGeometry($geometry['coordinates'], $xml);
                    break;
                    
                case 'LineString':
                    $this->writeLineStringGeometry($geometry['coordinates'], $xml);
                    break;
                    
                case 'Polygon':
                    $this->writePolygonGeometry($geometry['coordinates'], $xml);
                    break;
                    
                default:
                    // Skip unsupported geometry types in collection
                    break;
            }
        }
        
        $xml->endElement(); // MultiGeometry
    }

    protected function writePointGeometry(array $coordinates, XMLWriter $xml): void
    {
        $xml->startElement('Point');
        
        // KML coordinates format: longitude,latitude,elevation
        $coordParts = [];
        if (isset($coordinates['longitude'])) $coordParts[] = $coordinates['longitude'];
        if (isset($coordinates['latitude'])) $coordParts[] = $coordinates['latitude'];
        if (isset($coordinates['elevation'])) $coordParts[] = $coordinates['elevation'];

        if (!empty($coordParts)) {
            $xml->writeElement('coordinates', implode(',', $coordParts));
        }

        $xml->endElement(); // Point
    }

    protected function writeLineStringGeometry(array $coordinates, XMLWriter $xml): void
    {
        $xml->startElement('LineString');

        $coordStrings = [];
        foreach ($coordinates as $point) {
            $parts = [];
            if (isset($point['longitude'])) $parts[] = $point['longitude'];
            if (isset($point['latitude'])) $parts[] = $point['latitude'];
            if (isset($point['elevation'])) $parts[] = $point['elevation'];
            
            if (!empty($parts)) {
                $coordStrings[] = implode(',', $parts);
            }
        }

        if (!empty($coordStrings)) {
            $xml->writeElement('coordinates', implode(' ', $coordStrings));
        }

        $xml->endElement(); // LineString
    }

    protected function writePolygon($polygonData, XMLWriter $xml): void
    {
        $xml->startElement('Placemark');

        // Handle both array and GeoPolygon object inputs
        $polygon = new \stdClass();
        
        if (is_object($polygonData) && get_class($polygonData) === 'HWX\Geodata\Model\GeoPolygon') {
            $polygon->name = $polygonData->name;
            $polygon->description = $polygonData->description;
            $polygon->rings = $polygonData->rings;
        } else {
            $polygon->name = $polygonData['name'] ?? null;
            $polygon->description = $polygonData['description'] ?? null;
            $polygon->rings = $polygonData['rings'] ?? [];
        }

        if ($polygon->name) {
            $xml->writeElement('name', $polygon->name);
        }
        if ($polygon->description) {
            $xml->writeElement('desc', $polygon->description);
        }

        // Write Polygon geometry
        $this->writePolygonGeometry($polygon->rings, $xml);

        $xml->endElement(); // Placemark
    }

    protected function writePolygonGeometry(array $coordinates, XMLWriter $xml): void
    {
        $xml->startElement('Polygon');

        // Write outer boundary
        if (!empty($coordinates)) {
            $xml->startElement('outerBoundaryIs');
            $xml->startElement('LinearRing');
            
            $coordStrings = [];
            foreach ($coordinates[0] as $point) {  // First ring is outer boundary
                $parts = [];
                if (isset($point['longitude'])) $parts[] = $point['longitude'];
                if (isset($point['latitude'])) $parts[] = $point['latitude'];
                if (isset($point['elevation'])) $parts[] = $point['elevation'];
                
                if (!empty($parts)) {
                    $coordStrings[] = implode(',', $parts);
                }
            }

            if (!empty($coordStrings)) {
                $xml->writeElement('coordinates', implode(' ', $coordStrings));
            }

            $xml->endElement(); // LinearRing
            $xml->endElement(); // outerBoundaryIs
        }

        // Write inner boundaries (holes)
        if (count($coordinates) > 1) {
            for ($i = 1; $i < count($coordinates); $i++) {
                $xml->startElement('innerBoundaryIs');
                $xml->startElement('LinearRing');
                
                $coordStrings = [];
                foreach ($coordinates[$i] as $point) {
                    $parts = [];
                    if (isset($point['longitude'])) $parts[] = $point['longitude'];
                    if (isset($point['latitude'])) $parts[] = $point['latitude'];
                    if (isset($point['elevation'])) $parts[] = $point['elevation'];
                    
                    if (!empty($parts)) {
                        $coordStrings[] = implode(',', $parts);
                    }
                }

                if (!empty($coordStrings)) {
                    $xml->writeElement('coordinates', implode(' ', $coordStrings));
                }

                $xml->endElement(); // LinearRing
                $xml->endElement(); // innerBoundaryIs
            }
        }

        $xml->endElement(); // Polygon
    }
}
