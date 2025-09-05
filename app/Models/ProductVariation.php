<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'display_name',
        'attribute_values',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'attribute_values' => 'array',
    ];

    /**
     * Get the product that owns the variation.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Generate SKU when creating if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variation) {
            if (empty($variation->sku)) {
                $baseProduct = Product::find($variation->product_id);
                $baseSku = $baseProduct ? $baseProduct->sku : 'PRD';
                $variation->sku = $baseSku . '-' . strtoupper(Str::random(4));
            }
        });
    }

    /**
     * Scope for active variations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the effective price (sale price if available, otherwise regular price)
     */
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if variation is on sale
     */
    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    /**
     * Get formatted variation display name
     */
    public function getFormattedDisplayNameAttribute()
    {
        return $this->display_name;
    }
}
