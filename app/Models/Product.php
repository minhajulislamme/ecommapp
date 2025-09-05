<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'image',
        'images',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the sub-category that owns the product.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Get the variations for the product.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Get active variations for the product.
     */
    public function activeVariations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->where('is_active', true);
    }

    /**
     * Generate slug from name when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the discounted price if sale price exists
     */
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if product is on sale
     */
    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    /**
     * Get gallery images array
     */
    public function getGalleryImagesAttribute()
    {
        return $this->images ?? [];
    }

    /**
     * Add image to gallery
     */
    public function addGalleryImage(string $imageName): void
    {
        $images = $this->images ?? [];
        $images[] = $imageName;
        $this->update(['images' => $images]);
    }

    /**
     * Remove image from gallery
     */
    public function removeGalleryImage(string $imageName): void
    {
        $images = $this->images ?? [];
        $images = array_values(array_filter($images, fn($image) => $image !== $imageName));
        $this->update(['images' => $images]);
    }

    /**
     * Update entire gallery
     */
    public function updateGallery(array $images): void
    {
        $this->update(['images' => $images]);
    }

    /**
     * Get all product images (main + gallery)
     */
    public function getAllImagesAttribute()
    {
        $allImages = [];

        if ($this->image) {
            $allImages[] = $this->image;
        }

        if ($this->images) {
            $allImages = array_merge($allImages, $this->images);
        }

        return array_unique($allImages);
    }

    /**
     * Check if product has variations
     */
    public function hasVariations(): bool
    {
        return $this->variations()->exists();
    }

    /**
     * Get the cheapest variation price or base price
     */
    public function getMinPriceAttribute()
    {
        if ($this->hasVariations()) {
            $minVariationPrice = $this->activeVariations()->min('price');
            return $minVariationPrice ?? $this->price;
        }
        return $this->price;
    }

    /**
     * Get the most expensive variation price or base price
     */
    public function getMaxPriceAttribute()
    {
        if ($this->hasVariations()) {
            $maxVariationPrice = $this->activeVariations()->max('price');
            return $maxVariationPrice ?? $this->price;
        }
        return $this->price;
    }

    /**
     * Get price range for display
     */
    public function getPriceRangeAttribute()
    {
        if ($this->hasVariations()) {
            $minPrice = $this->min_price;
            $maxPrice = $this->max_price;

            if ($minPrice == $maxPrice) {
                return '$' . number_format($minPrice, 2);
            }

            return '$' . number_format($minPrice, 2) . ' - $' . number_format($maxPrice, 2);
        }

        return '$' . number_format($this->effective_price, 2);
    }

    /**
     * Get total stock including variations
     */
    public function getTotalStockAttribute()
    {
        if ($this->hasVariations()) {
            return $this->activeVariations()->sum('stock_quantity');
        }
        return $this->stock_quantity;
    }
}
