<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CodLocation;
use App\Models\DailyProductSales;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class KabitaFullDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->clearDemoData();
            $this->seedUsers();
            $this->seedShops();
            $this->seedCategories();
            $this->seedProducts();
            $this->seedProductImages();
            $this->seedCarts();
            $this->seedCartItems();
            $this->seedOrders();
            $this->seedOrderItems();
            $this->seedPayments();
            $this->seedDailyProductSales();
            $this->seedEmailVerificationCodes();
            $this->seedPersonalAccessTokens();
            $this->seedCodLocations();
            $this->seedPaymentSettings();
        });

        $this->command->info('Kabita full data seeded.');
    }

    private function clearDemoData(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'daily_product_sales',
            'payments',
            'order_items',
            'orders',
            'cart_items',
            'carts',
            'cod_locations',
            'payment_settings',
            'product_images',
            'products',
            'shops',
            'categories',
            'users',
        ] as $table) {
            DB::table($table)->delete();
        }
        Schema::enableForeignKeyConstraints();
    }

    private function seedUsers(): void
    {
        $users = [
            ['id' => 1, 'name' => 'Admin Kabita', 'email' => 'admin@kabita.test', 'email_verified_at' => '2026-01-01 00:00:00', 'password' => Hash::make('password'), 'role' => 'admin', 'phone' => '081234500001', 'address' => 'Jl. Merdeka No.1, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'name' => 'Siti Aminah', 'email' => 'siti@umkm.test', 'email_verified_at' => '2026-01-02 00:00:00', 'password' => Hash::make('password'), 'role' => 'seller', 'phone' => '081234500002', 'address' => 'Jl. Pasar No.2, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 00:00:00'],
            ['id' => 3, 'name' => 'Budi Santoso', 'email' => 'budi@umkm.test', 'email_verified_at' => '2026-01-03 00:00:00', 'password' => Hash::make('password'), 'role' => 'seller', 'phone' => '081234500003', 'address' => 'Jl. Melati No.3, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 00:00:00'],
            ['id' => 4, 'name' => 'Rina Wati', 'email' => 'rina@umkm.test', 'email_verified_at' => '2026-01-04 00:00:00', 'password' => Hash::make('password'), 'role' => 'seller', 'phone' => '081234500004', 'address' => 'Jl. Mawar No.4, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 00:00:00'],
            ['id' => 5, 'name' => 'Andi Wijaya', 'email' => 'andi@buyer.test', 'email_verified_at' => '2026-01-05 00:00:00', 'password' => Hash::make('password'), 'role' => 'buyer', 'phone' => '081234500005', 'address' => 'Jl. Kenari No.5, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 00:00:00'],
            ['id' => 6, 'name' => 'Dewi Lestari', 'email' => 'dewi@buyer.test', 'email_verified_at' => '2026-01-06 00:00:00', 'password' => Hash::make('password'), 'role' => 'buyer', 'phone' => '081234500006', 'address' => 'Jl. Cempaka No.6, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-06 00:00:00', 'updated_at' => '2026-01-06 00:00:00'],
            ['id' => 7, 'name' => 'Fajar Nugroho', 'email' => 'fajar@buyer.test', 'email_verified_at' => '2026-01-07 00:00:00', 'password' => Hash::make('password'), 'role' => 'buyer', 'phone' => '081234500007', 'address' => 'Jl. Anggrek No.7, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-07 00:00:00', 'updated_at' => '2026-01-07 00:00:00'],
            ['id' => 8, 'name' => 'Maya Putri', 'email' => 'maya@buyer.test', 'email_verified_at' => '2026-01-08 00:00:00', 'password' => Hash::make('password'), 'role' => 'buyer', 'phone' => '081234500008', 'address' => 'Jl. Dahlia No.8, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-08 00:00:00', 'updated_at' => '2026-01-08 00:00:00'],
            ['id' => 9, 'name' => 'Rahmat Hidayat', 'email' => 'rahmat@buyer.test', 'email_verified_at' => '2026-01-09 00:00:00', 'password' => Hash::make('password'), 'role' => 'buyer', 'phone' => '081234500009', 'address' => 'Jl. Flamboyan No.9, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-09 00:00:00', 'updated_at' => '2026-01-09 00:00:00'],
            ['id' => 10, 'name' => 'Lina Marlina', 'email' => 'lina@buyer.test', 'email_verified_at' => '2026-01-10 00:00:00', 'password' => Hash::make('password'), 'role' => 'buyer', 'phone' => '081234500010', 'address' => 'Jl. Sakura No.10, Kabupaten A', 'remember_token' => null, 'status' => 'active', 'proof_image' => null, 'verified_by' => null, 'verified_at' => null, 'created_at' => '2026-01-10 00:00:00', 'updated_at' => '2026-01-10 00:00:00'],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(['id' => $user['id']], $user);
        }
    }

    private function seedShops(): void
    {
        $shops = [
            ['id' => 1, 'seller_id' => 2, 'name' => 'Warung Makan Siti', 'slug' => 'warung-makan-siti', 'description' => 'Warung makan rumahan dengan masakan tradisional khas Kabupaten A.', 'logo' => 'https://via.placeholder.com/150/FF6B6B/FFFFFF?text=Warung+Makan+Siti', 'status' => 'verified', 'verified_by' => 1, 'verified_at' => '2026-01-02 08:00:00', 'rejection_reason' => null, 'phone' => '081234500002', 'address' => 'Jl. Pasar No.2, Kabupaten A', 'banner' => null, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 08:00:00'],
            ['id' => 2, 'seller_id' => 3, 'name' => 'Budi Craft', 'slug' => 'budi-craft', 'description' => 'Produk kerajinan tangan dari bahan daur ulang dan kayu.', 'logo' => 'https://via.placeholder.com/150/4ECDC4/FFFFFF?text=Budi+Craft', 'status' => 'verified', 'verified_by' => 1, 'verified_at' => '2026-01-03 08:00:00', 'rejection_reason' => null, 'phone' => '081234500003', 'address' => 'Jl. Melati No.3, Kabupaten A', 'banner' => null, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 08:00:00'],
            ['id' => 3, 'seller_id' => 4, 'name' => 'Rina Fashion', 'slug' => 'rina-fashion', 'description' => 'Toko fashion lokal dengan desain simple dan elegan.', 'logo' => 'https://via.placeholder.com/150/FFE66D/333333?text=Rina+Fashion', 'status' => 'verified', 'verified_by' => 1, 'verified_at' => '2026-01-04 08:00:00', 'rejection_reason' => null, 'phone' => '081234500004', 'address' => 'Jl. Mawar No.4, Kabupaten A', 'banner' => null, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 08:00:00'],
            ['id' => 4, 'seller_id' => 2, 'name' => 'Siti Snack Box', 'slug' => 'siti-snack-box', 'description' => 'Snack box untuk acara dan oleh-oleh khas daerah.', 'logo' => 'https://via.placeholder.com/150/95E1D3/333333?text=Siti+Snack+Box', 'status' => 'verified', 'verified_by' => 1, 'verified_at' => '2026-01-05 08:00:00', 'rejection_reason' => null, 'phone' => '081234500002', 'address' => 'Jl. Pasar No.2, Kabupaten A', 'banner' => null, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 08:00:00'],
        ];

        foreach ($shops as $shop) {
            Shop::updateOrCreate(['id' => $shop['id']], $shop);
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Makanan', 'slug' => 'makanan', 'icon' => '🍴', 'description' => 'Kategori produk makanan dan camilan.', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'name' => 'Minuman', 'slug' => 'minuman', 'icon' => '☕', 'description' => 'Kategori produk minuman segar dan kemasan.', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 3, 'name' => 'Fashion', 'slug' => 'fashion', 'icon' => '👕', 'description' => 'Kategori produk fashion dan pakaian.', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 4, 'name' => 'Kerajinan', 'slug' => 'kerajinan', 'icon' => '🔨', 'description' => 'Kategori produk kerajinan tangan.', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 5, 'name' => 'Kecantikan', 'slug' => 'kecantikan', 'icon' => '✨', 'description' => 'Kategori produk kecantikan dan perawatan.', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 6, 'name' => 'Oleh-oleh', 'slug' => 'oleh-oleh', 'icon' => '🎁', 'description' => 'Kategori produk oleh-oleh khas daerah.', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['id' => $category['id']], $category);
        }
    }

    private function seedProducts(): void
    {
        $products = [
            ['id' => 1, 'shop_id' => 1, 'category_id' => 1, 'name' => 'Nasi Goreng Spesial', 'slug' => 'nasi-goreng-spesial', 'description' => 'Nasi goreng dengan telur, ayam, dan kerupuk.', 'price' => 25000.00, 'cost_price' => 15000.00, 'stock' => 50, 'status' => 'approved', 'weight' => 500, 'verified_at' => '2026-01-02 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 09:00:00'],
            ['id' => 2, 'shop_id' => 1, 'category_id' => 1, 'name' => 'Mie Goreng Jawa', 'slug' => 'mie-goreng-jawa', 'description' => 'Mie goreng khas Jawa dengan sayuran segar.', 'price' => 20000.00, 'cost_price' => 12000.00, 'stock' => 40, 'status' => 'approved', 'weight' => 400, 'verified_at' => '2026-01-02 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 09:00:00'],
            ['id' => 3, 'shop_id' => 1, 'category_id' => 2, 'name' => 'Es Jeruk Peras', 'slug' => 'es-jeruk-peras', 'description' => 'Es jeruk segar tanpa pengawet.', 'price' => 8000.00, 'cost_price' => 4000.00, 'stock' => 100, 'status' => 'approved', 'weight' => 300, 'verified_at' => '2026-01-02 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 09:00:00'],
            ['id' => 4, 'shop_id' => 1, 'category_id' => 6, 'name' => 'Snack Box Nasi Tumpeng Mini', 'slug' => 'snack-box-nasi-tumpeng-mini', 'description' => 'Snack box nasi tumpeng mini untuk acara.', 'price' => 75000.00, 'cost_price' => 50000.00, 'stock' => 20, 'status' => 'approved', 'weight' => 1200, 'verified_at' => '2026-01-05 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 09:00:00'],
            ['id' => 5, 'shop_id' => 2, 'category_id' => 4, 'name' => 'Tas Anyaman Rotan', 'slug' => 'tas-anyaman-rotan', 'description' => 'Tas anyaman rotan handmade.', 'price' => 120000.00, 'cost_price' => 70000.00, 'stock' => 15, 'status' => 'approved', 'weight' => 800, 'verified_at' => '2026-01-03 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 09:00:00'],
            ['id' => 6, 'shop_id' => 2, 'category_id' => 4, 'name' => 'Frame Foto Kayu Jati', 'slug' => 'frame-foto-kayu-jati', 'description' => 'Frame foto dari kayu jati solid.', 'price' => 85000.00, 'cost_price' => 50000.00, 'stock' => 25, 'status' => 'approved', 'weight' => 600, 'verified_at' => '2026-01-03 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 09:00:00'],
            ['id' => 7, 'shop_id' => 2, 'category_id' => 4, 'name' => 'Cindera Mata Wooden', 'slug' => 'cindera-mata-wooden', 'description' => 'Cindera mata dari bahan woodcraft.', 'price' => 65000.00, 'cost_price' => 40000.00, 'stock' => 30, 'status' => 'approved', 'weight' => 300, 'verified_at' => '2026-01-03 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 09:00:00'],
            ['id' => 8, 'shop_id' => 3, 'category_id' => 3, 'name' => 'Kemeja Batik Wanita', 'slug' => 'kemeja-batik-wanita', 'description' => 'Kemeja batik modern untuk wanita.', 'price' => 150000.00, 'cost_price' => 90000.00, 'stock' => 20, 'status' => 'approved', 'weight' => 250, 'verified_at' => '2026-01-04 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 09:00:00'],
            ['id' => 9, 'shop_id' => 3, 'category_id' => 3, 'name' => 'Rok Plisket Premium', 'slug' => 'rok-plisket-premium', 'description' => 'Rok plisket bahan premium.', 'price' => 110000.00, 'cost_price' => 65000.00, 'stock' => 25, 'status' => 'approved', 'weight' => 200, 'verified_at' => '2026-01-04 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 09:00:00'],
            ['id' => 10, 'shop_id' => 3, 'category_id' => 3, 'name' => 'Tas Selempang Kanvas', 'slug' => 'tas-selempang-kanvas', 'description' => 'Tas selempang kanvas lokal.', 'price' => 95000.00, 'cost_price' => 55000.00, 'stock' => 18, 'status' => 'approved', 'weight' => 450, 'verified_at' => '2026-01-04 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 09:00:00'],
            ['id' => 11, 'shop_id' => 4, 'category_id' => 6, 'name' => 'Snack Box Kue Kering', 'slug' => 'snack-box-kue-kering', 'description' => 'Kue kering dalam snack box.', 'price' => 60000.00, 'cost_price' => 35000.00, 'stock' => 30, 'status' => 'approved', 'weight' => 700, 'verified_at' => '2026-01-05 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 09:00:00'],
            ['id' => 12, 'shop_id' => 4, 'category_id' => 6, 'name' => 'Dodol Garut Premium', 'slug' => 'dodol-garut-premium', 'description' => 'Dodol garut khas dengan kemasan premium.', 'price' => 45000.00, 'cost_price' => 25000.00, 'stock' => 35, 'status' => 'approved', 'weight' => 500, 'verified_at' => '2026-01-05 09:00:00', 'rejection_reason' => null, 'deleted_at' => null, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 09:00:00'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['id' => $product['id']], $product);
        }
    }

    private function seedProductImages(): void
    {
        $images = [
            ['id' => 1, 'product_id' => 1, 'image_path' => 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Nasi+Goreng+Spesial', 'order_column' => 1, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 00:00:00'],
            ['id' => 2, 'product_id' => 1, 'image_path' => 'https://via.placeholder.com/400x300/FF8E8E/FFFFFF?text=Nasi+Goreng+2', 'order_column' => 2, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 00:00:00'],
            ['id' => 3, 'product_id' => 2, 'image_path' => 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=Mie+Goreng+Jawa', 'order_column' => 1, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 00:00:00'],
            ['id' => 4, 'product_id' => 3, 'image_path' => 'https://via.placeholder.com/400x300/95E1D3/333333?text=Es+Jeruk+Peras', 'order_column' => 1, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 00:00:00'],
            ['id' => 5, 'product_id' => 4, 'image_path' => 'https://via.placeholder.com/400x300/FFE66D/333333?text=Snack+Box+Nasi+Tumpeng+Mini', 'order_column' => 1, 'created_at' => '2026-01-02 00:00:00', 'updated_at' => '2026-01-02 00:00:00'],
            ['id' => 6, 'product_id' => 5, 'image_path' => 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Tas+Anyaman+Rotan', 'order_column' => 1, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 00:00:00'],
            ['id' => 7, 'product_id' => 6, 'image_path' => 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=Frame+Foto+Kayu+Jati', 'order_column' => 1, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 00:00:00'],
            ['id' => 8, 'product_id' => 7, 'image_path' => 'https://via.placeholder.com/400x300/95E1D3/333333?text=Cindera+Mata+Wooden', 'order_column' => 1, 'created_at' => '2026-01-03 00:00:00', 'updated_at' => '2026-01-03 00:00:00'],
            ['id' => 9, 'product_id' => 8, 'image_path' => 'https://via.placeholder.com/400x300/FFE66D/333333?text=Kemeja+Batik+Wanita', 'order_column' => 1, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 00:00:00'],
            ['id' => 10, 'product_id' => 9, 'image_path' => 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Rok+Plisket+Premium', 'order_column' => 1, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 00:00:00'],
            ['id' => 11, 'product_id' => 10, 'image_path' => 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=Tas+Selempang+Kanvas', 'order_column' => 1, 'created_at' => '2026-01-04 00:00:00', 'updated_at' => '2026-01-04 00:00:00'],
            ['id' => 12, 'product_id' => 11, 'image_path' => 'https://via.placeholder.com/400x300/95E1D3/333333?text=Snack+Box+Kue+Kering', 'order_column' => 1, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 00:00:00'],
            ['id' => 13, 'product_id' => 12, 'image_path' => 'https://via.placeholder.com/400x300/FFE66D/333333?text=Dodol+Garut+Premium', 'order_column' => 1, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 00:00:00'],
        ];

        foreach ($images as $image) {
            ProductImage::updateOrCreate(['id' => $image['id']], $image);
        }
    }

    private function seedCarts(): void
    {
        $carts = [
            ['id' => 1, 'buyer_id' => 5, 'created_at' => '2026-02-01 00:00:00', 'updated_at' => '2026-02-05 00:00:00'],
            ['id' => 2, 'buyer_id' => 6, 'created_at' => '2026-02-02 00:00:00', 'updated_at' => '2026-02-06 00:00:00'],
            ['id' => 3, 'buyer_id' => 7, 'created_at' => '2026-02-03 00:00:00', 'updated_at' => '2026-02-07 00:00:00'],
            ['id' => 4, 'buyer_id' => 8, 'created_at' => '2026-02-04 00:00:00', 'updated_at' => '2026-02-08 00:00:00'],
            ['id' => 5, 'buyer_id' => 9, 'created_at' => '2026-02-05 00:00:00', 'updated_at' => '2026-02-09 00:00:00'],
            ['id' => 6, 'buyer_id' => 10, 'created_at' => '2026-02-06 00:00:00', 'updated_at' => '2026-02-10 00:00:00'],
        ];

        foreach ($carts as $cart) {
            Cart::updateOrCreate(['id' => $cart['id']], $cart);
        }
    }

    private function seedCartItems(): void
    {
        $items = [
            ['id' => 1, 'cart_id' => 1, 'product_id' => 1, 'quantity' => 2, 'created_at' => '2026-02-01 00:00:00', 'updated_at' => '2026-02-05 00:00:00'],
            ['id' => 2, 'cart_id' => 1, 'product_id' => 3, 'quantity' => 1, 'created_at' => '2026-02-01 00:00:00', 'updated_at' => '2026-02-05 00:00:00'],
            ['id' => 3, 'cart_id' => 2, 'product_id' => 8, 'quantity' => 1, 'created_at' => '2026-02-02 00:00:00', 'updated_at' => '2026-02-06 00:00:00'],
            ['id' => 4, 'cart_id' => 2, 'product_id' => 9, 'quantity' => 2, 'created_at' => '2026-02-02 00:00:00', 'updated_at' => '2026-02-06 00:00:00'],
            ['id' => 5, 'cart_id' => 3, 'product_id' => 5, 'quantity' => 1, 'created_at' => '2026-02-03 00:00:00', 'updated_at' => '2026-02-07 00:00:00'],
            ['id' => 6, 'cart_id' => 4, 'product_id' => 11, 'quantity' => 2, 'created_at' => '2026-02-04 00:00:00', 'updated_at' => '2026-02-08 00:00:00'],
            ['id' => 7, 'cart_id' => 5, 'product_id' => 2, 'quantity' => 1, 'created_at' => '2026-02-05 00:00:00', 'updated_at' => '2026-02-09 00:00:00'],
            ['id' => 8, 'cart_id' => 6, 'product_id' => 6, 'quantity' => 1, 'created_at' => '2026-02-06 00:00:00', 'updated_at' => '2026-02-10 00:00:00'],
        ];

        foreach ($items as $item) {
            CartItem::updateOrCreate(['id' => $item['id']], $item);
        }
    }

    private function seedOrders(): void
    {
        $orders = [
            ['id' => 1, 'order_number' => 'ORD-20260201-001', 'buyer_id' => 5, 'shop_id' => 1, 'subtotal' => 58000.00, 'shipping_cost' => 10000.00, 'total_amount' => 68000.00, 'shipping_method' => 'kurir', 'payment_method' => 'transfer', 'status' => 'delivered', 'shipping_address' => 'Jl. Kenari No.5, Kabupaten A', 'tracking_number' => 'TRK001', 'notes' => 'Kirim sebelum jam 12.', 'created_at' => '2026-02-01 10:00:00', 'updated_at' => '2026-02-03 14:00:00'],
            ['id' => 2, 'order_number' => 'ORD-20260202-002', 'buyer_id' => 6, 'shop_id' => 3, 'subtotal' => 330000.00, 'shipping_cost' => 15000.00, 'total_amount' => 345000.00, 'shipping_method' => 'kurir', 'payment_method' => 'transfer', 'status' => 'shipped', 'shipping_address' => 'Jl. Cempaka No.6, Kabupaten A', 'tracking_number' => 'TRK002', 'notes' => 'Pack dengan aman.', 'created_at' => '2026-02-02 11:00:00', 'updated_at' => '2026-02-05 09:00:00'],
            ['id' => 3, 'order_number' => 'ORD-20260203-003', 'buyer_id' => 7, 'shop_id' => 2, 'subtotal' => 120000.00, 'shipping_cost' => 12000.00, 'total_amount' => 132000.00, 'shipping_method' => 'cod', 'payment_method' => 'cod', 'status' => 'processing', 'shipping_address' => 'Jl. Anggrek No.7, Kabupaten A', 'tracking_number' => null, 'notes' => 'COD sesuai.', 'created_at' => '2026-02-03 09:30:00', 'updated_at' => '2026-02-04 08:00:00'],
            ['id' => 4, 'order_number' => 'ORD-20260204-004', 'buyer_id' => 8, 'shop_id' => 4, 'subtotal' => 120000.00, 'shipping_cost' => 10000.00, 'total_amount' => 130000.00, 'shipping_method' => 'kurir', 'payment_method' => 'transfer', 'status' => 'pending', 'shipping_address' => 'Jl. Dahlia No.8, Kabupaten A', 'tracking_number' => null, 'notes' => 'Segera dikirim.', 'created_at' => '2026-02-04 13:00:00', 'updated_at' => '2026-02-04 13:00:00'],
            ['id' => 5, 'order_number' => 'ORD-20260205-005', 'buyer_id' => 9, 'shop_id' => 1, 'subtotal' => 20000.00, 'shipping_cost' => 8000.00, 'total_amount' => 28000.00, 'shipping_method' => 'cod', 'payment_method' => 'cod', 'status' => 'pending', 'shipping_address' => 'Jl. Flamboyan No.9, Kabupaten A', 'tracking_number' => null, 'notes' => null, 'created_at' => '2026-02-05 16:00:00', 'updated_at' => '2026-02-05 16:00:00'],
            ['id' => 6, 'order_number' => 'ORD-20260206-006', 'buyer_id' => 10, 'shop_id' => 2, 'subtotal' => 85000.00, 'shipping_cost' => 11000.00, 'total_amount' => 96000.00, 'shipping_method' => 'kurir', 'payment_method' => 'transfer', 'status' => 'cancelled', 'shipping_address' => 'Jl. Sakura No.10, Kabupaten A', 'tracking_number' => null, 'notes' => 'Dibatalkan pembeli.', 'created_at' => '2026-02-06 10:00:00', 'updated_at' => '2026-02-06 12:00:00'],
            ['id' => 7, 'order_number' => 'ORD-20260207-007', 'buyer_id' => 5, 'shop_id' => 3, 'subtotal' => 95000.00, 'shipping_cost' => 13000.00, 'total_amount' => 108000.00, 'shipping_method' => 'kurir', 'payment_method' => 'transfer', 'status' => 'delivered', 'shipping_address' => 'Jl. Kenari No.5, Kabupaten A', 'tracking_number' => 'TRK003', 'notes' => null, 'created_at' => '2026-02-07 08:00:00', 'updated_at' => '2026-02-09 10:00:00'],
            ['id' => 8, 'order_number' => 'ORD-20260208-008', 'buyer_id' => 6, 'shop_id' => 1, 'subtotal' => 16000.00, 'shipping_cost' => 9000.00, 'total_amount' => 25000.00, 'shipping_method' => 'cod', 'payment_method' => 'cod', 'status' => 'processing', 'shipping_address' => 'Jl. Cempaka No.6, Kabupaten A', 'tracking_number' => null, 'notes' => null, 'created_at' => '2026-02-08 15:00:00', 'updated_at' => '2026-02-09 07:00:00'],
        ];

        foreach ($orders as $order) {
            Order::updateOrCreate(['id' => $order['id']], $order);
        }
    }

    private function seedOrderItems(): void
    {
        $items = [
            ['id' => 1, 'order_id' => 1, 'product_id' => 1, 'quantity' => 2, 'price_snapshot' => 25000.00, 'cost_snapshot' => 15000.00, 'created_at' => '2026-02-01 10:00:00', 'updated_at' => '2026-02-01 10:00:00'],
            ['id' => 2, 'order_id' => 1, 'product_id' => 3, 'quantity' => 1, 'price_snapshot' => 8000.00, 'cost_snapshot' => 4000.00, 'created_at' => '2026-02-01 10:00:00', 'updated_at' => '2026-02-01 10:00:00'],
            ['id' => 3, 'order_id' => 2, 'product_id' => 8, 'quantity' => 1, 'price_snapshot' => 150000.00, 'cost_snapshot' => 90000.00, 'created_at' => '2026-02-02 11:00:00', 'updated_at' => '2026-02-02 11:00:00'],
            ['id' => 4, 'order_id' => 2, 'product_id' => 9, 'quantity' => 2, 'price_snapshot' => 110000.00, 'cost_snapshot' => 65000.00, 'created_at' => '2026-02-02 11:00:00', 'updated_at' => '2026-02-02 11:00:00'],
            ['id' => 5, 'order_id' => 3, 'product_id' => 5, 'quantity' => 1, 'price_snapshot' => 120000.00, 'cost_snapshot' => 70000.00, 'created_at' => '2026-02-03 09:30:00', 'updated_at' => '2026-02-03 09:30:00'],
            ['id' => 6, 'order_id' => 4, 'product_id' => 11, 'quantity' => 2, 'price_snapshot' => 60000.00, 'cost_snapshot' => 35000.00, 'created_at' => '2026-02-04 13:00:00', 'updated_at' => '2026-02-04 13:00:00'],
            ['id' => 7, 'order_id' => 5, 'product_id' => 2, 'quantity' => 1, 'price_snapshot' => 20000.00, 'cost_snapshot' => 12000.00, 'created_at' => '2026-02-05 16:00:00', 'updated_at' => '2026-02-05 16:00:00'],
            ['id' => 8, 'order_id' => 6, 'product_id' => 6, 'quantity' => 1, 'price_snapshot' => 85000.00, 'cost_snapshot' => 50000.00, 'created_at' => '2026-02-06 10:00:00', 'updated_at' => '2026-02-06 10:00:00'],
            ['id' => 9, 'order_id' => 7, 'product_id' => 10, 'quantity' => 1, 'price_snapshot' => 95000.00, 'cost_snapshot' => 55000.00, 'created_at' => '2026-02-07 08:00:00', 'updated_at' => '2026-02-07 08:00:00'],
            ['id' => 10, 'order_id' => 8, 'product_id' => 2, 'quantity' => 1, 'price_snapshot' => 20000.00, 'cost_snapshot' => 12000.00, 'created_at' => '2026-02-08 15:00:00', 'updated_at' => '2026-02-08 15:00:00'],
            ['id' => 11, 'order_id' => 8, 'product_id' => 3, 'quantity' => 1, 'price_snapshot' => 8000.00, 'cost_snapshot' => 4000.00, 'created_at' => '2026-02-08 15:00:00', 'updated_at' => '2026-02-08 15:00:00'],
        ];

        foreach ($items as $item) {
            OrderItem::updateOrCreate(['id' => $item['id']], $item);
        }
    }

    private function seedPayments(): void
    {
        $payments = [
            ['id' => 1, 'order_id' => 1, 'amount' => 68000.00, 'proof_image' => 'payments/ord-20260201-001-proof.jpg', 'status' => 'verified', 'created_at' => '2026-02-01 10:30:00', 'updated_at' => '2026-02-02 08:00:00'],
            ['id' => 2, 'order_id' => 2, 'amount' => 345000.00, 'proof_image' => 'payments/ord-20260202-002-proof.jpg', 'status' => 'verified', 'created_at' => '2026-02-02 12:00:00', 'updated_at' => '2026-02-04 09:00:00'],
            ['id' => 3, 'order_id' => 4, 'amount' => 130000.00, 'proof_image' => null, 'status' => 'pending', 'created_at' => '2026-02-04 13:30:00', 'updated_at' => '2026-02-04 13:30:00'],
            ['id' => 4, 'order_id' => 7, 'amount' => 108000.00, 'proof_image' => 'payments/ord-20260207-007-proof.jpg', 'status' => 'verified', 'created_at' => '2026-02-07 09:00:00', 'updated_at' => '2026-02-08 08:00:00'],
            ['id' => 5, 'order_id' => 8, 'amount' => 25000.00, 'proof_image' => null, 'status' => 'pending', 'created_at' => '2026-02-08 15:30:00', 'updated_at' => '2026-02-08 15:30:00'],
        ];

        foreach ($payments as $payment) {
            Payment::updateOrCreate(['id' => $payment['id']], $payment);
        }
    }

    private function seedDailyProductSales(): void
    {
        $sales = [
            ['id' => 1, 'date' => '2026-02-01', 'product_id' => 1, 'shop_id' => 1, 'category_id' => 1, 'total_qty_sold' => 2, 'total_revenue' => 50000.00, 'total_profit' => 20000.00, 'created_at' => '2026-02-01 23:59:00', 'updated_at' => '2026-02-01 23:59:00'],
            ['id' => 2, 'date' => '2026-02-01', 'product_id' => 3, 'shop_id' => 1, 'category_id' => 2, 'total_qty_sold' => 1, 'total_revenue' => 8000.00, 'total_profit' => 4000.00, 'created_at' => '2026-02-01 23:59:00', 'updated_at' => '2026-02-01 23:59:00'],
            ['id' => 3, 'date' => '2026-02-02', 'product_id' => 8, 'shop_id' => 3, 'category_id' => 3, 'total_qty_sold' => 1, 'total_revenue' => 150000.00, 'total_profit' => 60000.00, 'created_at' => '2026-02-02 23:59:00', 'updated_at' => '2026-02-02 23:59:00'],
            ['id' => 4, 'date' => '2026-02-02', 'product_id' => 9, 'shop_id' => 3, 'category_id' => 3, 'total_qty_sold' => 2, 'total_revenue' => 220000.00, 'total_profit' => 90000.00, 'created_at' => '2026-02-02 23:59:00', 'updated_at' => '2026-02-02 23:59:00'],
            ['id' => 5, 'date' => '2026-02-03', 'product_id' => 5, 'shop_id' => 2, 'category_id' => 4, 'total_qty_sold' => 1, 'total_revenue' => 120000.00, 'total_profit' => 50000.00, 'created_at' => '2026-02-03 23:59:00', 'updated_at' => '2026-02-03 23:59:00'],
            ['id' => 6, 'date' => '2026-02-04', 'product_id' => 11, 'shop_id' => 4, 'category_id' => 6, 'total_qty_sold' => 2, 'total_revenue' => 120000.00, 'total_profit' => 70000.00, 'created_at' => '2026-02-04 23:59:00', 'updated_at' => '2026-02-04 23:59:00'],
            ['id' => 7, 'date' => '2026-02-05', 'product_id' => 2, 'shop_id' => 1, 'category_id' => 1, 'total_qty_sold' => 1, 'total_revenue' => 20000.00, 'total_profit' => 8000.00, 'created_at' => '2026-02-05 23:59:00', 'updated_at' => '2026-02-05 23:59:00'],
            ['id' => 8, 'date' => '2026-02-07', 'product_id' => 10, 'shop_id' => 3, 'category_id' => 3, 'total_qty_sold' => 1, 'total_revenue' => 95000.00, 'total_profit' => 40000.00, 'created_at' => '2026-02-07 23:59:00', 'updated_at' => '2026-02-07 23:59:00'],
            ['id' => 9, 'date' => '2026-02-08', 'product_id' => 2, 'shop_id' => 1, 'category_id' => 1, 'total_qty_sold' => 1, 'total_revenue' => 20000.00, 'total_profit' => 8000.00, 'created_at' => '2026-02-08 23:59:00', 'updated_at' => '2026-02-08 23:59:00'],
            ['id' => 10, 'date' => '2026-02-08', 'product_id' => 3, 'shop_id' => 1, 'category_id' => 2, 'total_qty_sold' => 1, 'total_revenue' => 8000.00, 'total_profit' => 4000.00, 'created_at' => '2026-02-08 23:59:00', 'updated_at' => '2026-02-08 23:59:00'],
        ];

        foreach ($sales as $sale) {
            DailyProductSales::updateOrCreate(['id' => $sale['id']], $sale);
        }
    }

    private function seedEmailVerificationCodes(): void
    {
        $codes = [
            ['id' => 1, 'user_id' => 5, 'code' => '123456', 'expires_at' => '2026-01-05 01:00:00', 'is_used' => 0, 'created_at' => '2026-01-05 00:00:00', 'updated_at' => '2026-01-05 00:00:00'],
            ['id' => 2, 'user_id' => 6, 'code' => '654321', 'expires_at' => '2026-01-06 01:00:00', 'is_used' => 0, 'created_at' => '2026-01-06 00:00:00', 'updated_at' => '2026-01-06 00:00:00'],
            ['id' => 3, 'user_id' => 7, 'code' => '111222', 'expires_at' => '2026-01-07 01:00:00', 'is_used' => 0, 'created_at' => '2026-01-07 00:00:00', 'updated_at' => '2026-01-07 00:00:00'],
        ];

        foreach ($codes as $code) {
            DB::table('email_verification_codes')->updateOrInsert(['id' => $code['id']], $code);
        }
    }

    private function seedPersonalAccessTokens(): void
    {
        $tokens = [
            ['id' => 1, 'tokenable_type' => 'App\Models\User', 'tokenable_id' => 5, 'name' => 'web-token', 'token' => '1|abcdef1234567890abcdef1234567890abcdef12', 'abilities' => '["*"]', 'last_used_at' => '2026-02-08 15:00:00', 'expires_at' => null, 'created_at' => '2026-02-01 00:00:00', 'updated_at' => '2026-02-08 15:00:00'],
            ['id' => 2, 'tokenable_type' => 'App\Models\User', 'tokenable_id' => 6, 'name' => 'web-token', 'token' => '2|abcdef1234567890abcdef1234567890abcdef34', 'abilities' => '["*"]', 'last_used_at' => '2026-02-07 08:00:00', 'expires_at' => null, 'created_at' => '2026-02-02 00:00:00', 'updated_at' => '2026-02-07 08:00:00'],
        ];

        foreach ($tokens as $token) {
            DB::table('personal_access_tokens')->updateOrInsert(['id' => $token['id']], $token);
        }
    }

    private function seedCodLocations(): void
    {
        $locations = [
            ['id' => 1, 'user_id' => 5, 'name' => 'Kabupaten A - Pusat', 'address' => 'Jl. Merdeka No.1, Kabupaten A', 'latitude' => -6.20000000, 'longitude' => 106.81666600, 'is_default' => 1, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'user_id' => 5, 'name' => 'Kabupaten A - Timur', 'address' => 'Jl. Timur No.12, Kabupaten A', 'latitude' => -6.20100000, 'longitude' => 106.82000000, 'is_default' => 0, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 3, 'user_id' => 6, 'name' => 'Kabupaten A - Barat', 'address' => 'Jl. Barat No.34, Kabupaten A', 'latitude' => -6.19900000, 'longitude' => 106.81000000, 'is_default' => 0, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 4, 'user_id' => 7, 'name' => 'Kabupaten B', 'address' => 'Jl. Utama No.56, Kabupaten B', 'latitude' => -6.30000000, 'longitude' => 106.90000000, 'is_default' => 1, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
        ];

        foreach ($locations as $location) {
            CodLocation::updateOrCreate(['id' => $location['id']], $location);
        }
    }

    private function seedPaymentSettings(): void
    {
        $settings = [
            ['id' => 1, 'bank_name' => 'Bank ABC', 'account_number' => '1234567890', 'account_holder_name' => 'Admin Kabita', 'is_active' => 1, 'deleted_at' => null, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'bank_name' => 'Bank XYZ', 'account_number' => '0987654321', 'account_holder_name' => 'Admin Kabita', 'is_active' => 1, 'deleted_at' => null, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
        ];

        foreach ($settings as $setting) {
            PaymentSetting::updateOrCreate(['id' => $setting['id']], $setting);
        }
    }
}
