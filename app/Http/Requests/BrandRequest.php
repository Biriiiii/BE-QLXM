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
        // 1. TỐI ƯU: Quy tắc chung cho file logo
        $logoRules = [
            'nullable', // Cho phép để trống (không up logo)
            'image',    // Phải là file ảnh
            'mimes:jpg,jpeg,png,webp,gif', // Chỉ định rõ loại file
            'max:2048', // Tối đa 2MB (2048 KB)
        ];

        // Quy tắc mặc định cho POST (tạo mới)
        if ($this->isMethod('post')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[a-zA-ZÀ-ỹ0-9\s\-\_]+$/',
                    Rule::unique('brands', 'name')
                ],
                'description' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:100',

                // 2. SỬA LỖI: Áp dụng quy tắc ảnh
                'logo' => $logoRules,
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $brandId = $this->route('brand'); // Tên tham số trên route

            return [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[a-zA-ZÀ-ỹ0-9\s\-\_]+$/',
                    Rule::unique('brands', 'name')->ignore($brandId)
                ],
                'description' => 'sometimes|nullable|string|max:255',
                'country' => 'sometimes|nullable|string|max:100',

                // 2. SỬA LỖI: Áp dụng quy tắc ảnh
                'logo' => $logoRules,
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

            // 3. THÊM: Thông báo lỗi cho quy tắc logo
            'logo.image' => 'File tải lên phải là hình ảnh.',
            'logo.mimes' => 'Chỉ chấp nhận ảnh định dạng: jpg, jpeg, png, webp, gif.',
            'logo.max' => 'Hình ảnh không được vượt quá 2MB.',
        ];
    }
}
