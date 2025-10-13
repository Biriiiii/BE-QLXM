<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được ủy quyền để thực hiện yêu cầu này không.
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Lưu ý: Đối với ảnh, không cần mimes vì Laravel tự động xác định, nhưng nên giữ nếu cần giới hạn rõ ràng.
            'image' => 'nullable|image|max:2048',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            // Sử dụng Rule::in cho các giá trị cố định
            'status' => ['required', Rule::in(['available', 'unavailable'])],
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id'
        ];

        // LOGIC QUAN TRỌNG: Điều chỉnh quy tắc cho phương thức CẬP NHẬT (PUT/PATCH)
        // Khi cập nhật, các trường không bắt buộc phải được gửi đi (trừ trường quan trọng)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = 'nullable|string|max:255';
            $rules['price'] = 'nullable|numeric|min:0';
            $rules['stock'] = 'nullable|integer|min:0';
            $rules['status'] = ['nullable', Rule::in(['available', 'unavailable'])];
            $rules['brand_id'] = 'nullable|exists:brands,id';
            $rules['category_id'] = 'nullable|exists:categories,id';
        }

        return $rules;
    }
}
