<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'status' => $this->status,
            'image' => $this->image,
            'stock' => $this->stock,
            'description' => $this->description,
            // Trả về link public S3 nếu có ảnh
            'image_url' => $this->image ? Storage::disk('s3')->url($this->image) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'brand' => $this->brand,
            'category' => $this->category,
        ];
    }
}
