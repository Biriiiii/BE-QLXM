<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    UserController,
    CategoryController,
    BrandController,
    ProductController,
    CustomerController,
    OrderController,
    ClientController
};

// ------------------- TEST & AUTH (public) -------------------
// API test endpoint
Route::get('test', fn() => 'API test works!');
// Health check / ping
Route::get('ping', fn() => response()->json(['pong' => true]));
// User Login
Route::post('auth/login', [AuthController::class, 'login']);

// ------------------- CLIENT (public, không cần auth) -------------------
Route::prefix('client')->group(function () {
    // Products
    Route::get('products', [ClientController::class, 'getProducts']);
    Route::get('products/{id}', [ClientController::class, 'getProduct']);
    Route::get('products/related', [ClientController::class, 'getRelatedProducts']);
    Route::get('product/relate', [ClientController::class, 'getRelatedProducts']); // Alias/Fallback

    // Categories
    Route::get('categories', [ClientController::class, 'getCategories']);
    Route::get('categories/{id}', [ClientController::class, 'getCategory']);

    // Brands
    Route::get('brands', [ClientController::class, 'getBrands']);
    Route::get('brands/{id}', [ClientController::class, 'getBrand']);

    // Orders & Cart
    Route::post('orders', [ClientController::class, 'createOrder']);
    Route::post('cart/add', [ClientController::class, 'addToCart']);
    Route::get('cart', [ClientController::class, 'getCart']);
    Route::post('cart/remove', [ClientController::class, 'removeFromCart']);
});

// ------------------- AUTH (cần auth:sanctum) -------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

// ------------------- ADMIN & STAFF (cần auth + role:admin,staff) -------------------
Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {

    // Users (Tất cả hành động CRUD)
    Route::apiResource('users', UserController::class)
        ->parameters(['users' => 'id']);
    Route::patch('users/{id}/password', [UserController::class, 'changePassword'])
        ->whereNumber('id');

    // Orders (Tất cả hành động CRUD)
    // Sử dụng route tùy chỉnh cho các hành động không phải CRUD tiêu chuẩn
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id');
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->whereNumber('id');

    // CRUD Resources (Categories, Brands, Products, Customers)
    Route::apiResource('categories', CategoryController::class)
        ->parameters(['categories' => 'id']);

    Route::apiResource('brands', BrandController::class)
        ->parameters(['brands' => 'id']);

    Route::apiResource('products', ProductController::class)
        ->parameters(['products' => 'id']);

    Route::apiResource('customers', CustomerController::class)
        ->parameters(['customers' => 'id']);
});
