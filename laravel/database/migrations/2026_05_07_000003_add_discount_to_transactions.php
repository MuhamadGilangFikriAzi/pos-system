<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom diskon ke transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percent');
            $table->decimal('price_after_discount', 15, 2)->default(0)->after('discount_amount');
        });

        // Tambah kolom ke transactions (diskon global + pajak)
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('total');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percent');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_percent');
            $table->decimal('grand_total', 15, 2)->default(0)->after('tax_amount');
            $table->decimal('subtotal', 15, 2)->default(0)->after('grand_total');
        });

        // Update existing data — copy total to subtotal
        DB::statement('UPDATE transactions SET subtotal = total, grand_total = total');
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount', 'price_after_discount']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount', 'tax_percent', 'tax_amount', 'grand_total', 'subtotal']);
        });
    }
};
