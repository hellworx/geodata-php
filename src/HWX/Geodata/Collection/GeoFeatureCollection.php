<?php

namespace HWX\Geodata\Collection;

use HWX\Geodata\Model\GeoFeature;
use HWX\Base\Collection\Collection;

/**
 * Collection of GeoFeature objects
 */
class GeoFeatureCollection extends Collection
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return GeoFeature::class;
    }
}
