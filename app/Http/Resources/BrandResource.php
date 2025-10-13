<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandResource extends JsonResource
{
    /**
     * Chuyển đổi tài nguyên thành mảng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            // Logo là đường dẫn lưu trữ nội bộ (internal path)
            'logo' => $this->logo,
            // logo_url là đường dẫn công khai (public URL) từ S3
            'logo_url' => $this->logo
                ? Storage::disk('s3')->url($this->logo)
                : null,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
