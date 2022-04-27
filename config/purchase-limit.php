<?php

use Armezit\GetCandy\PurchaseLimit\Rules\CustomerLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CustomerProductLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\CustomerProductVariantLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\ProductLimit;
use Armezit\GetCandy\PurchaseLimit\Rules\ProductVariantLimit;

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
];
