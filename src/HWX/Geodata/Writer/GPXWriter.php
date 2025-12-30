<?php

namespace HWX\Geodata\Writer;

use HWX\Geodata\Model\GeoData;
use HWX\Geodata\Model\GeoPoint;
use HWX\Geodata\Model\GeoLineString;
use HWX\Geodata\Model\GeoMetadata;
use HWX\Geodata\Model\GeoPerson;
use HWX\Geodata\Model\GeoEmail;
use HWX\Geodata\Model\GeoCopyright;
use HWX\Geodata\Model\GeoBounds;
use HWX\Geodata\Model\GeoFeature;
use XMLWriter;

/**
 * GPX format writer that converts from unified GeoData model
 */
class GPXWriter
{
    const GPX_XMLNS = 'http://www.topografix.com/GPX/1/1';

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

        $xml->startElement('gpx');
        $xml->writeAttribute('creator', $geoData->creator ?? 'GeoData PHP Library');
        $xml->writeAttribute('version', $geoData->version ?? '1.1');
        $xml->writeAttribute('xmlns', self::GPX_XMLNS);

        if ($geoData->metadata) $this->writeMetadata($geoData->metadata, $xml);
        
        // Write points
        foreach ($geoData->points as $point) {
            $this->writeWaypoint($point, $xml);
        }

        // Write linestrings
        foreach ($geoData->linestrings as $lineString) {
            $this->writeLineString($lineString, $xml);
        }

        // Write features (convert to appropriate GPX elements)
        foreach ($geoData->features as $feature) {
            if ($feature->geometry) {
                switch ($feature->geometry['type']) {
                    case 'Point':
                        $this->writeFeatureAsWaypoint($feature, $xml);
                        break;
                    case 'LineString':
                        $this->writeFeatureAsTrack($feature, $xml);
                        break;
                    // Add more geometry types as needed
                }
            }
        }

