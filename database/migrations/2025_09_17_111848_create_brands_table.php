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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();

            // TỐI ƯU HÓA: Thêm index UNIQUE cho 'name'.
            // Đây là cột quan trọng nhất để tìm kiếm và nên là duy nhất.
            $table->string('name', 100)->unique();

            $table->string('description')->nullable();

            // Đã có: Index 'country' rất tốt cho việc lọc.
            $table->string('country', 100)->nullable()->index();

            $table->string('logo')->nullable();
            $table->timestamps();

            // (Đề xuất thêm): Index 'created_at' nếu bạn thường xuyên sắp xếp theo brand mới nhất.
            $table->index('created_at');
        });
    }

    /**
     * Đảo ngược các migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
