<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('daily_product_sales', function (Blueprint $table) {
      $table->index('date');
      $table->index('shop_id');
      $table->index(['date', 'shop_id']);
    });
  }

  public function down(): void
  {
    Schema::table('daily_product_sales', function (Blueprint $table) {
      $table->dropIndex(['date']);
      $table->dropIndex(['shop_id']);
      $table->dropIndex(['date', 'shop_id']);
    });
  }
};
