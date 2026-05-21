# Kafka Bus for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/micromus/kafka-bus-laravel.svg?style=flat-square)](https://packagist.org/packages/micromus/kafka-bus-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/micromus/kafka-bus-laravel/run-tests.yml?branch=2.x&label=tests&style=flat-square)](https://github.com/micromus/kafka-bus-laravel/actions?query=workflow%3Arun-tests+branch%3A2.x)
[![GitHub Code Style](https://img.shields.io/github/actions/workflow/status/micromus/kafka-bus-laravel/php-code-style.yml?branch=2.x&label=code-style&style=flat-square)](https://github.com/micromus/kafka-bus-laravel/actions?query=workflow%3Acode-style+branch%3A2.x)
[![GitHub PHPStan](https://img.shields.io/github/actions/workflow/status/micromus/kafka-bus-laravel/phpstan.yml?branch=2.x&label=phpstan&style=flat-square)](https://github.com/micromus/kafka-bus-laravel/actions?query=workflow%3Aphpstan+branch%3A2.x)
[![Total Downloads](https://img.shields.io/packagist/dt/micromus/kafka-bus-laravel.svg?style=flat-square)](https://packagist.org/packages/micromus/kafka-bus-laravel)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require micromus/kafka-bus-laravel
```

## Usage

```php

use Micromus\KafkaBus\Interfaces\Bus\BusInterface;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;

public function execute(BusInterface $bus): void
{
    $bus->publish(new ProducerMessageFaker());
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kirill Popkov](https://github.com/popkovkirill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
