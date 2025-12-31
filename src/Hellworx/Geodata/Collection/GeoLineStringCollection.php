<?php

namespace Hellworx\Geodata\Collection;

use Hellworx\Geodata\Model\GeoLineString;
use Hellworx\Base\Collection\Collection;

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
