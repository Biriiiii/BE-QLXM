<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::insert([
            ['name' => 'Xe số', 'description' => 'Các dòng xe số phổ thông', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Xe ga', 'description' => 'Các dòng xe tay ga', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Xe côn tay', 'description' => 'Các dòng xe côn tay thể thao', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
