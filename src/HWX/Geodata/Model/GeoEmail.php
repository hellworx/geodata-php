<?php

namespace HWX\Geodata\Model;

use HWX\Base\Model\Model;

/**
 * Email information container
 */
class GeoEmail extends Model
{
    /**
     * Email ID
     */
    public ?string $id = null;

    /**
     * Email domain
     */
    public ?string $domain = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->domain
        ];
    }
}
