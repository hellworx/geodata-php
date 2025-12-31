<?php

namespace Hellworx\Geodata\Collection;

use Hellworx\Geodata\Model\GeoPolygon;
use Hellworx\Base\Collection\Collection;

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
