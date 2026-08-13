<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom proof_image jika belum ada
            if (!Schema::hasColumn('users', 'proof_image')) {
                $table->string('proof_image')->nullable()->after('status');
            }

            // Tambahkan kolom verified_by jika belum ada
            if (!Schema::hasColumn('users', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('proof_image')->constrained('users')->nullOnDelete();
            }

            // Tambahkan kolom verified_at jika belum ada
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['proof_image', 'verified_by', 'verified_at']);
        });
    }
};
