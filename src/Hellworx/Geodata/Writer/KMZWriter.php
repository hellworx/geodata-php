<?php

namespace Hellworx\Geodata\Writer;

use Hellworx\Geodata\Model\GeoData;
use ZipArchive;
use Hellworx\Geodata\Writer\KMLWriter;

/**
 * KMZ format writer that converts from unified GeoData model
 * KMZ is just a ZIP file containing KML files and other resources
 */
class KMZWriter
{
    /**
     * Write GeoData to KMZ file
     * 
     * @param GeoData $geoData GeoData object to write
     * @param string $filename Output file path
     * @return void
     * @throws \Exception If file cannot be created or written
     */
    public function writeToFile(GeoData $geoData, string $filename)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Failed to create KMZ file: $filename");
        }

        // Convert GeoData to KML first
        $kmlContent = $this->convertToKML($geoData);
        
        // Add KML to ZIP (use doc.kml as the main file)
        $zip->addFromString('doc.kml', $kmlContent);
        
        // Add optional resources (none in this basic implementation)
        
        $zip->close();
    }

    /**
     * Write GeoData to KMZ string (not supported for KMZ format)
     * 
     * @param GeoData $geoData GeoData object to write
     * @return string KMZ content (actually a zip file)
     * @throws \Exception KMZ format does not support string output
     */
    public function writeToString(GeoData $geoData): string
    {
        throw new \Exception("KMZ format does not support string output - use file-based methods only");
    }

    /**
     * Convert GeoData to KML content
     * 
     * @param GeoData $geoData GeoData object to convert
     * @return string KML content
     */
    protected function convertToKML(GeoData $geoData): string
    {
        $kmlWriter = new KMLWriter();
        return $kmlWriter->writeToString($geoData);
    }
}
