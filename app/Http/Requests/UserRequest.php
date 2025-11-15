<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
                    'regex:/^[a-zA-ZÀ-ỹ\s]+$/', // Chỉ cho phép chữ cái và khoảng trắng
                ],
                'email' => [
                    'required',
                    'string',
                    'email:rfc,dns',
                    'max:100',
                    Rule::unique('users', 'email') // Email không được trùng
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:50',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', // Ít nhất 1 chữ thường, 1 chữ hoa, 1 số
                ],
                'password_confirmation' => 'required|same:password',
                'role' => [
                    'required',
                    'string',
                    Rule::in(['admin', 'staff', 'user']) // Chỉ cho phép các role này
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'regex:/^(0[3-9])[0-9]{8}$/', // Số điện thoại VN
                    Rule::unique('users', 'phone')
                ],
                'address' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ];
        }

        // Quy tắc cho PUT/PATCH (cập nhật)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Lấy ID user từ route (ví dụ: /api/users/123)
            $userId = $this->route('user'); // Tên tham số trên route

            return [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[a-zA-ZÀ-ỹ\s]+$/',
                ],
                'email' => [
                    'sometimes',
                    'required',
                    'string',
                    'email:rfc,dns',
                    'max:100',
                    Rule::unique('users', 'email')->ignore($userId)
                ],
                'password' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:8',
                    'max:50',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                ],
                'password_confirmation' => 'required_with:password|same:password',
                'role' => [
                    'sometimes',
                    'required',
                    'string',
                    Rule::in(['admin', 'staff', 'user'])
                ],
                'phone' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'regex:/^(0[3-9])[0-9]{8}$/',
                    Rule::unique('users', 'phone')->ignore($userId)
                ],
                'address' => 'sometimes|nullable|string|max:255',
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
            'name.required' => 'Tên người dùng là bắt buộc.',
            'name.min' => 'Tên người dùng quá ngắn (tối thiểu 2 ký tự).',
            'name.max' => 'Tên người dùng quá dài (tối đa 50 ký tự).',
            'name.regex' => 'Tên người dùng chỉ được chứa chữ cái và khoảng trắng.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'email.max' => 'Email quá dài (tối đa 100 ký tự).',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu quá ngắn (tối thiểu 8 ký tự).',
            'password.max' => 'Mật khẩu quá dài (tối đa 50 ký tự).',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ thường, 1 chữ hoa và 1 số.',
            'password_confirmation.required' => 'Xác nhận mật khẩu là bắt buộc.',
            'password_confirmation.same' => 'Xác nhận mật khẩu không khớp.',
            'password_confirmation.required_with' => 'Vui lòng xác nhận mật khẩu mới.',
            'role.required' => 'Vai trò là bắt buộc.',
            'role.in' => 'Vai trò không hợp lệ. Chỉ được chọn: admin, staff, user.',
            'phone.regex' => 'Số điện thoại không hợp lệ (định dạng: 0xxxxxxxxx).',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'address.max' => 'Địa chỉ quá dài (tối đa 255 ký tự).',
        ];
    }
}
