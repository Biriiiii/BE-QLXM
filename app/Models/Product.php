<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'stock',
        'status',
        'brand_id',
        'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
    protected $appends = ['image_url'];
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor cho image_url
    /**
     * Get the full S3 URL for the product image (IDE friendly).
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if ($this->image && config('filesystems.disks.s3.url')) {
            $path = $this->image;
            $baseUrl = config('filesystems.disks.s3.url');
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
        return null;
    }
}
