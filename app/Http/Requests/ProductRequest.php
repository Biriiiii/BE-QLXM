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
                    'min:2',
                    'max:255',
                    Rule::unique('products', 'name') // Tên sản phẩm không được trùng
                ],
                'description' => 'nullable|string|max:2000',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'status' => 'nullable|in:active,inactive,draft',
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('categories', 'id')
                ],
                'brand_id' => [
                    'required',
                    'integer',
                    Rule::exists('brands', 'id')
                ],
                'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', // File upload
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $productId = $this->route('product') ?? $this->route('id'); // Lấy ID từ route parameter

            return [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                    Rule::unique('products', 'name')->ignore($productId)
                ],
                'description' => 'sometimes|nullable|string|max:2000',
                'price' => 'sometimes|required|numeric|min:0',
                'stock' => 'sometimes|required|integer|min:0',
                'status' => 'sometimes|nullable|in:active,inactive,draft',
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
                'image' => 'sometimes|nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
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
            'name.min' => 'Tên sản phẩm quá ngắn (tối thiểu 2 ký tự).',
            'name.max' => 'Tên sản phẩm quá dài (tối đa 255 ký tự).',
            'description.max' => 'Mô tả quá dài (tối đa 2000 ký tự).',
            'price.required' => 'Giá bán là bắt buộc.',
            'price.numeric' => 'Giá bán phải là số.',
            'price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
            'stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'stock.min' => 'Số lượng tồn kho phải lớn hơn hoặc bằng 0.',
            'status.in' => 'Trạng thái phải là: active, inactive hoặc draft.',
            'category_id.required' => 'Danh mục sản phẩm là bắt buộc.',
            'category_id.exists' => 'Danh mục được chọn không tồn tại.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'brand_id.exists' => 'Thương hiệu được chọn không tồn tại.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, jpg, png, webp.',
            'image.max' => 'Kích thước hình ảnh tối đa 2MB.',
        ];
    }
}
