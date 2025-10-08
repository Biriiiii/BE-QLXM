<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run()
    {
        Brand::insert([
            ['name' => 'Honda', 'country' => 'Nhật Bản', 'logo' => 'brands/honda.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yamaha', 'country' => 'Nhật Bản', 'logo' => 'brands/yamaha.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Suzuki', 'country' => 'Nhật Bản', 'logo' => 'brands/suzuki.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SYM', 'country' => 'Đài Loan', 'logo' => 'brands/sym.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ducati', 'country' => 'Ý', 'logo' => 'brands/ducati.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GPX', 'country' => 'Thái Lan', 'logo' => 'brands/gpx.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VinFast', 'country' => 'Việt Nam', 'logo' => 'brands/vinfast.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kawasaki', 'country' => 'Nhật Bản', 'logo' => 'brands/kawasaki.jpg', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
