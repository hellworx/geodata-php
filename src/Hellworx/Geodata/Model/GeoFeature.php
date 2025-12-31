<?php

namespace Hellworx\Geodata\Model;

use Hellworx\Base\Model\Model;

/**
 * Represents a GeoJSON feature with geometry and properties
 */
class GeoFeature extends Model
{
    /**
     * Feature geometry (can be Point, LineString, Polygon, etc.)
     */
    public ?array $geometry = null;

    /**
     * Feature properties
     */
    public ?array $properties = null;

    /**
     * Feature ID
     */
    public ?string $id = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => $this->geometry,
            'properties' => $this->properties,
            'id' => $this->id
        ];
    }
}
