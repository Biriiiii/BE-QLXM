<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    protected $fillable = ['name', 'description', 'country', 'logo'];
    protected $appends = ['logo_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Accessor cho logo_url
    /**
     * Get the full S3 URL for the brand logo (IDE friendly).
     * @return string|null
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo && config('filesystems.disks.s3.url')) {
            $path = $this->logo;
            $baseUrl = config('filesystems.disks.s3.url');
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
        return null;
    }
}
