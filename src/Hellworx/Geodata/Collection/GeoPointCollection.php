<?php

namespace Hellworx\Geodata\Collection;

use Hellworx\Geodata\Model\GeoPoint;
use Hellworx\Base\Collection\Collection;

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
