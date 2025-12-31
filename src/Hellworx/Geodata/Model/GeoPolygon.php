<?php

namespace Hellworx\Geodata\Model;

use Hellworx\Base\Model\Model;

/**
 * Represents a polygon (closed loop of linestrings)
 */
class GeoPolygon extends Model
{
    /**
     * Array of GeoLineString objects (the rings of the polygon)
     */
    public array $rings = [];

    /**
     * Name of the polygon
     */
    public ?string $name = null;

    /**
     * Description of the polygon
     */
    public ?string $description = null;

    /**
     * Type/category of the polygon
     */
    public ?string $type = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        $ringArray = [];
        foreach ($this->rings as $ring) {
            $ringArray[] = $ring->toArray();
        }
        
        return [
            'rings' => $ringArray,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type
        ];
    }
}
