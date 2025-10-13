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
// Users (Tất cả hành động CRUD)
Route::apiResource('users', UserController::class)
    ->parameters(['users' => 'id'])
    ->middleware(['auth:sanctum', 'role:admin,staff']);
Route::patch('users/{id}/password', [UserController::class, 'changePassword'])
    ->whereNumber('id')
    ->middleware(['auth:sanctum', 'role:admin,staff']);

// Orders (Tất cả hành động CRUD)
Route::get('orders', [OrderController::class, 'index'])->middleware(['auth:sanctum', 'role:admin,staff']);
Route::post('orders', [OrderController::class, 'store'])->middleware(['auth:sanctum', 'role:admin,staff']);
Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id')->middleware(['auth:sanctum', 'role:admin,staff']);
Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id')->middleware(['auth:sanctum', 'role:admin,staff']);
Route::delete('orders/{id}', [OrderController::class, 'destroy'])->whereNumber('id')->middleware(['auth:sanctum', 'role:admin,staff']);

// Categories (Tất cả hành động CRUD)
Route::apiResource('categories', CategoryController::class)
    ->parameters(['categories' => 'id'])
    ->middleware(['auth:sanctum', 'role:admin,staff']);
// Brands (Tất cả hành động CRUD)
Route::apiResource('brands', BrandController::class)
    ->parameters(['brands' => 'id'])
    ->middleware(['auth:sanctum', 'role:admin,staff']);
// Products (Tất cả hành động CRUD)
Route::apiResource('products', ProductController::class)
    ->parameters(['products' => 'id'])
    ->middleware(['auth:sanctum', 'role:admin,staff']);
// Customers (Tất cả hành động CRUD)
Route::apiResource('customers', CustomerController::class)
    ->parameters(['customers' => 'id'])
    ->middleware(['auth:sanctum', 'role:admin,staff']);
