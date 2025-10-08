<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel API is running!',
        'timestamp' => now(),
        'app_url' => config('app.url'),
        'environment' => config('app.env')
    ]);
});

Route::get('/test', function () {
    return 'Hello World!';
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
