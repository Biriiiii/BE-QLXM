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
        // Mặc định là quy tắc cho POST (store)
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 2MB
        ];

        // Kiểm tra nếu request là PUT/PATCH (update)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Khi CẬP NHẬT, 'name' và 'logo' không còn bắt buộc (required) nữa 
            // vì người dùng có thể chỉ cập nhật description hoặc country.
            // Nếu gửi 'logo' thì vẫn phải là 'image'.
            $rules['name'] = 'nullable|string|max:100';
            $rules['logo'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        return $rules;
    }
}
