<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::insert([
            [
                'customer_id' => 1,
                'order_date' => '2025-09-25 09:00:00',
                'total_amount' => 39000000,
                'status' => 'paid'
            ],
            [
                'customer_id' => 2,
                'order_date' => '2025-09-24 14:30:00',
                'total_amount' => 32000000,
                'status' => 'completed'
            ],
        ]);
    }
}
