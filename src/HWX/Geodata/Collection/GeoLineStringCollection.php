<?php

namespace HWX\Geodata\Collection;

use HWX\Geodata\Model\GeoLineString;
use HWX\Base\Collection\Collection;

/**
 * Collection of GeoLineString objects
 */
class GeoLineStringCollection extends Collection
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return GeoLineString::class;
    }
}
