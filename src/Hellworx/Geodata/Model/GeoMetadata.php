<?php

namespace Hellworx\Geodata\Model;

use Hellworx\Base\Model\Model;

/**
 * Metadata container for geospatial data
 */
class GeoMetadata extends Model
{
    /**
     * Name of the dataset
     */
    public ?string $name = null;

    /**
     * Description of the dataset
     */
    public ?string $description = null;

    /**
     * Author information
     */
    public ?GeoPerson $author = null;

    /**
     * Copyright information
     */
    public ?GeoCopyright $copyright = null;

    /**
     * Creation time
     */
    public ?\DateTime $time = null;

    /**
     * Keywords associated with the dataset
     */
    public ?string $keywords = null;

    /**
     * Bounding box of the dataset
     */
    public ?GeoBounds $bounds = null;

    /**
     * Additional links
     */
    public ?array $links = null;

    /**
     * Convert to array representation
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'author' => $this->author ? $this->author->toArray() : null,
            'copyright' => $this->copyright ? $this->copyright->toArray() : null,
            'time' => $this->time ? $this->time->format(\DateTime::ISO8601) : null,
            'keywords' => $this->keywords,
            'bounds' => $this->bounds ? $this->bounds->toArray() : null,
            'links' => $this->links
        ];
    }
}
