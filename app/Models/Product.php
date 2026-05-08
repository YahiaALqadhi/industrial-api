<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'price',
        'discount_price',
        'stock',
        'brand',
        'main_image',
        'short_description',
        'description',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function ($product) {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function images()
{
    return $this->hasMany(ProductImage::class);
}
public function cartItems()
{
    return $this->hasMany(CartItem::class);
}

public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}
}