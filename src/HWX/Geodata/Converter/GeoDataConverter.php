<?php

namespace HWX\Geodata\Converter;

use HWX\Geodata\Model\GeoData;
use HWX\Geodata\Reader\GPXReader;
use HWX\Geodata\Reader\KMLReader;
use HWX\Geodata\Reader\KMZReader;
use HWX\Geodata\Reader\GeoJSONReader;
use HWX\Geodata\Writer\GPXWriter;
use HWX\Geodata\Writer\KMLWriter;
use HWX\Geodata\Writer\KMZWriter;
use HWX\Geodata\Writer\GeoJSONWriter;

/**
 * Conversion service to convert between different geospatial formats
 */
class GeoDataConverter
{
    /**
     * Convert from one format to another
     * 
     * @param string $inputContent Content to convert
     * @param string $inputFormat Input format (GPX, KML, KMZ, GeoJSON)
     * @param string $outputFormat Output format (GPX, KML, KMZ, GeoJSON)
     * @return string Converted content
     * @throws \Exception If input or output format is not supported
     */
    public function convert(string $inputContent, string $inputFormat, string $outputFormat): string
    {
        // Convert to unified model first
        $geoData = $this->toUnifiedModel($inputContent, $inputFormat);
        
        // Convert from unified model to target format
        return $this->fromUnifiedModel($geoData, $outputFormat);
    }

    /**
     * Convert content to unified GeoData model
     * 
     * @param string $content Input content
     * @param string $format Input format
     * @return GeoData Unified model
     * @throws \Exception If format is not supported
     */
    public function toUnifiedModel(string $content, string $format): GeoData
    {
        $format = strtoupper($format);
        
        switch ($format) {
            case 'GPX':
                return (new GPXReader())->readFromString($content);
            case 'KML':
                return (new KMLReader())->readFromString($content);
            case 'KMZ':
                return (new KMZReader())->readFromString($content);
            case 'GEOJSON':
            case 'JSON':
                return (new GeoJSONReader())->readFromString($content);
            default:
                throw new \Exception("Unsupported input format: $format");
        }
    }

    /**
     * Convert unified GeoData model to specific format
     * 
     * @param GeoData $geoData Unified model
     * @param string $format Output format
     * @return string Formatted content
     * @throws \Exception If format is not supported
     */
    public function fromUnifiedModel(GeoData $geoData, string $format): string
    {
        $format = strtoupper($format);
        
        switch ($format) {
            case 'GPX':
                return (new GPXWriter())->writeToString($geoData);
            case 'KML':
                return (new KMLWriter())->writeToString($geoData);
            case 'KMZ':
                return (new KMZWriter())->writeToString($geoData);
            case 'GEOJSON':
            case 'JSON':
                return (new GeoJSONWriter())->writeToString($geoData);
            default:
                throw new \Exception("Unsupported output format: $format");
        }
    }

    /**
     * Convert from file to unified model
     * 
     * @param string $filename Input file path
     * @param string $format Input format (optional, auto-detect if not provided)
     * @return GeoData Unified model
     * @throws \Exception If format is not supported or file not found
     */
    public function convertFileToModel(string $filename, string $format = null): GeoData
    {
        if (!$format) {
            $format = $this->detectFormatFromFile($filename);
        }

        $format = strtoupper($format);
        
        switch ($format) {
            case 'GPX':
                return (new GPXReader())->readFromFile($filename);
            case 'KML':
                return (new KMLReader())->readFromFile($filename);
            case 'KMZ':
                return (new KMZReader())->readFromFile($filename);
            case 'GEOJSON':
            case 'JSON':
                return (new GeoJSONReader())->readFromFile($filename);
            default:
                throw new \Exception("Unsupported input format: $format");
        }
    }

    /**
     * Convert unified model to file
     * 
     * @param GeoData $geoData Unified model
     * @param string $filename Output file path
     * @param string $format Output format (optional, detect from extension if not provided)
     * @return bool Success status
     * @throws \Exception If format is not supported
     */
    public function convertModelToFile(GeoData $geoData, string $filename, string $format = null): bool
    {
        if (!$format) {
            $format = $this->detectFormatFromFile($filename);
        }

        $format = strtoupper($format);
        
        switch ($format) {
            case 'GPX':
                (new GPXWriter())->writeToFile($geoData, $filename);
                return true;
            case 'KML':
                (new KMLWriter())->writeToFile($geoData, $filename);
                return true;
            case 'KMZ':
                (new KMZWriter())->writeToFile($geoData, $filename);
                return true;
            case 'GEOJSON':
            case 'JSON':
                (new GeoJSONWriter())->writeToFile($geoData, $filename);
                return true;
            default:
                throw new \Exception("Unsupported output format: $format");
        }
    }

    /**
     * Convert file to file (different formats)
     * 
     * @param string $inputFile Input file path
     * @param string $outputFile Output file path
     * @param string $inputFormat Input format (optional, auto-detect)
     * @param string $outputFormat Output format (optional, auto-detect from extension)
     * @return bool Success status
     * @throws \Exception If conversion fails
     */
    public function convertFile(string $inputFile, string $outputFile, string $inputFormat = null, string $outputFormat = null): bool
    {
        $inputFormat = $inputFormat ?: $this->detectFormatFromFile($inputFile);
        $outputFormat = $outputFormat ?: $this->detectFormatFromFile($outputFile);

        $geoData = $this->convertFileToModel($inputFile, $inputFormat);
        return $this->convertModelToFile($geoData, $outputFile, $outputFormat);
    }

    /**
     * Detect format from file extension
     * 
     * @param string $filename File path
     * @return string Detected format
     * @throws \Exception If format cannot be detected
     */
    protected function detectFormatFromFile(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'gpx':
                return 'GPX';
            case 'kml':
                return 'KML';
            case 'kmz':
                return 'KMZ';
            case 'json':
                return 'GEOJSON';
            default:
                throw new \Exception("Cannot detect format from file extension: $extension");
        }
    }
}
