<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'=>$this->id,
            'customer'=>[
                'id'=>$this->customer->id,
                'name'=>$this->customer->name,
            ],
            'order_date'=>$this->order_date,
            'status'=>$this->status,
            'total_amount'=>$this->total_amount,
            'items'=> OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'=>$this->created_at
        ];
    }
}