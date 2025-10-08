<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            // asset() sẽ trả URL public → không cần token
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'brand' => $this->brand,
            'category' => $this->category,
        ];
    }
}
