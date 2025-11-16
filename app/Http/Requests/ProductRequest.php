<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được ủy quyền để thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     */
    public function rules(): array
    {
        // Quy tắc đơn giản cho POST (tạo mới)
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|min:3|max:100',
                'description' => 'nullable|string|max:1000',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'image' => 'nullable|string|max:500', // URL string hoặc bỏ trống
                'is_active' => 'nullable|boolean',
            ];
        }

        // Quy tắc đơn giản cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name' => 'sometimes|required|string|min:3|max:100',
                'description' => 'sometimes|nullable|string|max:1000',
                'price' => 'sometimes|required|numeric|min:0',
                'quantity' => 'sometimes|required|integer|min:0',
                'category_id' => 'sometimes|required|integer',
                'brand_id' => 'sometimes|required|integer',
                'image' => 'sometimes|nullable|string|max:500',
                'is_active' => 'sometimes|nullable|boolean',
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
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.min' => 'Tên sản phẩm quá ngắn (tối thiểu 3 ký tự).',
            'name.max' => 'Tên sản phẩm quá dài (tối đa 100 ký tự).',
            'price.required' => 'Giá bán là bắt buộc.',
            'price.numeric' => 'Giá bán phải là số.',
            'price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
            'quantity.required' => 'Số lượng là bắt buộc.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.integer' => 'Danh mục phải là số nguyên.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'brand_id.integer' => 'Thương hiệu phải là số nguyên.',
            'image.image' => 'File phải là hình ảnh.',
            'image.max' => 'Hình ảnh không được vượt quá 2MB.',
        ];
    }
}
