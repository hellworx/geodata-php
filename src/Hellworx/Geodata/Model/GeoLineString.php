<?php

namespace Hellworx\Geodata\Model;

use Hellworx\Base\Model\Model;

/**
 * Represents a linestring (sequence of points)
 */
class GeoLineString extends Model
{
    /**
     * Array of GeoPoint objects
     */
    public array $points = [];

    /**
     * Name of the linestring
     */
    public ?string $name = null;

    /**
     * Description of the linestring
     */
    public ?string $description = null;

    /**
     * Type/category of the linestring
     */
    public ?string $type = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        $pointArray = [];
        foreach ($this->points as $point) {
            $pointArray[] = $point->toArray();
        }
        
        return [
            'points' => $pointArray,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type
        ];
    }
}
