# Hellworx Geodata PHP Library

Hellworx Geodata PHP Library - A comprehensive library for working with geospatial data in PHP.

## Features

- **Multi-format support**: Read and write GPX, KML, KMZ, and GeoJSON formats
- **Unified data model**: Work with a single unified representation regardless of input/output format
- **Comprehensive data types**: Support for points, linestrings, polygons, and features
- **Metadata handling**: Complete support for GPS metadata including copyright, person, bounds, and more
- **Collection classes**: Efficient handling of multiple geospatial objects
- **Conversion utilities**: Easy conversion between different geospatial formats

## Requirements

- PHP ^7.4 || ^8.0
- hellworx/base-php

## Installation

```bash
composer require hellworx/geodata-php
```

## Quick Start

```php
use Hellworx\Geodata\Converter\GeoDataConverter;

// Create converter instance
$converter = new GeoDataConverter();

// Convert GPX file to GeoJSON
$geoData = $converter->convertFileToModel('path/to/file.gpx');
$result = $converter->convertModelToFile($geoData, 'output.json', 'GEOJSON');

// Convert between string formats
$gpxContent = file_get_contents('data.gpx');
$geoJson = $converter->convert($gpxContent, 'GPX', 'GEOJSON');
```

## Supported Formats

### Input Formats
- **GPX** - GPS Exchange Format
- **KML** - Keyhole Markup Language  
- **KMZ** - Compressed KML files
- **GeoJSON** - Geographic JSON

### Output Formats
- **GPX** - GPS Exchange Format
- **KML** - Keyhole Markup Language
- **KMZ** - Compressed KML files  
- **GeoJSON** - Geographic JSON

## Core Classes

### Models
- `Hellworx\Geodata\Model\GeoData` - Main container for all geospatial data
- `Hellworx\Geodata\Model\GeoPoint` - Geographic point with coordinates and metadata
- `Hellworx\Geodata\Model\GeoLineString` - Line string geometry
- `Hellworx\Geodata\Model\GeoPolygon` - Polygon geometry
- `Hellworx\Geodata\Model\GeoFeature` - Feature with geometry and properties

### Collections
- `Hellworx\Geodata\Collection\GeoPointCollection` - Collection of points
- `Hellworx\Geodata\Collection\GeoLineStringCollection` - Collection of linestrings
- `Hellworx\Geodata\Collection\GeoPolygonCollection` - Collection of polygons
- `Hellworx\Geodata\Collection\GeoFeatureCollection` - Collection of features

### Readers & Writers
- `Hellworx\Geodata\Reader\GPXReader` - Read GPX files
- `Hellworx\Geodata\Writer\GPXWriter` - Write GPX files
- `Hellworx\Geodata\Reader\KMLReader` - Read KML files
- `Hellworx\Geodata\Writer\KMLWriter` - Write KML files
- `Hellworx\Geodata\Reader\KMZReader` - Read KMZ files
- `Hellworx\Geodata\Writer\KMZWriter` - Write KMZ files
- `Hellworx\Geodata\Reader\GeoJSONReader` - Read GeoJSON files
- `Hellworx\Geodata\Writer\GeoJSONWriter` - Write GeoJSON files

### Converter
- `Hellworx\Geodata\Converter\GeoDataConverter` - Unified conversion interface

## Examples

See the `examples/` directory for comprehensive usage examples:

- `comprehensive_example.php` - Full-featured example showing all conversion types
- `conversion_example.php` - Simple conversion examples

## Development

```bash
# Install dependencies
make build

# Run linting
make lint

# Run tests  
make test

# Run all checks
make
```

## Testing

The library includes comprehensive tests covering:
- File format reading/writing
- Data model conversions
- Metadata handling
- Edge cases and error conditions

Run tests with:
```bash
make test
```

## Migration from rnd-geodata-php

This library is a migration of the rnd-geodata-php project with the following changes:

- **Namespace**: Changed from `GeoData\*` to `Hellworx\Geodata\*`
- **Dependencies**: Changed from `LosKoderos\Generic\*` to `Hellworx\Base\*`
- **Structure**: Maintained all existing functionality while updating to use Hellworx base components

## License

MIT License - see LICENSE file for details.
