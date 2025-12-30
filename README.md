# HWX Geodata PHP Library

Hellworx Geodata PHP Library - A library for working with geospatial data.

## Installation

```bash
composer require hellworx/hwx-geodata-php
```

## Requirements

- PHP 7.4 or higher
- hellworx/hwx-base-php library

## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use HWX\Geodata\YourClass;

$instance = new YourClass();
```

## Development

### Running Tests

```bash
make test
# or
./vendor/bin/phpunit
```

### Static Analysis

```bash
make lint
# or
./vendor/bin/phpstan analyse --memory-limit=200M
```

### Code Style

```bash
make cs-fix
# or
./vendor/bin/phpcbf src test
```

## License

MIT License - see [LICENSE](LICENSE) file.
