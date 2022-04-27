# GetCandy Purchase Limit Addon

The Purchase Limit addon for GetCandy allows you to set up  
purchase limitations in your 
[GetCandy](https://github.com/getcandy/getcandy) store.

This package uses GetCandy Cart/CartLine modifiers pipeline to check
various purchase limit rules against the current cart.

[[toc]]

## Features

You can define a criteria for each rule. 
Currently, these are possible:

* by a specific product
* by a specific product variant
* by a customer or customer group
* by a specific product and customer / customer group
* by a specific product variant and customer / customer group

Each purchase limit rule allows you to restrict quantity, price, or 
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

### (Optional) Publish resources

#### config

```shell
php artisan vendor:publish --tag=getcandy:virtual-inventory:config
```

#### language files

```shell
php artisan vendor:publish --tag=getcandy:virtual-inventory:lang
```

## Usage


## License

This package is open-sourced software licensed under the 
[MIT license](LICENSE.md).
