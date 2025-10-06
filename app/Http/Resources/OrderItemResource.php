<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product->name ?? null,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => (float) $this->price * $this->quantity
        ];
    }
}
