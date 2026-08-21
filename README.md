# Kabita E-Commerce

Marketplace ini memakai Vue 3 sebagai frontend dan Laravel 12 sebagai backend API.
Semua data bisnis mengalir melalui API Laravel; frontend tidak mengakses database secara
langsung. Database lokal default adalah SQLite sehingga proyek dapat dijalankan tanpa
MariaDB/MySQL.

## Menjalankan backend dan frontend

Prasyarat: PHP 8.3+, Composer, Node.js 22+, dan ekstensi PHP `pdo_sqlite`.

Terminal 1 — backend:

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

API tersedia di `http://localhost:8000/api/v1`. Cek koneksi dengan:

```sh
curl http://localhost:8000/api/v1/health
```

Terminal 2 — frontend:

```sh
cd frontend
npm install
cp .env.example .env
npm run dev
```

Frontend membaca `VITE_API_BASE_URL` dari `.env` (default:
`http://localhost:8000/api/v1`). Jangan membuat query database dari Vue; buat endpoint,
request validation, resource/response, service frontend, lalu hubungkan ke view/store.

## Kontrak integrasi untuk fitur baru

1. Tambahkan route di `backend/routes/api.php` di bawah prefix `v1`.
2. Implementasikan controller, FormRequest, Resource, dan test backend.
3. Tambahkan service typed di `frontend/src/services` yang memakai `apiClient`.
4. Hubungkan service ke Pinia store atau view dan tangani loading/error state.
5. Perbarui dokumentasi kontrak jika format response berubah.

Autentikasi memakai Sanctum Bearer token. `frontend/src/services/apiClient.ts` otomatis
menambahkan token dan menangani respons 401. CORS lokal dikonfigurasi melalui
`CORS_ALLOWED_ORIGINS` di backend `.env`.

## Validasi

```sh
cd backend && php artisan test
cd ../frontend && npm run lint && npm run build
```


## Panduan demo end-to-end

### Menjalankan project

Prasyarat: PHP 8.3+, Composer, Node.js 22+/24, npm, MariaDB aktif pada port 3306, dan ekstensi pdo_mysql.

Buat database MariaDB:

    CREATE DATABASE kabita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

Setup pertama kali:

    cd backend
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan storage:link

Atur backend/.env:

    DB_CONNECTION=mariadb
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=kabita
    DB_USERNAME=kabita
    DB_PASSWORD=password_database
    CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
    QUEUE_CONNECTION=database

Setup frontend:

    cd ../frontend
    npm install
    cp .env.example .env

Atur frontend/.env:

    VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1

Jalankan dari root:

    chmod +x run.sh
    ./run.sh

Gunakan DEMO_SEED=0 ./run.sh jika tidak ingin seed ulang. Manual backend: cd backend, php artisan migrate --force, php artisan db:seed --force, php artisan optimize:clear, php artisan serve --host=127.0.0.1 --port=8000. Terminal lain: cd frontend, npm run dev -- --host=127.0.0.1 --port=5173.

URL: Frontend http://127.0.0.1:5173, API http://127.0.0.1:8000/api/v1, health http://127.0.0.1:8000/api/v1/health.

Akun demo: admin@kabita.test / password, seller@kabita.test / password, buyer@kabita.test / password.

### Flow Buyer

1. Buka /produk, cari produk, atau buka kategori seperti /kategori/kerajinan.
2. Buka detail produk dan tambahkan produk ke cart.
3. Pilih item pada /cart, ubah quantity, lalu checkout.
4. Pilih alamat, pengiriman, dan pembayaran.
5. Untuk transfer, upload bukti pembayaran.
6. Pantau order pada /profile/orders.
7. Setelah seller mengirim order, klik Pesanan Diterima / Selesaikan pada detail order.
8. Hubungi Penjual membuka WhatsApp jika nomor toko tersedia. Bantuan Pesanan membuka dialog bantuan.

Endpoint buyer penting: GET /public/products, GET /categories/{slug}, GET /cart, POST /cart/items, PUT /cart/items/{id}, DELETE /cart/items/{id}, POST /checkout, POST /payments/{payment}/upload, GET /orders, GET /orders/{order}, PATCH /orders/{order}/confirm.

### Flow Admin

1. Login admin.
2. Dashboard menampilkan statistik platform.
3. Kelola user dengan search, filter, pagination, suspend, dan activate.
4. Kelola kategori dengan create, edit, dan delete.
5. Buka /admin/verifikasi. Tab Pembayaran mengambil /payments/pending.
6. Lihat bukti transfer, verifikasi atau tolak pembayaran.
7. Tab Produk digunakan untuk approve/reject produk seller.
8. Verifikasi toko seller, pantau transaksi, dan lihat analitik.

Role admin adalah satu-satunya role yang memverifikasi pembayaran transfer.

### Flow Seller

1. Login seller.
2. Kelola profil toko pada /seller/profil-toko.
3. CRUD produk dan stok.
4. Pantau order pada /seller/pesanan.
5. Buka detail order untuk melihat buyer dan bukti transfer.
6. Setelah admin memverifikasi pembayaran, proses order.
7. Kirim order dan masukkan nomor resi.
8. Buyer mengonfirmasi penerimaan sehingga status menjadi delivered.

Status order: pending -> processing -> shipped -> delivered.

Admin memverifikasi pembayaran, seller memproses dan mengirim, buyer menyelesaikan order.

### Storage, CORS, dan validasi

Jalankan sekali untuk file upload: cd backend && php artisan storage:link.

Jika CORS bermasalah, pastikan CORS_ALLOWED_ORIGINS memuat localhost:5173 dan 127.0.0.1:5173, lalu jalankan php artisan optimize:clear.

Validasi: cd backend && php artisan test; cd ../frontend && npm run build-only; npm run type-check.

Smoke test: health API sukses, login tiga role, buyer checkout, payment pending muncul di /admin/verifikasi, admin verifikasi, seller process/ship, dan buyer confirm received.

### Struktur integrasi fitur

Backend memakai routes/api.php, Controller, FormRequest, Resource, Model, migration, seeder, dan Feature Test. Frontend memakai service typed pada frontend/src/services, Pinia store, route guard, dan view. Semua fitur baru wajib memiliki loading state, error state, empty state, validasi, dan tidak boleh memakai data bisnis hardcode/mock.
