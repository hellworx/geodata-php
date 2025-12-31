<?php

namespace Hellworx\Geodata\Collection;

use Hellworx\Geodata\Model\GeoFeature;
use Hellworx\Base\Collection\Collection;

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
