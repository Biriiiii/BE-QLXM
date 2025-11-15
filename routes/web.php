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
        'app_env' => env('APP_ENV'),
        'filesystem_disk' => config('filesystems.default'),
    ];
});

// Route để serve files từ storage trên Heroku
Route::get('/storage/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

Route::get('/test-email', function () {
    try {
        Mail::raw('Đây là nội dung email test từ route.', function (Message $message) {
            $message->to('email-cua-ban@gmail.com') // Thay bằng email thật
                    ->subject('Test Mail từ Laravel BE');
        });
        
        return response()->json(['message' => 'Đã gửi email thành công!']);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
