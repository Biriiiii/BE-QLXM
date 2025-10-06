<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'description', 'country', 'logo'];
    protected $appends = ['logo_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Accessor cho logo_url
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
