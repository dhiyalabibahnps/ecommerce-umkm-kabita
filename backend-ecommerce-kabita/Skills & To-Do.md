Baik, ini adalah langkah yang sangat tepat. Sebagai Senior Developer dan Pembimbing Skripsi Anda, saya sangat menyarankan untuk mendokumentasikan alur kerja ini. Ini tidak hanya membantu Anda tetap fokus saat coding di luar chat ini, tetapi juga bisa menjadi bahan pendukung untuk **Bab 3 (Metodologi)** dan **Bab 4 (Implementasi)** di skripsi Anda.

Berikut adalah draf untuk dua file penting tersebut: `SKILLS.md` (sebagai _cheat sheet_ & referensi arsitektur) dan `TODO.md` (sebagai _checklist_ progres Waterfall).

Silakan buat kedua file ini di root directory project Anda (bisa di folder `backend` atau di root monorepo jika Anda menggabungkannya).

---

### 1. File: `SKILLS.md`

File ini berfungsi sebagai dokumentasi teknis, konvensi penamaan, dan arsitektur sistem agar Anda tidak bingung saat kembali coding.

````markdown
# SKILLS & DEVELOPMENT GUIDE - KABITA E-COMMERCE

## 1. Tech Stack & Environment

- **Frontend:** Vue.js 3 (Composition API, `<script setup>`), TypeScript (Strict Mode), PrimeVue (UI Components), Pinia (State Management), Axios (HTTP Client), Vite.
- **Backend:** PHP 8.5, Laravel 13 (RESTful API), Laravel Sanctum (Auth).
- **Database:** MariaDB (InnoDB Engine).
- **Methodology:** Waterfall (Requirement -> Design -> Implementation -> Testing -> Maintenance).

## 2. Arsitektur & Desain Database (Penting untuk Skripsi)

- **Strict Typing:** Menggunakan PHP 8.1+ Backed Enums (`App\Enums\*`) untuk kolom status/role. Casting dilakukan di Model via `protected function casts(): array`.
- **Snapshot Harga:** Tabel `order_items` menyimpan `price_snapshot` dan `cost_snapshot` untuk menjaga historis data keuangan tetap akurat meskipun harga produk berubah.
- **Materialized View (Analitik):** Tabel `daily_product_sales` digunakan untuk Dashboard Analitik Portofolio Produk. Data di-agregasi via Laravel Scheduler/Command agar query dashboard tetap _fast_ (O(1)) dan tidak membebani database dengan `SUM()` real-time.

## 3. Konvensi Penamaan & Struktur Folder

### Backend (Laravel)

- **Controllers:** `app/Http/Controllers/Api/V1/` (Contoh: `ProductController.php`).
- **Requests:** `app/Http/Requests/` (Contoh: `StoreProductRequest.php`).
- **Resources:** `app/Http/Resources/` (Contoh: `ProductResource.php`).
- **Services:** `app/Services/` (Untuk logika bisnis kompleks, misal: `OrderProcessingService.php`).

### Frontend (Vue.js)

- **Components:** `src/components/` (Atomic design: `atoms/`, `molecules/`, `organisms/`).
- **Views/Pages:** `src/views/` (Contoh: `ProductListView.vue`, `DashboardAnalyticsView.vue`).
- **Stores (Pinia):** `src/stores/` (Contoh: `authStore.ts`, `cartStore.ts`).
- **Types/Interfaces:** `src/types/` (Contoh: `product.ts`, `user.ts`).
- **API Services:** `src/services/` (Contoh: `productService.ts`).

## 4. Standar API Response

Selalu gunakan format JSON yang konsisten untuk Frontend:

```json
{
  "success": true,
  "message": "Produk berhasil ditambahkan",
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 5 }
}
```
````

## 5. Git Workflow

- `main` / `master`: Branch produksi/stabil.
- `dev`: Branch development utama.
- `feature/nama-fitur`: Branch untuk fitur spesifik (misal: `feature/analitik-dashboard`).

