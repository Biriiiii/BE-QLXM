<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Cho phép truyền customer_id hoặc thông tin khách hàng mới
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:100',
            'customer_phone' => [
                'required_without:customer_id',
                'regex:/^0[0-9]{9}$/', // Đúng định dạng số điện thoại Việt Nam 10 số
                'max:10'
            ],
            'customer_email' => 'required|email|max:100',
            'customer_address' => 'required|string|max:200',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'installment_term' => 'nullable|string',
            'installment_amount' => 'nullable|numeric|min:0',
        ];
    }
}
