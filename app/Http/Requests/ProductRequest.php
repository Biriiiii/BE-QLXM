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
        return true; // Giả sử ai cũng có quyền (đã qua auth:sanctum)
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     */
    public function rules(): array
    {
        // 1. TỐI ƯU: Quy tắc chung cho file ảnh
        $imageRules = [
            'nullable', // Cho phép để trống (không up ảnh)
            'image',    // Phải là file ảnh (jpg, png, bmp, gif, svg, webp)
            'mimes:jpg,jpeg,png,webp,gif', // Chỉ định rõ loại file
            'max:2048', // Tối đa 2MB (2048 KB)
        ];

        // Quy tắc cho POST (tạo mới)
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|min:3|max:100',
                'description' => 'nullable|string|max:1000',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0', // Sửa: Tên cũ là 'stock'
                'category_id' => 'required|integer|exists:categories,id', // Thêm: exists
                'brand_id' => 'required|integer|exists:brands,id', // Thêm: exists

                // 2. SỬA LỖI: Áp dụng quy tắc ảnh
                'image' => $imageRules,

                'status' => 'nullable|string|in:available,unavailable', // Sửa: Tên cũ là 'is_active'
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name' => 'sometimes|required|string|min:3|max:100',
                'description' => 'sometimes|nullable|string|max:1000',
                'price' => 'sometimes|required|numeric|min:0',
                'quantity' => 'sometimes|required|integer|min:0',
                'category_id' => 'sometimes|required|integer|exists:categories,id',
                'brand_id' => 'sometimes|required|integer|exists:brands,id',

                // 2. SỬA LỖI: Áp dụng quy tắc ảnh
                'image' => $imageRules,

                'status' => 'sometimes|nullable|string|in:available,unavailable',
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
            'price.required' => 'Giá bán là bắt buộc.',
            'quantity.required' => 'Số lượng là bắt buộc.',
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',

            // 3. THÊM: Thông báo lỗi cho quy tắc ảnh
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.mimes' => 'Chỉ chấp nhận ảnh định dạng: jpg, jpeg, png, webp, gif.',
            'image.max' => 'Hình ảnh không được vượt quá 2MB.',
        ];
    }
}
