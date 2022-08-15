# GetCandy Purchase Limit Addon

The Purchase Limit addon for GetCandy allows you to set up purchase limitations in your 
[GetCandy](https://github.com/getcandy/getcandy) store.

This package injects Rule classes into the Cart/CartLine modifiers pipeline to check 
various purchase limit rules against the current cart.

You can even write your own rule classes to extend it\`s functionality.

- [Features](#features)
- [Setup](#setup)
    + [Composer Require Package](#composer-require-package)
    + [Service provider](#service-provider)
    + [Run Migration](#run-migration)
    + [Publish resources](#publish-resources)
- [Usage](#usage)
- [License](#license)

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

## Setup

### Composer Require Package

```shell
composer require armezit/getcandy-purchase-limit
```

### Service provider

Add service provider to your project config file `app.php`.

```php
// ...
'providers' => [
    // ...
    Armezit\GetCandy\PurchaseLimit\PurchaseLimitServiceProvider::class,
],
```

### Run Migration

```shell
php artisan migrate
```

This would create the `purchase_limits` table.

### Publish resources

#### config

```shell
php artisan vendor:publish --tag=getcandy:purchase-limit:config
```

#### language files

```shell
php artisan vendor:publish --tag=getcandy:purchase-limit:lang
```

## Usage

TBD.

## License

This package is open-sourced software licensed under the 
[MIT license](LICENSE.md).
