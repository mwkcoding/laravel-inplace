# :package_description

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_name/:package_name.svg?style=flat-square)](https://packagist.org/packages/:vendor_name/:package_name)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/:vendor_name/:package_name/run-tests?label=tests)](https://github.com/:vendor_name/:package_name/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/:vendor_name/:package_name/Check%20&%20fix%20styling?label=code%20style)](https://github.com/:vendor_name/:package_name/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/:vendor_name/:package_name.svg?style=flat-square)](https://packagist.org/packages/:vendor_name/:package_name)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require :vendor_name/:package_name
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Spatie\Skeleton\SkeletonServiceProvider" --tag=":package_name-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\Skeleton\SkeletonServiceProvider" --tag=":package_name-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$skeleton = new Spatie\Skeleton();
echo $skeleton->echoPhrase('Hello, Spatie!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
