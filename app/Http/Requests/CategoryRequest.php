<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được ủy quyền để thực hiện yêu cầu này không.
     * (Trong 1 API, việc này thường được xử lý bởi middleware như Sanctum/Passport)
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
                    'max:100',
                    Rule::unique('categories', 'name') // Tên danh mục không được trùng
                ],
                'description' => 'nullable|string'
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Lấy ID category từ route (ví dụ: /api/categories/123)
            $categoryId = $this->route('category'); // Tên tham số trên route

            return [
                'name' => [
                    'sometimes', // Chỉ validate nếu trường 'name' được gửi lên
                    'required',
                    'string',
                    'max:30',
                    // Khi update, kiểm tra unique nhưng bỏ qua chính nó
                    Rule::unique('categories', 'name')->ignore($categoryId)
                ],
                'description' => 'sometimes|nullable|string'
            ];
        }

        return [];
    }

    /**
     * Tùy chỉnh thông báo lỗi (ví dụ)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
            'name.max' => 'Tên danh mục quá dài (tối đa 30 ký tự).',
        ];
    }
}
