<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user') ?? $this->route('id');
        return [
            'name'  => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'role'  => 'sometimes|required|in:admin,staff',
            // 'password' => 'nullable|string|min:6|confirmed', // nếu muốn cho update ở đây
        ];
    }
}
