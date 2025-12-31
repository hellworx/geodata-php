<?php

namespace Hellworx\Geodata\Reader;

use Hellworx\Geodata\Model\GeoData;
use ZipArchive;

/**
 * KMZ format reader that converts to unified GeoData model
 * KMZ is just a ZIP file containing KML files and other resources
 */
class KMZReader
{
    public function readFromFile(string $filename): GeoData
    {
        $zip = new ZipArchive();
        
        if ($zip->open($filename) !== true) {
            throw new \Exception("Failed to open KMZ file: $filename");
        }

        // Find the main KML file (usually doc.kml or similar)
        $kmlFilename = $this->findMainKMLFile($zip);
        
        if (!$kmlFilename) {
            $zip->close();
            throw new \Exception("No KML file found in KMZ archive");
        }

        // Read KML content from zip
        $kmlContent = $zip->getFromName($kmlFilename);
        $zip->close();

        // Use KML reader to parse the content
        $kmlReader = new KMLReader();
        return $kmlReader->readFromString($kmlContent);
    }

    public function readFromString(string $content): GeoData
    {
        throw new \Exception("KMZ format does not support string input - use file-based methods only");
    }

    /**
     * Find the main KML file in the KMZ archive
     * @param ZipArchive $zip
     * @return string|null Filename of main KML file or null if not found
     */
    protected function findMainKMLFile(ZipArchive $zip): ?string
    {
        // Check for common main KML filenames
        $commonNames = ['doc.kml', 'main.kml', 'default.kml'];
        
        foreach ($commonNames as $name) {
            if ($zip->locateName($name) !== false) {
                return $name;
            }
        }

        // If no common name found, look for any KML file
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $extension = strtolower(pathinfo($stat['name'], PATHINFO_EXTENSION));
            
            if ($extension === 'kml') {
                return $stat['name'];
            }
        }

        return null;
    }
}
