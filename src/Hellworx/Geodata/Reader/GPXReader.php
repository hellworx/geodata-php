<?php

namespace Hellworx\Geodata\Reader;

use Hellworx\Geodata\Model\GeoData;
use Hellworx\Geodata\Model\GeoPoint;
use Hellworx\Geodata\Model\GeoLineString;
use Hellworx\Geodata\Model\GeoMetadata;
use Hellworx\Geodata\Model\GeoPerson;
use Hellworx\Geodata\Model\GeoEmail;
use Hellworx\Geodata\Model\GeoCopyright;
use Hellworx\Geodata\Model\GeoBounds;
use XMLReader;

/**
 * GPX format reader that converts to unified GeoData model
 */
class GPXReader
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

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'gpx':
                        $geoData->version = $xml->getAttribute('version');
                        $geoData->creator = $xml->getAttribute('creator');
                        $geoData->originalFormat = 'GPX';
                        break;

                    case 'metadata':
                        $geoData->metadata = $this->readMetadata($xml);
                        break;

                    case 'wpt':
                        $geoData->points->add($this->readWaypoint($xml, 'wpt'));
                        break;

                    case 'rte':
                        $this->readRoute($xml, $geoData);
                        break;

                    case 'trk':
                        $this->readTrack($xml, $geoData);
                        break;
                }
            }
        }

        $xml->close();

        return $geoData;
    }

    protected function readMetadata(XMLReader $xml): GeoMetadata
    {
        $metadata = new GeoMetadata();

        if ($xml->isEmptyElement) return $metadata;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'metadata') break;
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'name':
                        $xml->read();
                        $metadata->name = $xml->value;
                        break;

                    case 'desc':
                        $xml->read();
                        $metadata->description = $xml->value;
                        break;

                    case 'author':
                        $metadata->author = $this->readAuthor($xml);
                        break;

                    case 'copyright':
                        $metadata->copyright = $this->readCopyright($xml);
                        break;

                    case 'link':
                        // TODO: Implement link support
                        break;

                    case 'time':
                        $xml->read();
                        $metadata->time = new \DateTime($xml->value);
                        break;

                    case 'keywords':
                        $xml->read();
                        $metadata->keywords = $xml->value;
                        break;

                    case 'bounds':
                        $metadata->bounds = new GeoBounds();
                        $metadata->bounds->minLatitude = (float)$xml->getAttribute('minlat');
                        $metadata->bounds->minLongitude = (float)$xml->getAttribute('minlon');
                        $metadata->bounds->maxLatitude = (float)$xml->getAttribute('maxlat');
                        $metadata->bounds->maxLongitude = (float)$xml->getAttribute('maxlon');
                        break;
                }
            }
        }

        return $metadata;
    }

    protected function readAuthor(XMLReader $xml): GeoPerson
    {
        $author = new GeoPerson();

        if ($xml->isEmptyElement) return $author;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'author') break;
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'name':
                        $xml->read();
                        $author->name = $xml->value;
                        break;

                    case 'email':
                        $author->email = new GeoEmail();
                        $author->email->id = $xml->getAttribute('id');
                        $author->email->domain = $xml->getAttribute('domain');
                        break;

                    case 'link':
                        // TODO: Implement link support
                        break;
                }
            }
        }

        return $author;
    }

    protected function readCopyright(XMLReader $xml): GeoCopyright
    {
        $copyright = new GeoCopyright();
        $copyright->author = $xml->getAttribute('author');

        if ($xml->isEmptyElement) return $copyright;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'copyright') break;
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'year':
                        $xml->read();
                        $copyright->year = $xml->value;
                        break;

                    case 'license':
                        $xml->read();
                        $copyright->license = $xml->value;
                        break;
                }
            }
        }

        return $copyright;
    }

    protected function readWaypoint(XMLReader $xml, string $name): GeoPoint
    {
        $waypoint = new GeoPoint();
        $waypoint->latitude = (float)$xml->getAttribute('lat');
        $waypoint->longitude = (float)$xml->getAttribute('lon');

        if ($xml->isEmptyElement) return $waypoint;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == $name) break;
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'ele':
                        $xml->read();
                        $waypoint->elevation = (float)$xml->value;
                        break;

                    case 'time':
                        $xml->read();
                        $waypoint->time = new \DateTime($xml->value);
                        break;

                    case 'magvar':
                        $xml->read();
                        $waypoint->magneticVariation = (float)$xml->value;
                        break;

                    case 'geoidheight':
                        $xml->read();
                        $waypoint->geoidHeight = (float)$xml->value;
                        break;

                    case 'name':
                        $xml->read();
                        $waypoint->name = $xml->value;
                        break;

                    case 'cmt':
                        $xml->read();
                        $waypoint->comment = $xml->value;
                        break;

                    case 'desc':
                        $xml->read();
                        $waypoint->description = $xml->value;
                        break;

                    case 'src':
                        $xml->read();
                        $waypoint->source = $xml->value;
                        break;

                    case 'link':
                        // TODO: Implement link support
                        break;

                    case 'sym':
                        $xml->read();
                        $waypoint->symbol = $xml->value;
                        break;

                    case 'type':
                        $xml->read();
                        $waypoint->type = $xml->value;
                        break;

                    case 'fix':
                        $xml->read();
                        $waypoint->fix = $xml->value;
                        break;

                    case 'sat':
                        $xml->read();
                        $waypoint->satellites = (int)$xml->value;
                        break;

                    case 'hdop':
                        $xml->read();
                        $waypoint->horizontalDilution = (float)$xml->value;
                        break;

                    case 'vdop':
                        $xml->read();
                        $waypoint->verticalDilution = (float)$xml->value;
                        break;

                    case 'pdop':
                        $xml->read();
                        $waypoint->positionDilution = (float)$xml->value;
                        break;

                    case 'ageofdgpsdata':
                        $xml->read();
                        $waypoint->ageOfDgpsData = (float)$xml->value;
                        break;

                    case 'dgpsid':
                        $xml->read();
                        $waypoint->dgpsId = $xml->value;
                        break;
                }
            }
        }

        return $waypoint;
    }

    protected function readRoute(XMLReader $xml, GeoData $geoData): void
    {
        $lineString = new GeoLineString();

        if ($xml->isEmptyElement) {
            $geoData->linestrings->add($lineString);
            return;
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'rte') break;
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'name':
                        $xml->read();
                        $lineString->name = $xml->value;
                        break;

                    case 'cmt':
                        $xml->read();
                        $lineString->description = $xml->value;
                        break;

                    case 'desc':
                        $xml->read();
                        $lineString->description = $xml->value;
                        break;

                    case 'rtept':
                        $lineString->points[] = $this->readWaypoint($xml, 'rtept');
                        break;
                }
            }
        }

        $geoData->linestrings->add($lineString);
    }

    protected function readTrack(XMLReader $xml, GeoData $geoData): void
    {
        $lineString = new GeoLineString();

        if ($xml->isEmptyElement) {
            $geoData->linestrings->add($lineString);
            return;
        }

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'trk') break;
            if ($xml->nodeType == XMLReader::ELEMENT) {
                switch ($xml->name) {
                    case 'name':
                        $xml->read();
                        $lineString->name = $xml->value;
                        break;

                    case 'cmt':
                        $xml->read();
                        $lineString->description = $xml->value;
                        break;

                    case 'desc':
                        $xml->read();
                        $lineString->description = $xml->value;
                        break;

                    case 'trkseg':
                        $this->readTrackSegment($xml, $lineString);
                        break;
                }
            }
        }

        $geoData->linestrings->add($lineString);
    }

    protected function readTrackSegment(XMLReader $xml, GeoLineString $lineString): void
    {
        if ($xml->isEmptyElement) return;

        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'trkseg') break;
            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'trkpt') {
                $lineString->points[] = $this->readWaypoint($xml, 'trkpt');
            }
        }
    }
}
