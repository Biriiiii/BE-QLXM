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
Route::get('test', fn() => 'API test works!');
Route::get('ping', fn() => response()->json(['pong' => true]));
Route::post('auth/login', [AuthController::class, 'login']);

// ------------------- CLIENT (public, không cần auth) -------------------
Route::get('client/products/related', [ClientController::class, 'getRelatedProducts']);
Route::get('client/product/relate', [ClientController::class, 'getRelatedProducts']);
Route::get('client/categories', [ClientController::class, 'getCategories']);
Route::get('client/categories/{id}', [ClientController::class, 'getCategory']);
Route::get('client/brands', [ClientController::class, 'getBrands']);
Route::get('client/brands/{id}', [ClientController::class, 'getBrand']);
Route::get('client/products', [ClientController::class, 'getProducts']);
Route::get('client/products/{id}', [ClientController::class, 'getProduct']);
Route::post('client/orders', [ClientController::class, 'createOrder']);
// Giỏ hàng thao tác qua session
Route::post('client/cart/add', [ClientController::class, 'addToCart']);
Route::get('client/cart', [ClientController::class, 'getCart']);
Route::post('client/cart/remove', [ClientController::class, 'removeFromCart']);

// ------------------- AUTH (cần auth:sanctum) -------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

// ------------------- ADMIN & STAFF (cần auth + role:admin,staff) -------------------
// Cả Admin và Staff đều có toàn bộ quyền CRUD trên các tài nguyên này.
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

    // Categories (Tất cả hành động CRUD)
    Route::apiResource('categories', CategoryController::class)
        ->parameters(['categories' => 'id']);
    // Brands (Tất cả hành động CRUD)
    Route::apiResource('brands', BrandController::class)
        ->parameters(['brands' => 'id']);
    // Products (Tất cả hành động CRUD)
    Route::apiResource('products', ProductController::class)
        ->parameters(['products' => 'id']);
    // Customers (Tất cả hành động CRUD)
    Route::apiResource('customers', CustomerController::class)
        ->parameters(['customers' => 'id']);
});
