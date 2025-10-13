<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
     * * Thích ứng quy tắc cho POST (tạo mới) và PUT/PATCH (cập nhật).
     */
    public function rules(): array
    {
        // Kiểm tra nếu là phương thức cập nhật (PUT hoặc PATCH)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                // Cho phép 'name' và 'description' là nullable khi cập nhật, 
                // vì người dùng có thể chỉ muốn sửa một trường.
                'name' => 'nullable|string|max:100',
                'description' => 'nullable|string'
            ];
        }

        // Quy tắc mặc định cho POST (tạo mới)
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string'
        ];
    }
}
