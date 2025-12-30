<?php

namespace HWX\Geodata\Model;

use HWX\Geodata\Collection\GeoFeatureCollection;
use HWX\Geodata\Collection\GeoPointCollection;
use HWX\Geodata\Collection\GeoLineStringCollection;
use HWX\Geodata\Collection\GeoPolygonCollection;
use HWX\Base\Model\Model;

/**
 * Main container for geospatial data with unified format abstraction
 */
class GeoData extends Model
{
    /**
     * Format version
     */
    public string $version = '1.0';

    /**
     * Creator/tool information
     */
    public ?string $creator = null;

    /**
     * Metadata about the dataset
     */
    public ?GeoMetadata $metadata = null;

    /**
     * Collection of points
     */
    public GeoPointCollection $points;

    /**
     * Collection of linestrings
     */
    public GeoLineStringCollection $linestrings;

    /**
     * Collection of polygons
     */
    public GeoPolygonCollection $polygons;

    /**
     * Collection of features
     */
    public GeoFeatureCollection $features;

    /**
     * Original format type (GPX, KML, KMZ, GeoJSON)
     */
    public ?string $originalFormat = null;

    public function __construct($mixed = null)
    {
        $this->points = new GeoPointCollection();
        $this->linestrings = new GeoLineStringCollection();
        $this->polygons = new GeoPolygonCollection();
        $this->features = new GeoFeatureCollection();
        parent::__construct($mixed);
    }

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'creator' => $this->creator,
            'metadata' => $this->metadata ? $this->metadata->toArray() : null,
            'points' => $this->points->toArray(),
            'linestrings' => $this->linestrings->toArray(),
            'polygons' => $this->polygons->toArray(),
            'features' => $this->features->toArray(),
            'originalFormat' => $this->originalFormat
        ];
    }
}
