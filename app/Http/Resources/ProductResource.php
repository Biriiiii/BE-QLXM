<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Chuyển đổi tài nguyên sản phẩm thành mảng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'status' => $this->status,
            'quantity' => $this->stock, // Map stock -> quantity cho API response
            // Đường dẫn lưu trữ nội bộ (internal path)
            'image' => $this->image,
            'stock' => $this->stock,
            'description' => $this->description,
            // Trả về link public S3 nếu có ảnh
            'image_url' => $this->image
                ? Storage::disk('s3')->url($this->image)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Eager Loaded Relationships
            'brand' => $this->whenLoaded('brand', function () {
                // Sử dụng BrandResource nếu bạn muốn format nhất quán
                // return new BrandResource($this->brand); 
                return $this->brand;
            }),
            'category' => $this->whenLoaded('category', function () {
                // Sử dụng CategoryResource nếu bạn muốn format nhất quán
                // return new CategoryResource($this->category); 
                return $this->category;
            }),
        ];
    }
}
