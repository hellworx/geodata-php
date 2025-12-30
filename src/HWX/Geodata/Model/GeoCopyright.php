<?php

namespace HWX\Geodata\Model;

use HWX\Base\Model\Model;

/**
 * Copyright information container
 */
class GeoCopyright extends Model
{
    /**
     * Copyright author
     */
    public ?string $author = null;

    /**
     * Copyright year
     */
    public ?string $year = null;

    /**
     * License information
     */
    public ?string $license = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'author' => $this->author,
            'year' => $this->year,
            'license' => $this->license
        ];
    }
}
