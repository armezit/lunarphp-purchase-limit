<?php

namespace Armezit\Lunar\PurchaseLimit\Models;

use Armezit\Lunar\PurchaseLimit\Database\Factories\PurchaseLimitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class PurchaseLimit extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'customer_group_id',
        'customer_id',
        'period',
        'max_quantity',
        'max_total',
        'starts_at',
        'ends_at',
    ];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('lunarphp-purchase-limit.database.purchase_limits_table', 'purchase_limits');
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): PurchaseLimitFactory
    {
        return PurchaseLimitFactory::new();
    }

    /**
     * Get the product that owns the item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the product variant that owns the item.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the customer that owns the item.
     */
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Get the customer that owns the item.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
