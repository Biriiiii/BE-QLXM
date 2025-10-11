<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('deposit_amount', 12, 2)->nullable()->after('total_amount');
            $table->string('installment_term')->nullable()->after('deposit_amount');
            $table->decimal('installment_amount', 12, 2)->nullable()->after('installment_term');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'installment_term', 'installment_amount']);
        });
    }
};
