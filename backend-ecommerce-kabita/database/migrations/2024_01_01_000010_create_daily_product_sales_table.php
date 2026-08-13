<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_product_sales', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('total_qty_sold')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_profit', 12, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['date', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_product_sales');
    }
};
