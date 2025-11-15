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
        // Quy tắc mặc định cho POST (tạo mới)
        if ($this->isMethod('post')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                    'regex:/^[a-zA-ZÀ-ỹ0-9\s\-\_\.]+$/', // Chỉ cho phép chữ, số, khoảng trắng, dấu gạch, dấu chấm
                    Rule::unique('products', 'name') // Tên sản phẩm không được trùng
                ],
                'description' => 'nullable|string|max:1000',
                'price' => 'required|numeric|min:0|max:999999999.99',
                'cost_price' => 'nullable|numeric|min:0|max:999999999.99',
                'quantity' => 'required|integer|min:0',
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('categories', 'id') // Kiểm tra category có tồn tại
                ],
                'brand_id' => [
                    'required',
                    'integer',
                    Rule::exists('brands', 'id') // Kiểm tra brand có tồn tại
                ],
                'image' => 'nullable|string|max:500', // URL hình ảnh
                'sku' => [
                    'nullable',
                    'string',
                    'max:50',
                    'alpha_num',
                    Rule::unique('products', 'sku') // SKU không được trùng
                ],
                'is_active' => 'nullable|boolean',
                'weight' => 'nullable|numeric|min:0|max:99999.99',
                'dimensions' => 'nullable|string|max:100',
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Lấy ID product từ route (ví dụ: /api/products/123)
            $productId = $this->route('product'); // Tên tham số trên route

            return [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                    'regex:/^[a-zA-ZÀ-ỹ0-9\s\-\_\.]+$/',
                    Rule::unique('products', 'name')->ignore($productId)
                ],
                'description' => 'sometimes|nullable|string|max:1000',
                'price' => 'sometimes|required|numeric|min:0|max:999999999.99',
                'cost_price' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
                'quantity' => 'sometimes|required|integer|min:0',
                'category_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    Rule::exists('categories', 'id')
                ],
                'brand_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    Rule::exists('brands', 'id')
                ],
                'image' => 'sometimes|nullable|string|max:500',
                'sku' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:50',
                    'alpha_num',
                    Rule::unique('products', 'sku')->ignore($productId)
                ],
                'is_active' => 'sometimes|nullable|boolean',
                'weight' => 'sometimes|nullable|numeric|min:0|max:99999.99',
                'dimensions' => 'sometimes|nullable|string|max:100',
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
            'name.unique' => 'Tên sản phẩm này đã tồn tại.',
            'name.min' => 'Tên sản phẩm quá ngắn (tối thiểu 3 ký tự).',
            'name.max' => 'Tên sản phẩm quá dài (tối đa 100 ký tự).',
            'name.regex' => 'Tên sản phẩm chỉ được chứa chữ cái, số, khoảng trắng và các ký tự đặc biệt cơ bản.',
            'description.max' => 'Mô tả quá dài (tối đa 1000 ký tự).',
            'price.required' => 'Giá bán là bắt buộc.',
            'price.numeric' => 'Giá bán phải là số.',
            'price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
            'price.max' => 'Giá bán quá lớn.',
            'cost_price.numeric' => 'Giá nhập phải là số.',
            'cost_price.min' => 'Giá nhập phải lớn hơn hoặc bằng 0.',
            'quantity.required' => 'Số lượng là bắt buộc.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
            'image.max' => 'URL hình ảnh quá dài (tối đa 500 ký tự).',
            'sku.unique' => 'Mã SKU này đã tồn tại.',
            'sku.alpha_num' => 'Mã SKU chỉ được chứa chữ cái và số.',
            'sku.max' => 'Mã SKU quá dài (tối đa 50 ký tự).',
            'weight.numeric' => 'Trọng lượng phải là số.',
            'weight.min' => 'Trọng lượng phải lớn hơn hoặc bằng 0.',
            'dimensions.max' => 'Kích thước quá dài (tối đa 100 ký tự).',
        ];
    }
}
