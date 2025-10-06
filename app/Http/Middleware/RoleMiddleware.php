<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Sử dụng: ->middleware('role:admin') hoặc 'role:admin,staff'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Chưa đăng nhập
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // roles có thể chứa 1 phần tử duy nhất 'admin,staff' nếu bạn viết sai cú pháp
        // nhưng trong Laravel truyền đúng 'role:admin,staff' thì ...$roles đã tách thành ['admin','staff']
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
