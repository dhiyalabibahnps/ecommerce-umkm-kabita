<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // products(status, category_id)
    Schema::table('products', function (Blueprint $table) {
      $table->index(['status', 'category_id'], 'products_status_category_idx');
    });

    // orders(buyer_id, status)
    Schema::table('orders', function (Blueprint $table) {
      $table->index(['buyer_id', 'status'], 'orders_buyer_status_idx');
    });

    // order_items(order_id)
    Schema::table('order_items', function (Blueprint $table) {
      $table->index('order_id', 'order_items_order_id_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->dropIndex('products_status_category_idx');
    });

    Schema::table('orders', function (Blueprint $table) {
      $table->dropIndex('orders_buyer_status_idx');
    });

    Schema::table('order_items', function (Blueprint $table) {
      $table->dropIndex('order_items_order_id_idx');
    });
  }
};
