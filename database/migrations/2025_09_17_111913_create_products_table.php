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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Tối ưu hóa: Thêm index cho cột 'name' để hỗ trợ tìm kiếm/sắp xếp.
            $table->string('name')->index();

            // Cột 'description' thường không cần index trừ khi dùng FullText
            $table->text('description')->nullable();

            $table->string('image')->nullable();

            // Tối ưu hóa: Thêm index cho 'price' để hỗ trợ sắp xếp/lọc theo giá.
            $table->decimal('price', 15, 2)->index();

            // Tối ưu hóa: Thêm index cho 'stock' để hỗ trợ lọc sản phẩm còn/hết hàng.
            $table->integer('stock')->default(0)->index();

            // Tối ưu hóa: Thêm index cho 'status' để lọc trạng thái sản phẩm.
            $table->enum('status', ['available', 'unavailable'])->default('available')->index();

            // Khóa ngoại tự động có index
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            // Tối ưu hóa: Index Hợp chất cho các truy vấn lọc phổ biến (Ví dụ: Lấy sản phẩm đang bán trong danh mục X)
            $table->index(['category_id', 'status']);
            // Tối ưu hóa: Index Hợp chất cho các truy vấn lọc và sắp xếp (Ví dụ: Lấy sản phẩm đang bán trong danh mục X, sắp xếp theo giá)
            $table->index(['category_id', 'status', 'price']);
        });
    }

    /**
     * Đảo ngược các migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
