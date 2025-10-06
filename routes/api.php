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

/*
|--------------------------------------------------------------------------
| Public (No Auth)
|--------------------------------------------------------------------------
*/


Route::get('test', fn() => 'API test works!');
Route::get('ping', fn() => response()->json(['pong' => true]));
Route::post('auth/login',    [AuthController::class, 'login']);

// Public API cho client
Route::get('client/products/related', [ClientController::class, 'getRelatedProducts']);
Route::get('client/product/relate', [ClientController::class, 'getRelatedProducts']);
Route::get('client/categories', [ClientController::class, 'getCategories']);
Route::get('client/categories/{id}', [ClientController::class, 'getCategory']);

Route::get('client/brands', [ClientController::class, 'getBrands']);
Route::get('client/brands/{id}', [ClientController::class, 'getBrand']);

Route::get('client/products', [ClientController::class, 'getProducts']);
Route::get('client/products/{id}', [ClientController::class, 'getProduct']);

Route::post('client/orders', [ClientController::class, 'createOrder']);

/*
|--------------------------------------------------------------------------
| Protected (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Current user, logout
    Route::get('auth/me',     [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN AREA
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        // Users
        Route::apiResource('users', UserController::class)
            ->parameters(['users' => 'id']);
        Route::patch('users/{id}/password', [UserController::class, 'changePassword'])
            ->whereNumber('id');
    });

    /*
    |--------------------------------------------------------------------------
    | ORDERS (Admin + Staff)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('orders',      [OrderController::class, 'index']);
        Route::post('orders',     [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
        Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id');
        // Categories
        Route::apiResource('categories', CategoryController::class)
            ->parameters(['categories' => 'id']);

        // Brands
        Route::apiResource('brands', BrandController::class)
            ->parameters(['brands' => 'id']);

        // Products
        Route::apiResource('products', ProductController::class)
            ->parameters(['products' => 'id']);
        // Customers
        Route::apiResource('customers', CustomerController::class)
            ->parameters(['customers' => 'id']);
    });

    // Delete order (admin only)
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('role:admin');
});
