<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Chạy các migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // TỐI ƯU HÓA: Thêm index UNIQUE cho 'name'.
            // Tên danh mục nên là duy nhất và là cột tìm kiếm chính.
            $table->string('name', 100)->unique();

            $table->text('description')->nullable();
            $table->timestamps();

            // (Đề xuất thêm): Index 'created_at' nếu bạn thường sắp xếp danh mục mới nhất.
            $table->index('created_at');
        });
    }

    /**
     * Đảo ngược các migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
