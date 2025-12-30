<?php

namespace HWX\Geodata\Model;

use HWX\Base\Model\Model;

/**
 * Person/author information container
 */
class GeoPerson extends Model
{
    /**
     * Name of the person
     */
    public ?string $name = null;

    /**
     * Email information
     */
    public ?GeoEmail $email = null;

    /**
     * Website link
     */
    public ?string $link = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email ? $this->email->toArray() : null,
            'link' => $this->link
        ];
    }
}
