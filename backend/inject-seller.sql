-- ============================================================
-- Inject Seller: seller@kabita.my.id
-- Database: MySQL / MariaDB
-- Password : password
-- ============================================================

SET @seller_email = 'seller@kabita.my.id';
SET @seller_pass  = '$2y$12$P.V/xXWts.sIwfh5Rjz78u5uAIMGsYHOnWdsuV.QgtHGPbPgRPC6y';
SET @now          = NOW();

-- 1. Insert or update seller user
INSERT INTO `users` (
    `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `address`,
    `gender`, `date_of_birth`, `status`,
    `created_at`, `updated_at`
) VALUES (
    'Seller Kabita', @seller_email, @now, @seller_pass, 'seller', '081234500000', 'Jl. Kabita No.1, Kabupaten A',
    'male', '1995-06-15', 'active',
    @now, @now
)
ON DUPLICATE KEY UPDATE
    `role` = 'seller',
    `status` = 'active',
    `email_verified_at` = IFNULL(`email_verified_at`, @now),
    `updated_at` = @now;

-- Ambil ID seller
SET @seller_id = (SELECT `id` FROM `users` WHERE `email` = @seller_email LIMIT 1);

-- 2. Insert or update toko milik seller
INSERT INTO `shops` (
    `seller_id`, `name`, `slug`, `description`, `status`,
    `verified_at`, `phone`, `address`,
    `created_at`, `updated_at`
) VALUES (
    @seller_id, 'Toko Kabita', 'toko-kabita',
    'Toko resmi Kabita untuk keperluan testing dan demo.', 'verified',
    @now, '081234500000', 'Jl. Kabita No.1, Kabupaten A',
    @now, @now
)
ON DUPLICATE KEY UPDATE
    `status` = 'verified',
    `verified_at` = IFNULL(`verified_at`, @now),
    `updated_at` = @now;