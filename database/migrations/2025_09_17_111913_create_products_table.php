<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Index cho tìm kiếm
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Index cho lọc/sắp xếp riêng lẻ
            $table->decimal('price', 15, 2)->index();
            $table->integer('stock')->default(0)->index();
            $table->enum('status', ['available', 'unavailable'])->default('available')->index();

            // Khóa ngoại (tự động có index (brand_id) và (category_id))
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            // --- Tối ưu hóa Index hợp chất ---

            // Tối ưu cho lọc: (Danh mục + Trạng thái + Giá)
            $table->index(['category_id', 'status', 'price']);

            // (ĐỀ XUẤT THÊM) Tối ưu cho lọc: (Thương hiệu + Trạng thái + Giá)
            $table->index(['brand_id', 'status', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
