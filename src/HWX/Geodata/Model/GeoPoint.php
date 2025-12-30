<?php

namespace HWX\Geodata\Model;

use HWX\Base\Model\Model;

/**
 * Represents a geographic point with latitude, longitude and optional attributes
 */
class GeoPoint extends Model
{
    /**
     * Latitude in decimal degrees (-90.0 to 90.0)
     */
    public ?float $latitude = null;

    /**
     * Longitude in decimal degrees (-180.0 to 180.0)
     */
    public ?float $longitude = null;

    /**
     * Elevation in meters (optional)
     */
    public ?float $elevation = null;

    /**
     * Time of the point measurement
     */
    public ?\DateTime $time = null;

    /**
     * Magnetic variation
     */
    public ?float $magneticVariation = null;

    /**
     * Geoid height
     */
    public ?float $geoidHeight = null;

    /**
     * Name of the point
     */
    public ?string $name = null;

    /**
     * Comment about the point
     */
    public ?string $comment = null;

    /**
     * Detailed description
     */
    public ?string $description = null;

    /**
     * Source of the data
     */
    public ?string $source = null;

    /**
     * Symbol/icon identifier
     */
    public ?string $symbol = null;

    /**
     * Type/category of the point
     */
    public ?string $type = null;

    /**
     * Fix type (GPS fix quality)
     */
    public ?string $fix = null;

    /**
     * Number of satellites used
     */
    public ?int $satellites = null;

    /**
     * Horizontal dilution of precision
     */
    public ?float $horizontalDilution = null;

    /**
     * Vertical dilution of precision
     */
    public ?float $verticalDilution = null;

    /**
     * Position dilution of precision
     */
    public ?float $positionDilution = null;

    /**
     * Age of DGPS data
     */
    public ?float $ageOfDgpsData = null;

    /**
     * DGPS station ID
     */
    public ?string $dgpsId = null;

    /**
     * Additional custom attributes
     */
    public ?array $attributes = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'elevation' => $this->elevation,
            'time' => $this->time ? $this->time->format(\DateTime::ISO8601) : null,
            'magneticVariation' => $this->magneticVariation,
            'geoidHeight' => $this->geoidHeight,
            'name' => $this->name,
            'comment' => $this->comment,
            'description' => $this->description,
            'source' => $this->source,
            'symbol' => $this->symbol,
            'type' => $this->type,
            'fix' => $this->fix,
            'satellites' => $this->satellites,
            'horizontalDilution' => $this->horizontalDilution,
            'verticalDilution' => $this->verticalDilution,
            'positionDilution' => $this->positionDilution,
            'ageOfDgpsData' => $this->ageOfDgpsData,
            'dgpsId' => $this->dgpsId,
            'attributes' => $this->attributes
        ];
    }
}
