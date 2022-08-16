<?php

use Armezit\GetCandy\PurchaseLimit\Rules\CustomerLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CustomerProductLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CustomerProductVariantLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\ProductLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\ProductVariantLimit;

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

    /*
     * Config related to database
     */
    'database' => [
        'purchase_limits_table' => 'purchase_limits',
    ],
];
