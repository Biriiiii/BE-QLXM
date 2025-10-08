<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return "✅ Kết nối thành công tới SQL Server!";
    } catch (\Exception $e) {
        return "❌ Lỗi: " . $e->getMessage();
    }
});

Route::get('/debug-storage', function () {
    return [
        'public_path_storage' => public_path('storage'),
        'storage_path_app_public' => storage_path('app/public'),
        'filesystems_config' => config('filesystems.links'),
        'storage_exists' => file_exists(public_path('storage')),
        'is_link' => is_link(public_path('storage')),
        'readlink' => is_link(public_path('storage')) ? readlink(public_path('storage')) : 'not a link',
    ];
});
