# API Builder for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/api-builder.svg?style=flat-square)](https://packagist.org/packages/spatie/api-builder)
[![Build Status](https://img.shields.io/travis/spatie/api-builder/master.svg?style=flat-square)](https://travis-ci.org/spatie/api-builder)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/api-builder.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/api-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/api-builder.svg?style=flat-square)](https://packagist.org/packages/spatie/api-builder)

API builder for Laravel

## Installation

You can install the package via composer:

```bash
composer require coreproc/api-builder
```

## Usage

``` php
$skeleton = new CoreProc\ApiBuilder();
echo $skeleton->echoPhrase('Hello, CoreProc!');
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## About CoreProc

CoreProc is a software development company that provides software development services to startups, digital/ad agencies, and enterprises.

Learn more about us on our [website](https://coreproc.com).

## Credits

- [Chris Bautista](https://github.com/chrisbjr)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
