<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus semua user yang ada (gunakan delete() karena truncate() error karena foreign key)
        User::query()->delete();

        $emails = [];

        // 1. Buat Akun Admin Default
        $admin = User::create([
            'name' => 'Administrator Kabita',
            'email' => 'admin@kabita.test',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        $emails[] = $admin->email;

        // 2. Buat 1 Akun Seller Verified
        $verifiedSeller = User::factory()->pendingSeller()->create([
            'verified_at' => now(),
            'status' => UserStatus::ACTIVE,
        ]);
        $emails[] = $verifiedSeller->email;

        // 3. Buat 1 Akun Seller Pending (belum diverifikasi)
        $pendingSeller = User::factory()->pendingSeller()->create([
            'status' => UserStatus::INACTIVE,
            'verified_at' => null,
        ]);
        $emails[] = $pendingSeller->email;

        // 4. Buat 1 Akun Buyer
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
        ]);
        $emails[] = $buyer->email;

        // 5. Simpan semua email ke file text
        $filePath = base_path('generated_users.txt');
        $lines = [];
        $lines[] = "Email: admin@kabita.test";
        $lines[] = "Password: password";
        $lines[] = "Role: Admin";
        $lines[] = "";
        $lines[] = "Email: " . $verifiedSeller->email;
        $lines[] = "Password: password";
        $lines[] = "Role: Seller (Verified)";
        $lines[] = "";
        $lines[] = "Email: " . $pendingSeller->email;
        $lines[] = "Password: password";
        $lines[] = "Role: Seller (Pending)";
        $lines[] = "";
        $lines[] = "Email: " . $buyer->email;
        $lines[] = "Password: password";
        $lines[] = "Role: Buyer";
        File::put($filePath, implode("\n", $lines));

        $this->command->info('Generated users saved to: ' . $filePath);
        $this->command->info('Total users: ' . count($emails));
    }
}
