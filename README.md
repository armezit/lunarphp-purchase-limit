# Lunar Purchase Limit Addon

[![Latest Version on Packagist](https://img.shields.io/packagist/v/armezit/lunarphp-purchase-limit.svg?style=flat-square)](https://packagist.org/packages/armezit/lunarphp-purchase-limit)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/armezit/lunarphp-purchase-limit/run-tests?label=tests)](https://github.com/armezit/lunarphp-purchase-limit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/armezit/lunarphp-purchase-limit/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/armezit/lunarphp-purchase-limit/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/armezit/lunarphp-purchase-limit.svg?style=flat-square)](https://packagist.org/packages/armezit/lunarphp-purchase-limit)

The Purchase Limit addon for Lunar allows you to define complex purchase limitation scenarios in your 
[Lunar](https://github.com/lunarphp/lunar) store.

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

![Product Purchase Limit Slot](docs/assets/product-purchase-limit-screenshot.png?raw=true "Product Purchase Limit")

## Quick Setup

Install the Lunar Purchase Limit via composer:

```bash
composer require armezit/lunarphp-purchase-limit
``` 

Run the migrations with:

```bash
php artisan migrate
```

## Usage

By default, after quick setup, you can start to define purchase limits on products and customers. 
There is also a detailed [installation guide](#installation), in case you need more customization.

### Detect Violation of Defined Limitations

The package throws a child type of `Lunar\Exceptions\Carts\CartException` exception,
each time a violation of defined limits detected.

Typically, you want to catch these exceptions during create order process:

```php
use Lunar\Facades\CartSession;
use Lunar\Exceptions\Carts\CartException;
use Armezit\Lunar\PurchaseLimit\Exceptions\PurchaseLimitException;
use Armezit\Lunar\PurchaseLimit\Exceptions\CustomerQuantityLimitException;

try {
    $order = CartSession::current()
                ->getManager()
                ->createOrder();

// catch any CartException
} catch (CartException $e) {
    ...

// catch any kind of purchase limit violation
} catch (PurchaseLimitException $e) {
    ...

// catch specific kind of purchase limit violation
} catch (CustomerQuantityLimitException $e) {
    // customer quantity limit exceeded
}
```

### Product Purchase Limit (Slot)

The **Product Purchase Limit** slot will let you define limitations for a specific product.

In the following example, we have defined limitation rules for product `A`:

1. `Any` customer is allowed to purchase at most `10` units of the product in each `Week`.
2. Customers of `Retail` group are allowed to purchase at most `2000` total sum of the product in `Each Purchase`.
   So, for example, if price of product is $500, they can only purchase up to 4 unit of product `A` in each order.

![Product Purchase Limit Example 1](docs/assets/product-purchase-limit-example-01.png?raw=true "Product Purchase Limit Example 1")

BY default, you will find this slot in bottom of the product edit page. 
However, you can change this default position in config file.

### Customer Purchase Limit (Slot)

The **Customer Purchase Limit** slot will let you define limitations for a specific customer or customer group.

_TODO: Not Yet Implemented !_

## Advanced Usage

### Architecture

The primary component is `Rule` classes, which check defined limitations (stored in database) 
and throw exception if a violation of rule detected.

There are two kinds of rules which implement either `CartRuleInterface` or `CartLineRuleInterface` contracts.

Each rule perform only a unique type of checks. 

For example, `CustomerProductLimit` rule only checks purchase limits of a specific customer on a specific product.

While `CustomerLimit` rule only checks purchase limits of a specific customer on any product, and 
`ProductLimit` rules only checks purchase limits of any customer on a specific product; And so on.

These rules are injected into the Cart/CartLine modifiers pipelines. 

So, everytime you try to call `CreateOrder` action, these rules execute one by one 
and throw exception if detect violation of defined limitations.

### Complex Rules

Although there is Hub slots for defining typical limitations, nothing prevents you to compose more complex
rules by saving purchase limits directly in database. 

Take a look at the [PurchaseLimit](src/Models/PurchaseLimit.php) Model.

### Extend

In addition, you can extend/modify business logic by implementing `CartRuleInterface` and/or `CartLineRuleInterface`
contracts and inject them into the modifier pipelines.

After implementing a new `CheckVeryComplexLimit` rule, add it to the corresponding pipeline in config file:

```php
'cart_rules' => [
    ...
    CheckVeryComplexLimit::class,
],
```

or

```php
'cart_line_rules' => [
    ...
    CheckVeryComplexLimit::class,
],
```

## Installation

[Quick Setup](#quick-setup) covers the essential installation steps.
This section, however, is a detailed installation procedure, 
containing all optional parts. 

You can install the package via composer:

```bash
composer require armezit/lunarphp-purchase-limit
```

### Migrations

Optionally, publish the migrations:

```bash
php artisan vendor:publish --tag="lunarphp-purchase-limit-migrations"
```

Run the migrations with:

```bash
php artisan migrate
```

::: tip Table names are configurable. See the config file. :::

### Config

You can publish the config file with:

```bash
php artisan vendor:publish --tag="lunarphp-purchase-limit-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * List of rules to check during CartModifier::calculated() hook
     */
    'cart_rules' => [
        ProductLimit::class,
        CustomerLimit::class,
        CustomerProductLimit::class,
    ],

    /*
     * List of rules to check during CartLineModifier::calculated() hook
     */
    'cart_line_rules' => [
        ProductVariantLimit::class,
        CustomerProductVariantLimit::class,
    ],

    /*
     * Automatically register purchase-limit`s cart/cart-line modifiers during PurchaseLimitServiceProvider boot
     * Set it to false, if you want to manually register cart/cart-line modifiers
     */
    'register_cart_modifiers' => true,

    /*
     * Automatically register purchase-limit admin hub slots
     * Set it to false, if you want to manually register them
     */
    'register_hub_slots' => true,

    /*
     * The name (handle) of hub slot which you want to display product purchase limit component
     */
    'product_purchase_limit_slot' => 'product.all',

    'database' => [
        'purchase_limits_table' => 'purchase_limits',
    ],
];
```

### Translations & Views

Optionally, you can publish the translations and views using

```bash
php artisan vendor:publish --tag="lunarphp-purchase-limit-translations"
php artisan vendor:publish --tag="lunarphp-purchase-limit-views"
```


### Service provider

By default, this package automatically register it\`s service providers when it is installed.

If for any reason you prefer to register them manually, you should add the package service providers 
into your laravel application's `config/app.php` file.

```php
// ...
'providers' => [
    // ...
    Armezit\Lunar\PurchaseLimit\PurchaseLimitServiceProvider::class,
    Armezit\Lunar\PurchaseLimit\PurchaseLimitHubServiceProvider::class,
],
```

The `PurchaseLimitServiceProvider` bootstrap primary package features, 
while the `PurchaseLimitHubServiceProvider` is used to register some 
[Slots](https://docs.lunarphp.io/extending/admin-hub.html#slots) to be used in Lunar Admin Hub.

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
