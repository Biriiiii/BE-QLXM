<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Lấy thống kê tổng quan (GET /api/stats).
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'productCount' => Product::count(),
                'customerCount' => Customer::count(),
                'orderCount' => Order::count(),
                'userCount' => User::count(),
            ]
        ]);
    }
}
