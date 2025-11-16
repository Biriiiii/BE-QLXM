<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource; // 👈 1. Import Resource

class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        // 👈 2. Sử dụng UserResource để trả về
        return response()->json([
            'user' => new UserResource($user), // Chỉ trả về các trường đã định nghĩa
            'token' => $token
        ]);
    }

    public function me(Request $request)
    {
        // 👈 3. Sử dụng UserResource ở đây để nhất quán
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Đã đăng xuất']);
    }
}
