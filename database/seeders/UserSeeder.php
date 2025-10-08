<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::insert([
            [
                'name' => 'Admin QLXM',
                'email' => 'admin@qlxm.vn',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nhân viên bán hàng',
                'email' => 'sales@qlxm.vn',
                'password' => Hash::make('sales123'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
