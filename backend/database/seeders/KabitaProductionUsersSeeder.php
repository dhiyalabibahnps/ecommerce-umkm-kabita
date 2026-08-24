<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class KabitaProductionUsersSeeder extends Seeder
{
  public function run(): void
  {
    $password = Hash::make('password');

    $users = [
      [
        'name' => 'Administrator Kabita',
        'email' => 'admin@kabita.my.id',
        'password' => $password,
        'role' => UserRole::ADMIN,
        'status' => UserStatus::ACTIVE,
        'email_verified_at' => now(),
      ],
      [
        'name' => 'Seller Kabita',
        'email' => 'seller@kabita.my.id',
        'password' => $password,
        'role' => UserRole::SELLER,
        'status' => UserStatus::ACTIVE,
        'email_verified_at' => now(),
      ],
      [
        'name' => 'Buyer Kabita',
        'email' => 'buyer@kabita.my.id',
        'password' => $password,
        'role' => UserRole::BUYER,
        'status' => UserStatus::ACTIVE,
        'email_verified_at' => now(),
      ],
    ];

    foreach ($users as $user) {
      User::updateOrCreate(
        ['email' => $user['email']],
        $user
      );
    }

    $content = "========================================\n";
    $content .= "  KABITA - PRODUCTION USER ACCOUNTS\n";
    $content .= "========================================\n\n";
    $content .= "Generated at : " . now()->format('d F Y H:i:s') . "\n";
    $content .= "Environment : " . app()->environment() . "\n";
    $content .= "Seeder      : KabitaProductionUsersSeeder\n\n";
    $content .= "----------------------------------------\n";
    $content .= "  DEFAULT PASSWORD UNTUK SEMUA AKUN\n";
    $content .= "----------------------------------------\n";
    $content .= "Password : password\n\n";
    $content .= "Catatan :\n";
    $content .= "- Gunakan akun ini hanya untuk keperluan testing.\n";
    $content .= "- Jangan gunakan password ini di lingkungan production.\n";
    $content .= "- Setelah login, segera ganti password melalui menu profil.\n\n";
    $content .= "----------------------------------------\n";
    $content .= "  DAFTAR AKUN\n";
    $content .= "----------------------------------------\n\n";

    foreach ($users as $index => $user) {
      $roleLabel = match ($user['role']) {
        UserRole::ADMIN => 'Admin',
        UserRole::SELLER => 'Seller',
        UserRole::BUYER => 'Buyer',
        default => 'User',
      };

      $content .= "Akun " . ($index + 1) . "\n";
      $content .= "Nama     : " . $user['name'] . "\n";
      $content .= "Email    : " . $user['email'] . "\n";
      $content .= "Peran    : " . $roleLabel . "\n";
      $content .= "Status   : Aktif\n";
      $content .= "Password : password\n";
      $content .= "Login URL: " . rtrim(config('app.url'), '/') . "/login\n";
      $content .= "\n";
    }

    $content .= "----------------------------------------\n";
    $content .= "  PENJELASAN PERAN\n";
    $content .= "----------------------------------------\n\n";
    $content .= "1. Admin\n";
    $content .= "   - Bertugas mengelola sistem Kabita.\n";
    $content .= "   - Bisa memverifikasi toko dan produk.\n";
    $content .= "   - Tidak bisa melakukan transaksi sebagai pembeli.\n\n";
    $content .= "2. Seller\n";
    $content .= "   - Bertugas menjual produk UMKM.\n";
    $content .= "   - Bisa menambah produk dan mengelola pesanan.\n";
    $content .= "   - Tidak bisa membeli produk di Kabita.\n\n";
    $content .= "3. Buyer\n";
    $content .= "   - Bertugas membeli produk UMKM.\n";
    $content .= "   - Bisa menambahkan produk ke keranjang.\n";
    $content .= "   - Tidak bisa menjual produk di Kabita.\n\n";
    $content .= "----------------------------------------\n";
    $content .= "  TIPS PENGGUNAAN\n";
    $content .= "----------------------------------------\n\n";
    $content .= "- Setelah login, ubah password melalui menu profil.\n";
    $content .= "- Jika lupa password, gunakan fitur lupa password.\n";
    $content .= "- Jangan bagikan akun ini ke orang lain.\n";
    $content .= "- Untuk reset data, jalankan seeder kembali.\n\n";
    $content .= "========================================\n";
    $content .= "  END OF FILE\n";
    $content .= "========================================\n";

    File::put(base_path('generated_users.txt'), $content);

    $this->command?->info('Production users seeded and generated_users.txt created.');
  }
}
