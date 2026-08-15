-- ============================================================
-- Kabita E-Commerce Backend Seed Data
-- Database: MySQL / MariaDB
-- Engine: InnoDB
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- Purpose: Full sample dataset for development & demo
-- ============================================================

-- ============================================================
-- 1. USERS
-- ============================================================
REPLACE INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `address`, `remember_token`, `status`, `proof_image`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin Kabita', 'admin@kabita.test', '2026-01-01 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'admin', '081234500001', 'Jl. Merdeka No.1, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Siti Aminah', 'siti@umkm.test', '2026-01-02 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'seller', '081234500002', 'Jl. Pasar No.2, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-02 00:00:00', '2026-01-02 00:00:00'),
(3, 'Budi Santoso', 'budi@umkm.test', '2026-01-03 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'seller', '081234500003', 'Jl. Melati No.3, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-03 00:00:00', '2026-01-03 00:00:00'),
(4, 'Rina Wati', 'rina@umkm.test', '2026-01-04 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'seller', '081234500004', 'Jl. Mawar No.4, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-04 00:00:00', '2026-01-04 00:00:00'),
(5, 'Andi Wijaya', 'andi@buyer.test', '2026-01-05 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'buyer', '081234500005', 'Jl. Kenari No.5, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-05 00:00:00', '2026-01-05 00:00:00'),
(6, 'Dewi Lestari', 'dewi@buyer.test', '2026-01-06 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'buyer', '081234500006', 'Jl. Cempaka No.6, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-06 00:00:00', '2026-01-06 00:00:00'),
(7, 'Fajar Nugroho', 'fajar@buyer.test', '2026-01-07 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'buyer', '081234500007', 'Jl. Anggrek No.7, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-07 00:00:00', '2026-01-07 00:00:00'),
(8, 'Maya Putri', 'maya@buyer.test', '2026-01-08 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'buyer', '081234500008', 'Jl. Dahlia No.8, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-08 00:00:00', '2026-01-08 00:00:00'),
(9, 'Rahmat Hidayat', 'rahmat@buyer.test', '2026-01-09 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'buyer', '081234500009', 'Jl. Flamboyan No.9, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-09 00:00:00', '2026-01-09 00:00:00'),
(10, 'Lina Marlina', 'lina@buyer.test', '2026-01-10 00:00:00', '$2y$12$LQv3c1yqBWVHxkd5L5k1UO6Zx8Y7w9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6', 'buyer', '081234500010', 'Jl. Sakura No.10, Kabupaten A', NULL, 'active', NULL, NULL, NULL, '2026-01-10 00:00:00', '2026-01-10 00:00:00');

-- ============================================================
-- 2. SHOPS
-- ============================================================
REPLACE INTO `shops` (`id`, `seller_id`, `name`, `slug`, `description`, `logo`, `status`, `verified_by`, `verified_at`, `rejection_reason`, `phone`, `address`, `banner`, `created_at`, `updated_at`) VALUES
(1, 2, 'Warung Makan Siti', 'warung-makan-siti', 'Warung makan rumahan dengan masakan tradisional khas Kabupaten A.', 'https://via.placeholder.com/150/FF6B6B/FFFFFF?text=Warung+Makan+Siti', 'verified', 1, '2026-01-02 08:00:00', NULL, '081234500002', 'Jl. Pasar No.2, Kabupaten A', NULL, '2026-01-02 00:00:00', '2026-01-02 08:00:00'),
(2, 3, 'Budi Craft', 'budi-craft', 'Produk kerajinan tangan dari bahan daur ulang dan kayu.', 'https://via.placeholder.com/150/4ECDC4/FFFFFF?text=Budi+Craft', 'verified', 1, '2026-01-03 08:00:00', NULL, '081234500003', 'Jl. Melati No.3, Kabupaten A', NULL, '2026-01-03 00:00:00', '2026-01-03 08:00:00'),
(3, 4, 'Rina Fashion', 'rina-fashion', 'Toko fashion lokal dengan desain simple dan elegan.', 'https://via.placeholder.com/150/FFE66D/333333?text=Rina+Fashion', 'verified', 1, '2026-01-04 08:00:00', NULL, '081234500004', 'Jl. Mawar No.4, Kabupaten A', NULL, '2026-01-04 00:00:00', '2026-01-04 08:00:00'),
(4, 2, 'Siti Snack Box', 'siti-snack-box', 'Snack box untuk acara dan oleh-oleh khas daerah.', 'https://via.placeholder.com/150/95E1D3/333333?text=Siti+Snack+Box', 'verified', 1, '2026-01-05 08:00:00', NULL, '081234500002', 'Jl. Pasar No.2, Kabupaten A', NULL, '2026-01-05 00:00:00', '2026-01-05 08:00:00');

-- ============================================================
-- 3. CATEGORIES
-- ============================================================
REPLACE INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Makanan', 'makanan', '🍴', 'Kategori produk makanan dan camilan.', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Minuman', 'minuman', '☕', 'Kategori produk minuman segar dan kemasan.', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Fashion', 'fashion', '👕', 'Kategori produk fashion dan pakaian.', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Kerajinan', 'kerajinan', '🔨', 'Kategori produk kerajinan tangan.', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Kecantikan', 'kecantikan', '✨', 'Kategori produk kecantikan dan perawatan.', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Oleh-oleh', 'oleh-oleh', '🎁', 'Kategori produk oleh-oleh khas daerah.', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- ============================================================
-- 4. PRODUCTS
-- ============================================================
REPLACE INTO `products` (`id`, `shop_id`, `category_id`, `name`, `slug`, `description`, `price`, `cost_price`, `stock`, `status`, `weight`, `verified_at`, `rejection_reason`, `deleted_at`, `created_at`, `updated_at`) VALUES
-- Shop 1: Warung Makan Siti
(1, 1, 1, 'Nasi Goreng Spesial', 'nasi-goreng-spesial', 'Nasi goreng dengan telur, ayam, dan kerupuk.', 25000.00, 15000.00, 50, 'approved', 500.00, '2026-01-02 09:00:00', NULL, NULL, '2026-01-02 00:00:00', '2026-01-02 09:00:00'),
(2, 1, 1, 'Mie Goreng Jawa', 'mie-goreng-jawa', 'Mie goreng khas Jawa dengan sayuran segar.', 20000.00, 12000.00, 40, 'approved', 400.00, '2026-01-02 09:00:00', NULL, NULL, '2026-01-02 00:00:00', '2026-01-02 09:00:00'),
(3, 1, 2, 'Es Jeruk Peras', 'es-jeruk-peras', 'Es jeruk segar tanpa pengawet.', 8000.00, 4000.00, 100, 'approved', 300.00, '2026-01-02 09:00:00', NULL, NULL, '2026-01-02 00:00:00', '2026-01-02 09:00:00'),
(4, 1, 6, 'Snack Box Nasi Tumpeng Mini', 'snack-box-nasi-tumpeng-mini', 'Snack box nasi tumpeng mini untuk acara.', 75000.00, 50000.00, 20, 'approved', 1200.00, '2026-01-05 09:00:00', NULL, NULL, '2026-01-05 00:00:00', '2026-01-05 09:00:00'),

-- Shop 2: Budi Craft
(5, 2, 4, 'Tas Anyaman Rotan', 'tas-anyaman-rotan', 'Tas anyaman rotan handmade.', 120000.00, 70000.00, 15, 'approved', 800.00, '2026-01-03 09:00:00', NULL, NULL, '2026-01-03 00:00:00', '2026-01-03 09:00:00'),
(6, 2, 4, 'Frame Foto Kayu Jati', 'frame-foto-kayu-jati', 'Frame foto dari kayu jati solid.', 85000.00, 50000.00, 25, 'approved', 600.00, '2026-01-03 09:00:00', NULL, NULL, '2026-01-03 00:00:00', '2026-01-03 09:00:00'),
(7, 2, 4, 'Cindera Mata Wooden', 'cindera-mata-wooden', 'Cindera mata dari bahan woodcraft.', 65000.00, 40000.00, 30, 'approved', 300.00, '2026-01-03 09:00:00', NULL, NULL, '2026-01-03 00:00:00', '2026-01-03 09:00:00'),

-- Shop 3: Rina Fashion
(8, 3, 3, 'Kemeja Batik Wanita', 'kemeja-batik-wanita', 'Kemeja batik modern untuk wanita.', 150000.00, 90000.00, 20, 'approved', 250.00, '2026-01-04 09:00:00', NULL, NULL, '2026-01-04 00:00:00', '2026-01-04 09:00:00'),
(9, 3, 3, 'Rok Plisket Premium', 'rok-plisket-premium', 'Rok plisket bahan premium.', 110000.00, 65000.00, 25, 'approved', 200.00, '2026-01-04 09:00:00', NULL, NULL, '2026-01-04 00:00:00', '2026-01-04 09:00:00'),
(10, 3, 3, 'Tas Selempang Kanvas', 'tas-selempang-kanvas', 'Tas selempang kanvas lokal.', 95000.00, 55000.00, 18, 'approved', 450.00, '2026-01-04 09:00:00', NULL, NULL, '2026-01-04 00:00:00', '2026-01-04 09:00:00'),

-- Shop 4: Siti Snack Box
(11, 4, 6, 'Snack Box Kue Kering', 'snack-box-kue-kering', 'Kue kering dalam snack box.', 60000.00, 35000.00, 30, 'approved', 700.00, '2026-01-05 09:00:00', NULL, NULL, '2026-01-05 00:00:00', '2026-01-05 09:00:00'),
(12, 4, 6, 'Dodol Garut Premium', 'dodol-garut-premium', 'Dodol garut khas dengan kemasan premium.', 45000.00, 25000.00, 35, 'approved', 500.00, '2026-01-05 09:00:00', NULL, NULL, '2026-01-05 00:00:00', '2026-01-05 09:00:00');

-- ============================================================
-- 4.1 PRODUCT IMAGES
-- ============================================================
REPLACE INTO `product_images` (`id`, `product_id`, `image_path`, `order_column`, `created_at`, `updated_at`) VALUES
-- Shop 1: Warung Makan Siti
(1, 1, 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Nasi+Goreng+Spesial', 1, '2026-01-02 00:00:00', '2026-01-02 00:00:00'),
(2, 1, 'https://via.placeholder.com/400x300/FF8E8E/FFFFFF?text=Nasi+Goreng+2', 2, '2026-01-02 00:00:00', '2026-01-02 00:00:00'),
(3, 2, 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=Mie+Goreng+Jawa', 1, '2026-01-02 00:00:00', '2026-01-02 00:00:00'),
(4, 3, 'https://via.placeholder.com/400x300/95E1D3/333333?text=Es+Jeruk+Peras', 1, '2026-01-02 00:00:00', '2026-01-02 00:00:00'),
(5, 4, 'https://via.placeholder.com/400x300/FFE66D/333333?text=Snack+Box+Nasi+Tumpeng+Mini', 1, '2026-01-02 00:00:00', '2026-01-02 00:00:00'),
-- Shop 2: Budi Craft
(6, 5, 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Tas+Anyaman+Rotan', 1, '2026-01-03 00:00:00', '2026-01-03 00:00:00'),
(7, 6, 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=Frame+Foto+Kayu+Jati', 1, '2026-01-03 00:00:00', '2026-01-03 00:00:00'),
(8, 7, 'https://via.placeholder.com/400x300/95E1D3/333333?text=Cindera+Mata+Wooden', 1, '2026-01-03 00:00:00', '2026-01-03 00:00:00'),
-- Shop 3: Rina Fashion
(9, 8, 'https://via.placeholder.com/400x300/FFE66D/333333?text=Kemeja+Batik+Wanita', 1, '2026-01-04 00:00:00', '2026-01-04 00:00:00'),
(10, 9, 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Rok+Plisket+Premium', 1, '2026-01-04 00:00:00', '2026-01-04 00:00:00'),
(11, 10, 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=Tas+Selempang+Kanvas', 1, '2026-01-04 00:00:00', '2026-01-04 00:00:00'),
-- Shop 4: Siti Snack Box
(12, 11, 'https://via.placeholder.com/400x300/95E1D3/333333?text=Snack+Box+Kue+Kering', 1, '2026-01-05 00:00:00', '2026-01-05 00:00:00'),
(13, 12, 'https://via.placeholder.com/400x300/FFE66D/333333?text=Dodol+Garut+Premium', 1, '2026-01-05 00:00:00', '2026-01-05 00:00:00');

-- ============================================================
-- 5. CARTS
-- ============================================================
REPLACE INTO `carts` (`id`, `buyer_id`, `created_at`, `updated_at`) VALUES
(1, 5, '2026-02-01 00:00:00', '2026-02-05 00:00:00'),
(2, 6, '2026-02-02 00:00:00', '2026-02-06 00:00:00'),
(3, 7, '2026-02-03 00:00:00', '2026-02-07 00:00:00'),
(4, 8, '2026-02-04 00:00:00', '2026-02-08 00:00:00'),
(5, 9, '2026-02-05 00:00:00', '2026-02-09 00:00:00'),
(6, 10, '2026-02-06 00:00:00', '2026-02-10 00:00:00');

-- ============================================================
-- 6. CART ITEMS
-- ============================================================
REPLACE INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '2026-02-01 00:00:00', '2026-02-05 00:00:00'),
(2, 1, 3, 1, '2026-02-01 00:00:00', '2026-02-05 00:00:00'),
(3, 2, 8, 1, '2026-02-02 00:00:00', '2026-02-06 00:00:00'),
(4, 2, 9, 2, '2026-02-02 00:00:00', '2026-02-06 00:00:00'),
(5, 3, 5, 1, '2026-02-03 00:00:00', '2026-02-07 00:00:00'),
(6, 4, 11, 2, '2026-02-04 00:00:00', '2026-02-08 00:00:00'),
(7, 5, 2, 1, '2026-02-05 00:00:00', '2026-02-09 00:00:00'),
(8, 6, 6, 1, '2026-02-06 00:00:00', '2026-02-10 00:00:00');

-- ============================================================
-- 7. ORDERS
-- ============================================================
REPLACE INTO `orders` (`id`, `order_number`, `buyer_id`, `shop_id`, `subtotal`, `shipping_cost`, `total_amount`, `shipping_method`, `payment_method`, `status`, `shipping_address`, `tracking_number`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20260201-001', 5, 1, 58000.00, 10000.00, 68000.00, 'kurir', 'transfer', 'delivered', 'Jl. Kenari No.5, Kabupaten A', 'TRK001', 'Kirim sebelum jam 12.', '2026-02-01 10:00:00', '2026-02-03 14:00:00'),
(2, 'ORD-20260202-002', 6, 3, 330000.00, 15000.00, 345000.00, 'kurir', 'transfer', 'shipped', 'Jl. Cempaka No.6, Kabupaten A', 'TRK002', 'Pack dengan aman.', '2026-02-02 11:00:00', '2026-02-05 09:00:00'),
(3, 'ORD-20260203-003', 7, 2, 120000.00, 12000.00, 132000.00, 'cod', 'cod', 'processing', 'Jl. Anggrek No.7, Kabupaten A', NULL, 'COD sesuai.', '2026-02-03 09:30:00', '2026-02-04 08:00:00'),
(4, 'ORD-20260204-004', 8, 4, 120000.00, 10000.00, 130000.00, 'kurir', 'transfer', 'pending', 'Jl. Dahlia No.8, Kabupaten A', NULL, 'Segera dikirim.', '2026-02-04 13:00:00', '2026-02-04 13:00:00'),
(5, 'ORD-20260205-005', 9, 1, 20000.00, 8000.00, 28000.00, 'cod', 'cod', 'pending', 'Jl. Flamboyan No.9, Kabupaten A', NULL, NULL, '2026-02-05 16:00:00', '2026-02-05 16:00:00'),
(6, 'ORD-20260206-006', 10, 2, 85000.00, 11000.00, 96000.00, 'kurir', 'transfer', 'cancelled', 'Jl. Sakura No.10, Kabupaten A', NULL, 'Dibatalkan pembeli.', '2026-02-06 10:00:00', '2026-02-06 12:00:00'),
(7, 'ORD-20260207-007', 5, 3, 95000.00, 13000.00, 108000.00, 'kurir', 'transfer', 'delivered', 'Jl. Kenari No.5, Kabupaten A', 'TRK003', NULL, '2026-02-07 08:00:00', '2026-02-09 10:00:00'),
(8, 'ORD-20260208-008', 6, 1, 16000.00, 9000.00, 25000.00, 'cod', 'cod', 'processing', 'Jl. Cempaka No.6, Kabupaten A', NULL, NULL, '2026-02-08 15:00:00', '2026-02-09 07:00:00');

-- ============================================================
-- 8. ORDER ITEMS
-- ============================================================
REPLACE INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_snapshot`, `cost_snapshot`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 25000.00, 15000.00, '2026-02-01 10:00:00', '2026-02-01 10:00:00'),
(2, 1, 3, 1, 8000.00, 4000.00, '2026-02-01 10:00:00', '2026-02-01 10:00:00'),
(3, 2, 8, 1, 150000.00, 90000.00, '2026-02-02 11:00:00', '2026-02-02 11:00:00'),
(4, 2, 9, 2, 110000.00, 65000.00, '2026-02-02 11:00:00', '2026-02-02 11:00:00'),
(5, 3, 5, 1, 120000.00, 70000.00, '2026-02-03 09:30:00', '2026-02-03 09:30:00'),
(6, 4, 11, 2, 60000.00, 35000.00, '2026-02-04 13:00:00', '2026-02-04 13:00:00'),
(7, 5, 2, 1, 20000.00, 12000.00, '2026-02-05 16:00:00', '2026-02-05 16:00:00'),
(8, 6, 6, 1, 85000.00, 50000.00, '2026-02-06 10:00:00', '2026-02-06 10:00:00'),
(9, 7, 10, 1, 95000.00, 55000.00, '2026-02-07 08:00:00', '2026-02-07 08:00:00'),
(10, 8, 2, 1, 20000.00, 12000.00, '2026-02-08 15:00:00', '2026-02-08 15:00:00'),
(11, 8, 3, 1, 8000.00, 4000.00, '2026-02-08 15:00:00', '2026-02-08 15:00:00');

-- ============================================================
-- 9. PAYMENTS
-- ============================================================
REPLACE INTO `payments` (`id`, `order_id`, `amount`, `proof_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 68000.00, 'payments/ord-20260201-001-proof.jpg', 'verified', '2026-02-01 10:30:00', '2026-02-02 08:00:00'),
(2, 2, 345000.00, 'payments/ord-20260202-002-proof.jpg', 'verified', '2026-02-02 12:00:00', '2026-02-04 09:00:00'),
(3, 4, 130000.00, NULL, 'pending', '2026-02-04 13:30:00', '2026-02-04 13:30:00'),
(4, 7, 108000.00, 'payments/ord-20260207-007-proof.jpg', 'verified', '2026-02-07 09:00:00', '2026-02-08 08:00:00'),
(5, 8, 25000.00, NULL, 'pending', '2026-02-08 15:30:00', '2026-02-08 15:30:00');

-- ============================================================
-- 10. DAILY PRODUCT SALES
-- ============================================================
REPLACE INTO `daily_product_sales` (`id`, `date`, `product_id`, `shop_id`, `category_id`, `total_qty_sold`, `total_revenue`, `total_profit`, `created_at`, `updated_at`) VALUES
(1, '2026-02-01', 1, 1, 1, 2, 50000.00, 20000.00, '2026-02-01 23:59:00', '2026-02-01 23:59:00'),
(2, '2026-02-01', 3, 1, 2, 1, 8000.00, 4000.00, '2026-02-01 23:59:00', '2026-02-01 23:59:00'),
(3, '2026-02-02', 8, 3, 3, 1, 150000.00, 60000.00, '2026-02-02 23:59:00', '2026-02-02 23:59:00'),
(4, '2026-02-02', 9, 3, 3, 2, 220000.00, 90000.00, '2026-02-02 23:59:00', '2026-02-02 23:59:00'),
(5, '2026-02-03', 5, 2, 4, 1, 120000.00, 50000.00, '2026-02-03 23:59:00', '2026-02-03 23:59:00'),
(6, '2026-02-04', 11, 4, 6, 2, 120000.00, 70000.00, '2026-02-04 23:59:00', '2026-02-04 23:59:00'),
(7, '2026-02-05', 2, 1, 1, 1, 20000.00, 8000.00, '2026-02-05 23:59:00', '2026-02-05 23:59:00'),
(8, '2026-02-07', 10, 3, 3, 1, 95000.00, 40000.00, '2026-02-07 23:59:00', '2026-02-07 23:59:00'),
(9, '2026-02-08', 2, 1, 1, 1, 20000.00, 8000.00, '2026-02-08 23:59:00', '2026-02-08 23:59:00'),
(10, '2026-02-08', 3, 1, 2, 1, 8000.00, 4000.00, '2026-02-08 23:59:00', '2026-02-08 23:59:00');

-- ============================================================
-- 11. EMAIL VERIFICATION CODES
-- ============================================================
REPLACE INTO `email_verification_codes` (`id`, `user_id`, `code`, `expires_at`, `is_used`, `created_at`, `updated_at`) VALUES
(1, 5, '123456', '2026-01-05 01:00:00', 0, '2026-01-05 00:00:00', '2026-01-05 00:00:00'),
(2, 6, '654321', '2026-01-06 01:00:00', 0, '2026-01-06 00:00:00', '2026-01-06 00:00:00'),
(3, 7, '111222', '2026-01-07 01:00:00', 0, '2026-01-07 00:00:00', '2026-01-07 00:00:00');

-- ============================================================
-- 12. PERSONAL ACCESS TOKENS
-- ============================================================
REPLACE INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\Models\User', 5, 'web-token', '1|abcdef1234567890abcdef1234567890abcdef12', '["*"]', '2026-02-08 15:00:00', NULL, '2026-02-01 00:00:00', '2026-02-08 15:00:00'),
(2, 'App\Models\User', 6, 'web-token', '2|abcdef1234567890abcdef1234567890abcdef34', '["*"]', '2026-02-07 08:00:00', NULL, '2026-02-02 00:00:00', '2026-02-07 08:00:00');

-- ============================================================
-- 13. CODELOCATIONS
-- ============================================================
REPLACE INTO `cod_locations` (`id`, `user_id`, `name`, `address`, `latitude`, `longitude`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 5, 'Kabupaten A - Pusat', 'Jl. Merdeka No.1, Kabupaten A', -6.20000000, 106.81666600, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 5, 'Kabupaten A - Timur', 'Jl. Timur No.12, Kabupaten A', -6.20100000, 106.82000000, 0, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 6, 'Kabupaten A - Barat', 'Jl. Barat No.34, Kabupaten A', -6.19900000, 106.81000000, 0, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 7, 'Kabupaten B', 'Jl. Utama No.56, Kabupaten B', -6.30000000, 106.90000000, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- ============================================================
-- 14. PAYMENT SETTINGS
-- ============================================================
REPLACE INTO `payment_settings` (`id`, `bank_name`, `account_number`, `account_holder_name`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Bank ABC', '1234567890', 'Admin Kabita', 1, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Bank XYZ', '0987654321', 'Admin Kabita', 1, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- ============================================================
-- 15. JOBS (queue table placeholder)
-- ============================================================
-- Table jobs is managed by Laravel queue worker.
-- No manual seed data required for normal development.

-- ============================================================
-- END OF SEED DATA
-- ============================================================