# GetCandy Purchase Limit Addon

[![Latest Version on Packagist](https://img.shields.io/packagist/v/armezit/getcandy-purchase-limit.svg?style=flat-square)](https://packagist.org/packages/armezit/getcandy-purchase-limit)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/armezit/getcandy-purchase-limit/run-tests?label=tests)](https://github.com/armezit/getcandy-purchase-limit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/armezit/getcandy-purchase-limit/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/armezit/getcandy-purchase-limit/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/armezit/getcandy-purchase-limit.svg?style=flat-square)](https://packagist.org/packages/armezit/getcandy-purchase-limit)

The Purchase Limit addon for GetCandy allows you to set up purchase limitations in your 
[GetCandy](https://github.com/getcandy/getcandy) store.

This package injects Rule classes into the Cart/CartLine modifiers pipeline to check 
various purchase limit rules against the current cart. You can even write your own rule 
classes to extend it\`s functionality.

<details open><summary><h3>Table of Contents</h3></summary>
<p>

- [Features](#features)
- [Installation](#installation)
    + [Migrations](#migrations)
    + [Config](#config)
    + [Translations & Views](#translations-&-views)
    + [Service provider](#service-provider)
- [Usage](#usage)
- [Testing](#testing)
- [Changelog](#changelog)
- [License](#license)

</p>
</details>

## Features

You can define a criteria for each rule. 
Currently, you are able to define purchase limits for:

* a specific Product
* a specific Product Variant
* a Customer or Customer Group
* a specific Product for a specific Customer or Customer Group
* a specific Product Variant for a specific Customer or Customer Group

Each purchase limit rule allows you to restrict either quantity, amount (total sum), or 
both of them.

## Installation

You can install the package via composer:

```bash
composer require armezit/getcandy-purchase-limit
```

### Migrations

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="getcandy-purchase-limit-migrations"
php artisan migrate
```

::: tip By default this would create the `purchase_limits` table. You can change that in config file. :::

### Config

You can publish the config file with:

```bash
php artisan vendor:publish --tag="getcandy-purchase-limit-config"
```

This is the contents of the published config file:

```php
return [
    'cart_rules' => [
        ProductLimit::class,
        CustomerLimit::class,
        CustomerProductLimit::class,
    ],
    'cart_line_rules' => [
        ProductVariantLimit::class,
        CustomerProductVariantLimit::class,
    ],
    'database' => [
        'purchase_limits_table' => 'purchase_limits',
    ],
];
```

### Translations & Views

Optionally, you can publish the translations and views using

```bash
php artisan vendor:publish --tag="getcandy-purchase-limit-translations"
php artisan vendor:publish --tag="getcandy-purchase-limit-views"
```


### Service provider

By default, this package automatically register it\`s service providers when it is installed.

If for any reason you prefer to register them manually, you should add the package service providers 
into your laravel application's `config/app.php` file.

```php
// ...
'providers' => [
    // ...
    Armezit\GetCandy\PurchaseLimit\PurchaseLimitServiceProvider::class,
    Armezit\GetCandy\PurchaseLimit\PurchaseLimitHubServiceProvider::class,
],
```

The `PurchaseLimitServiceProvider` bootstrap primary package features, 
while the `PurchaseLimitHubServiceProvider` is used to register some 
[Slots](https://docs.getcandy.io/extending/admin-hub.html#slots) to be used in GetCandy Admin Hub.

## Usage

TBD.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/armezit/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Armin Rezayati](https://github.com/armezit)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