````

---

### 2. File: `TODO.md`
File ini adalah *checklist* implementasi berdasarkan metode Waterfall (Fase Implementation & Testing). Centang (`- [x]`) jika sudah selesai.

```markdown
# TODO LIST - IMPLEMENTASI SKRIPSI KABITA E-COMMERCE

## FASE 1: BACKEND CORE & AUTHENTICATION (Laravel)
- [ ] Setup Laravel Sanctum untuk API Token Authentication.
- [ ] Buat API Controller & Routes untuk Auth (`AuthController.php`).
  - [ ] Endpoint: `POST /api/register` (Buyer & Seller).
  - [ ] Endpoint: `POST /api/login`.
  - [ ] Endpoint: `POST /api/logout`.
  - [ ] Endpoint: `GET /api/user` (Get current user).
- [ ] Buat Middleware untuk Role-Based Access Control (`RoleMiddleware.php`).
  - [ ] Roles: `admin`, `seller`, `buyer`.
- [ ] Buat Form Request Validation untuk Register & Login.

## FASE 2: BACKEND BUSINESS LOGIC (CRUD & TRANSAKSI)
- [ ] **Manajemen Toko (Seller):**
  - [ ] Endpoint: `POST /api/shops` (Register shop, upload logo).
  - [ ] Endpoint: `GET /api/shops/me` (Get seller's shop).
- [ ] **Manajemen Produk (Seller & Admin):**
  - [ ] Endpoint: CRUD Produk (`ProductController.php`).
  - [ ] Endpoint: `POST /api/products/{id}/upload-images` (Upload foto produk).
  - [ ] Endpoint: `PATCH /api/products/{id}/status` (Admin verifikasi produk).
- [ ] **Keranjang Belanja (Buyer):**
  - [ ] Endpoint: CRUD Cart & Cart Items.
- [ ] **Proses Checkout & Pesanan (Buyer & Seller):**
  - [ ] Endpoint: `POST /api/checkout` (Hitung total, kurangi stok, buat Order).
  - [ ] Endpoint: `POST /api/orders/{id}/payment` (Upload bukti TF).
  - [ ] Endpoint: `PATCH /api/orders/{id}/status` (Seller update status, Admin verifikasi payment).
  - [ ] Endpoint: `POST /api/orders/{id}/confirm` (Buyer konfirmasi barang sampai).

## FASE 3: BACKEND ANALITIK & OTOMASI (DASHBOARD PORTOFOLIO)
- [ ] Buat Laravel Command: `php artisan make:command AggregateDailySales`.
  - [ ] Logika: Hitung total qty, revenue, profit dari `order_items` yang statusnya 'delivered' per hari, lalu simpan ke `daily_product_sales`.
- [ ] Daftarkan Command ke `app/Console/Kernel.php` atau `routes/console.php` (Laravel 11+) untuk jalan setiap malam (Cronjob).
- [ ] Buat API Controller untuk Analitik (`AnalyticsController.php`).
  - [ ] Endpoint: `GET /api/analytics/portfolio` (Data untuk grafik penjualan per produk/kategori).
  - [ ] Endpoint: `GET /api/analytics/revenue` (Data revenue & profit toko).
- [ ] Buat API Resource (`DailyProductSaleResource.php`) untuk memformat data analitik.

## FASE 4: FRONTEND SETUP & UI FOUNDATION (Vue.js)
- [ ] Setup Project Vue.js dengan Vite & TypeScript (`npm create vue@latest`).
- [ ] Install Dependencies: PrimeVue, Pinia, Axios, Vue Router, Chart.js (untuk analitik).
- [ ] Konfigurasi Axios Interceptor (untuk inject Bearer Token & handle 401 Unauthorized).
- [ ] Setup Vue Router & Navigation Guards (Proteksi route berdasarkan Role: Admin/Seller/Buyer).
- [ ] Setup Layouts: `GuestLayout.vue`, `BuyerLayout.vue`, `SellerLayout.vue`, `AdminLayout.vue`.
- [ ] Buat Pinia Store: `authStore.ts`, `cartStore.ts`.

## FASE 5: FRONTEND BUYER FLOW (E-Commerce Standar)
- [ ] Halaman Auth: Login, Register.
- [ ] Halaman Utama: Homepage, Kategori, Pencarian Produk.
- [ ] Halaman Detail Produk (dengan galeri foto & pilih varian/qty).
- [ ] Halaman Keranjang (Cart) & Checkout (Pilih Alamat, Kurir/COD, Metode Bayar).
- [ ] Halaman Riwayat Pesanan (Buyer) & Konfirmasi Terima Barang.

## FASE 6: FRONTEND DASHBOARD (SELLER & ADMIN)
- [ ] **Dashboard Seller:**
  - [ ] Manajemen Produk (Tabel PrimeVue, Form Tambah/Edit Produk).
  - [ ] Manajemen Pesanan Masuk (Update status, Input Resi).
- [ ] **Dashboard Admin:**
  - [ ] Verifikasi Toko & Verifikasi Produk (Approve/Reject).
  - [ ] Verifikasi Pembayaran (Cek mutasi/bukti TF).
  - [ ] Manajemen User & Kategori.

## FASE 7: FRONTEND DASHBOARD ANALITIK PORTOFOLIO PRODUK (FITUR UNGGULAN SKRIPSI)
- [ ] Integrasi Chart.js / PrimeChart dengan data dari `AnalyticsController`.
- [ ] Buat Komponen: `PortfolioSalesChart.vue` (Grafik Line/Bar penjualan produk).
- [ ] Buat Komponen: `CategoryPerformanceChart.vue` (Grafik Pie/Doughnut performa kategori).
- [ ] Buat Komponen: `TopProductsTable.vue` (Tabel PrimeVue produk terlaris & profit tertinggi).
- [ ] Implementasi Filter Tanggal (Datepicker PrimeVue) untuk rentang data analitik.

## FASE 8: TESTING, OPTIMASI & DOKUMENTASI
- [ ] Testing Manual: Jalankan seluruh alur diagram (Registrasi -> Verifikasi -> Belanja -> Checkout -> Analitik).
- [ ] Optimasi: Pastikan query analitik menggunakan index database (sudah di-setup di migration).
- [ ] Dokumentasi API: Install `Scribe` atau `Swagger` di Laravel untuk generate API Docs (Nilai plus di skripsi).
- [ ] Persiapan Demo: Buat seeder khusus untuk data analitik (jika cronjob belum jalan, jalankan command manual).
````

---

### Tips Tambahan dari Pembimbing:

1. **Fokus Bertahap:** Jangan mengerjakan Frontend dan Backend secara bersamaan. Selesaikan **Fase 1, 2, dan 3** di Backend sampai tuntas dan dites menggunakan **Postman/Insomnia**. Setelah API stabil, baru pindah ke Frontend.
2. **Manajemen State:** Saat masuk ke Fase 4, pastikan Anda benar-benar memahami cara Pinia menyimpan data user dan cart. Gunakan `localStorage` untuk menyimpan token auth agar user tidak perlu login ulang saat refresh halaman.
3. **Fitur Unggulan:** Karena judul skripsi Anda menonjolkan **"Dashboard Analitik Portofolio Produk"**, pastikan **Fase 3 dan Fase 7** mendapatkan porsi waktu dan effort paling besar. Buat tampilannya se-menarik mungkin menggunakan PrimeVue dan Chart.js.

Simpan kedua file ini. Jika nanti Anda macet di salah satu _checklist_ (misalnya saat membuat Laravel Command untuk analitik atau setup Axios Interceptor), Anda bisa kembali ke sini dan bertanya spesifik pada bagian tersebut.

Selamat coding! Semoga skripsinya lancar dan mendapat nilai A!
