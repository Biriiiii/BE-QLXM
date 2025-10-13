<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được ủy quyền để thực hiện yêu cầu này không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Trả về true vì quyền truy cập được xử lý bởi middleware 'role:admin,staff'
        return true;
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Quy tắc chuẩn cho file ảnh
        ];

        // Điều chỉnh quy tắc cho phương thức PUT/PATCH (được gửi qua _method=PUT)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Khi CẬP NHẬT: name không bắt buộc, nhưng nếu có logo thì phải là ảnh
            $rules['name'] = 'nullable|string|max:100';
            $rules['logo'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        return $rules;
    }
}
