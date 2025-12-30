<?php

namespace HWX\Geodata\Collection;

use HWX\Geodata\Model\GeoPolygon;
use HWX\Base\Collection\Collection;

/**
 * Collection of GeoPolygon objects
 */
class GeoPolygonCollection extends Collection
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return GeoPolygon::class;
    }
}
