<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Có thể route('customer') (cấu trúc cũ) hoặc route('id') (cấu trúc mới)
        $id = $this->route('customer') ?? $this->route('id');
        return [
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $id,
            'email' => 'required|email|max:150|unique:customers,email,' . $id,
            'address' => 'nullable|string'
        ];
    }
}
