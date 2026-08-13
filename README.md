# ecommerce-umkm-kabita

## Menjalankan stack backend + frontend secara lokal

### 1) Backend (Laravel API)

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Backend API berjalan di `http://localhost:8000` dengan prefix endpoint `api/v1`.

### 2) Frontend (Vue)

```sh
cd frontend
npm install
cp .env.example .env
npm run dev
```

Pastikan nilai `VITE_API_BASE_URL` di `frontend/.env` mengarah ke backend:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

## Validasi frontend

```sh
cd frontend
npm run lint
npm run build
```
