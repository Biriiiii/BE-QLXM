<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được ủy quyền để thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        return true; // Giả sử middleware đã xác thực
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     */
    public function rules(): array
    {
        // Quy tắc mặc định cho POST (tạo mới)
        if ($this->isMethod('post')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[a-zA-ZÀ-ỹ0-9\s\-\_]+$/', // Chỉ cho phép chữ, số, khoảng trắng, dấu gạch
                    Rule::unique('brands', 'name') // Tên thương hiệu không được trùng
                ],
                'description' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:100',
                'logo' => 'nullable|string|max:500', // URL logo
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Lấy ID brand từ route (ví dụ: /api/brands/123)
            $brandId = $this->route('brand'); // Tên tham số trên route

            return [
                'name' => [
                    'sometimes', // Chỉ validate nếu trường 'name' được gửi lên
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[a-zA-ZÀ-ỹ0-9\s\-\_]+$/', // Chỉ cho phép chữ, số, khoảng trắng, dấu gạch
                    // Khi update, kiểm tra unique nhưng bỏ qua chính nó
                    Rule::unique('brands', 'name')->ignore($brandId)
                ],
                'description' => 'sometimes|nullable|string|max:255',
                'country' => 'sometimes|nullable|string|max:100',
                'logo' => 'sometimes|nullable|string|max:500',
            ];
        }

        return [];
    }

    /**
     * Tùy chỉnh thông báo lỗi
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên thương hiệu là bắt buộc.',
            'name.unique' => 'Tên thương hiệu này đã tồn tại.',
            'name.min' => 'Tên thương hiệu quá ngắn (tối thiểu 2 ký tự).',
            'name.max' => 'Tên thương hiệu quá dài (tối đa 50 ký tự).',
            'name.regex' => 'Tên thương hiệu chỉ được chứa chữ cái, số, khoảng trắng và dấu gạch.',
            'description.max' => 'Mô tả quá dài (tối đa 255 ký tự).',
            'country.max' => 'Tên quốc gia quá dài (tối đa 100 ký tự).',
            'logo.max' => 'URL logo quá dài (tối đa 500 ký tự).',
        ];
    }
}