        $xml->endElement();
    }

    protected function writeFeatureAsWaypoint(GeoFeature $feature, XMLWriter $xml)
    {
        $pointData = $feature->geometry['coordinates'];
        
        $xml->startElement('wpt');
        $xml->writeAttribute('lat', $pointData['latitude']);
        $xml->writeAttribute('lon', $pointData['longitude']);
        
        if ($pointData['elevation']) $xml->writeElement('ele', $pointData['elevation']);
        if (isset($feature->properties['name']) && $feature->properties['name']) $xml->writeElement('name', $feature->properties['name']);
        if (isset($feature->properties['description']) && $feature->properties['description']) $xml->writeElement('desc', $feature->properties['description']);

        $xml->endElement();
    }

    protected function writeFeatureAsTrack(GeoFeature $feature, XMLWriter $xml)
    {
        $lineStringData = $feature->geometry['coordinates'];
        
        $xml->startElement('trk');
        
        if (isset($feature->properties['name']) && $feature->properties['name']) $xml->writeElement('name', $feature->properties['name']);
        if (isset($feature->properties['description']) && $feature->properties['description']) $xml->writeElement('desc', $feature->properties['description']);
        
        $xml->startElement('trkseg');
        
        foreach ($lineStringData as $point) {
            $xml->startElement('trkpt');
            $xml->writeAttribute('lat', $point['latitude']);
            $xml->writeAttribute('lon', $point['longitude']);
            
            if ($point['elevation']) $xml->writeElement('ele', $point['elevation']);
            
            $xml->endElement();
        }
        
        $xml->endElement(); // trkseg
        $xml->endElement(); // trk
    }

    protected function writeMetadata(GeoMetadata $metadata, XMLWriter $xml)
    {
        $xml->startElement('metadata');
        
        if ($metadata->name) $xml->writeElement('name', $metadata->name);
        if ($metadata->description) $xml->writeElement('desc', $metadata->description);
        
        if ($metadata->author) $this->writePerson($metadata->author, $xml, 'author');
        if ($metadata->copyright) $this->writeCopyright($metadata->copyright, $xml);
        
        if ($metadata->time) $this->writeTime($metadata->time, $xml);
        if ($metadata->keywords) $xml->writeElement('keywords', $metadata->keywords);
        
        if ($metadata->bounds) $this->writeBounds($metadata->bounds, $xml);

        $xml->endElement();
    }

    protected function writeWaypoint(GeoPoint $waypoint, XMLWriter $xml, string $tag = 'wpt')
    {
        $xml->startElement($tag);
        $xml->writeAttribute('lat', $waypoint->latitude);
        $xml->writeAttribute('lon', $waypoint->longitude);
        
        if ($waypoint->elevation) $xml->writeElement('ele', $waypoint->elevation);
        if ($waypoint->time) $this->writeTime($waypoint->time, $xml);
        if ($waypoint->magneticVariation) $xml->writeElement('magvar', $waypoint->magneticVariation);
        if ($waypoint->geoidHeight) $xml->writeElement('geoidheight', $waypoint->geoidHeight);
        if ($waypoint->name) $xml->writeElement('name', $waypoint->name);
        if ($waypoint->comment) $xml->writeElement('cmt', $waypoint->comment);
        if ($waypoint->description) $xml->writeElement('desc', $waypoint->description);
        if ($waypoint->source) $xml->writeElement('src', $waypoint->source);
        if ($waypoint->symbol) $xml->writeElement('sym', $waypoint->symbol);
        if ($waypoint->type) $xml->writeElement('type', $waypoint->type);
        if ($waypoint->fix) $xml->writeElement('fix', $waypoint->fix);
        if ($waypoint->satellites) $xml->writeElement('sat', $waypoint->satellites);
        if ($waypoint->horizontalDilution) $xml->writeElement('hdop', $waypoint->horizontalDilution);
        if ($waypoint->verticalDilution) $xml->writeElement('vdop', $waypoint->verticalDilution);
        if ($waypoint->positionDilution) $xml->writeElement('pdop', $waypoint->positionDilution);
        if ($waypoint->ageOfDgpsData) $xml->writeElement('ageofdgpsdata', $waypoint->ageOfDgpsData);
        if ($waypoint->dgpsId) $xml->writeElement('dgpsid', $waypoint->dgpsId);

        $xml->endElement();
    }

    protected function writeLineString(GeoLineString $lineString, XMLWriter $xml)
    {
        $xml->startElement('trk');
        
        if ($lineString->name) $xml->writeElement('name', $lineString->name);
        if ($lineString->description) $xml->writeElement('desc', $lineString->description);
        
        $xml->startElement('trkseg');
        
        foreach ($lineString->points as $point) {
            $this->writeWaypoint($point, $xml, 'trkpt');
        }
        
        $xml->endElement(); // trkseg
        $xml->endElement(); // trk
    }

    protected function writePerson(GeoPerson $person, XMLWriter $xml, string $tag = 'person')
    {
        $xml->startElement($tag);
        
        if ($person->name) $xml->writeElement('name', $person->name);
        
        if ($person->email) {
            $xml->startElement('email');
            $xml->writeAttribute('id', $person->email->id);
            $xml->writeAttribute('domain', $person->email->domain);
            $xml->endElement();
        }

        $xml->endElement();
    }

    protected function writeCopyright(GeoCopyright $copyright, XMLWriter $xml)
    {
        $xml->startElement('copyright');
        $xml->writeAttribute('author', $copyright->author);
        
        if ($copyright->year) $xml->writeElement('year', $copyright->year);
        if ($copyright->license) $xml->writeElement('license', $copyright->license);
        
        $xml->endElement();
    }

    protected function writeTime(\DateTime $dt, XMLWriter $xml)
    {
        $xml->writeElement('time', $dt->format(\DateTime::ISO8601));
    }

    protected function writeBounds(GeoBounds $bounds, XMLWriter $xml)
    {
        $xml->startElement('bounds');
        $xml->writeAttribute('minlat', $bounds->minLatitude);
        $xml->writeAttribute('minlon', $bounds->minLongitude);
        $xml->writeAttribute('maxlat', $bounds->maxLatitude);
        $xml->writeAttribute('maxlon', $bounds->maxLongitude);
        $xml->endElement();
    }
}
