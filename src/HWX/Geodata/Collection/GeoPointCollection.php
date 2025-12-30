<?php

namespace HWX\Geodata\Collection;

use HWX\Geodata\Model\GeoPoint;
use HWX\Base\Collection\Collection;

/**
 * Collection of GeoPoint objects
 */
class GeoPointCollection extends Collection
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return GeoPoint::class;
    }
}
