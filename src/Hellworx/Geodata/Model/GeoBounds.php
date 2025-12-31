<?php

namespace Hellworx\Geodata\Model;

use Hellworx\Base\Model\Model;

/**
 * Bounding box container
 */
class GeoBounds extends Model
{
    /**
     * Minimum latitude
     */
    public ?float $minLatitude = null;

    /**
     * Minimum longitude
     */
    public ?float $minLongitude = null;

    /**
     * Maximum latitude
     */
    public ?float $maxLatitude = null;

    /**
     * Maximum longitude
     */
    public ?float $maxLongitude = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'minLatitude' => $this->minLatitude,
            'minLongitude' => $this->minLongitude,
            'maxLatitude' => $this->maxLatitude,
            'maxLongitude' => $this->maxLongitude
        ];
    }
}
