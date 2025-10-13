
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
Route::post('auth/login',    [AuthController::class, 'login']);

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

// ------------------- ADMIN (cần auth + role:admin) -------------------
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Users
    Route::apiResource('users', UserController::class)
        ->parameters(['users' => 'id']);
    Route::patch('users/{id}/password', [UserController::class, 'changePassword'])
        ->whereNumber('id');

    // Orders
    Route::get('orders',      [OrderController::class, 'index']);
    Route::post('orders',     [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id');
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->whereNumber('id');

    // Categories
    Route::apiResource('categories', CategoryController::class)
        ->parameters(['categories' => 'id']);
    // Brands
    Route::apiResource('brands', BrandController::class)
        ->parameters(['brands' => 'id']);
    Route::put('/{id}', [BrandController::class, 'update']);
    Route::patch('/{id}', [BrandController::class, 'update']);
    // Products
    Route::apiResource('products', ProductController::class)
        ->parameters(['products' => 'id']);
    // Customers
    Route::apiResource('customers', CustomerController::class)
        ->parameters(['customers' => 'id']);
});
