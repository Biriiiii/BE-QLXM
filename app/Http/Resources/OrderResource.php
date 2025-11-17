<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_code' => 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
                'address' => $this->customer->address,
            ],
            'order_date' => $this->order_date?->format('d/m/Y H:i'),
            'order_date_iso' => $this->order_date,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'total_amount' => (float) $this->total_amount,
            'total_amount_formatted' => number_format($this->total_amount, 0, ',', '.') . 'đ',
            'deposit_amount' => (float) ($this->deposit_amount ?? 0),
            'installment_term' => $this->installment_term,
            'installment_amount' => (float) ($this->installment_amount ?? 0),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }

    protected function getStatusText()
    {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status);
    }
}
