<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      $table->enum('status', [
        'awaiting_verification',
        'pending',
        'processing',
        'packed',
        'shipped',
        'delivered',
        'cod_meeting',
        'completed',
        'cancelled',
      ])->default('pending')->change();
    });
  }

  public function down(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      $table->enum('status', [
        'awaiting_verification',
        'pending',
        'processing',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
      ])->default('pending')->change();
    });
  }
};
