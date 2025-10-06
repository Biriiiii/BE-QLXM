<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query();

        if ($request->filled('role')) {
            $q->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $users = $q->latest()->paginate(20);
        return UserResource::collection($users);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return new UserResource($user);
    }

    public function changePassword(ChangePasswordRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $auth = $request->user();

        if ($auth->id !== $user->id && $auth->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validated();
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng'], 422);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Đổi mật khẩu thành công']);
    }

    public function destroy($id)
    {
        $auth = auth()->user();

        if ($auth->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ((int)$auth->id === (int)$id) {
            return response()->json(['message' => 'Không thể tự xóa chính mình'], 422);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
