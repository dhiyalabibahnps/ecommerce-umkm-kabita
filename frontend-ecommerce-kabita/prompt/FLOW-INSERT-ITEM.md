PROMPT v3 — FE↔BE INTEGRATION: "PENAMBAHAN BARANG" (BULK PRODUCT UPLOAD + ADMIN REVIEW)
PROJECT: KABITA UMKM E-COMMERCE (Thesis, Waterfall method)
STATUS: FINAL — ZERO ERROR, PRODUCTION-READY

============================================================
CONTEXT
============================================================
You are a senior full-stack developer implementing ONE vertical slice of
the "Sistem Penjualan" flowchart:

  Mulai
    -> Toko (seller) Mengupload Beberapa Produk
    -> Produk Ditinjau oleh Admin E-Commerce (goal: block illegal products)
    -> Lolos -> Produk Tampil di Web
    -> Tidak Lolos -> Produk Tidak Tampil di Web
    -> Selesai

Stack (LOCKED — do not deviate):
- FE: Vue 3 SFC (<script setup lang="ts">), TypeScript STRICT (no `any`,
  no @ts-ignore, no @ts-expect-error), PrimeVue 4, Pinia setup-stores,
  Axios instance `src/services/apiClient.ts`, Vue Router.
- BE: PHP 8.5 + Laravel 13 (REST API), MariaDB, Sanctum.
- Monorepo: single repo, `backend/` (Laravel) + `frontend/` (Vite+Vue).

============================================================
AUTHORITATIVE SOURCE OF TRUTH (use exactly, no improvisation)
============================================================
These files already exist and MUST be respected:

[BACKEND — already implemented]
- Admin product endpoints ALREADY EXIST in routes/api.php:
    GET    /api/v1/products/pending        (Admin\ProductController@index)
    PATCH  /api/v1/products/{product}/approve  (Admin\ProductController@approve)
    PATCH  /api/v1/products/{product}/reject    (Admin\ProductController@reject)
  See api-contracts.md § "Admin\ProductController" for exact contracts.

[FRONTEND — existing artifacts]
- src/types/enums.ts        -> ProductStatus = 'pending' | 'approved' | 'rejected' | 'active'
- src/types/entities.ts     -> Product { id, shop_id, category_id, name, slug, description,
                                         price: string, cost_price: string|null, stock: number,
                                         weight: number|null, status, verified_at,
                                         rejection_reason, created_at, images: ProductImage[] }
- src/types/schemas.ts      -> RejectProductRequest { rejection_reason: string } (REQUIRED)
- src/types/utils.ts        -> PaginatedResponse<T>, SingleResponse<T>,
                               ApiResponse<T>, ApiError, PaginationMeta
- src/services/             -> apiClient.ts, adminProductService.ts,
                               publicProductService.ts, categoryService.ts
- src/stores/product.ts     -> existing store to EXTEND (not replace)
- src/router/index.ts       -> seller routes already registered; admin needs review route

MISSING (you must create):
- src/services/sellerProductService.ts  (NEW file)
- src/views/admin-internal/page/AdminProductReviewView.vue  (NEW file)
- admin product-review route in router (insert BEFORE 'admin-dynamic-slug')

============================================================
BUSINESS RULES (trace to flowchart "Sistem Penjualan")
============================================================
FR-1  Seller with shop.status === 'verified' may upload SEVERAL products
      in ONE submission via multipart/form-data.
FR-2  Every created product starts with status 'pending'.
FR-3  Products with status 'pending' or 'rejected' are NEVER visible
      to public endpoints (GET /public/products returns only 'active').
FR-4  Admin reviews via PATCH /products/{id}/approve -> sets status='active'
      and verified_at=now(); PATCH /products/{id}/reject -> sets status='rejected'
      and rejection_reason=<text>.
FR-5  Seller sees rejection_reason on their list page.
FR-6  Bulk upload is ATOMIC: if any row fails validation, ALL rows roll back.

============================================================
DELIVERABLES — WATERFALL ORDER, ONE HEADED SECTION EACH
============================================================

PHASE 1 — REQUIREMENTS TRACEABILITY
Table (4 columns):
  | Flowchart Node | FR | Endpoint (method + path) | FE Artifact (service/store/view) |

PHASE 2 — DESIGN (extend api-contracts.md)
Add a NEW section "Seller\ProductController" with these contracts.
Use EXACT envelope types from utils.ts:

Endpoint 2.1 — POST /api/v1/seller/products (multipart/form-data)
  Auth: seller (shop must be verified).
  Body fields (use Laravel array-input notation):
    products[0][name]          string, required, max:120
    products[0][category_id]   integer, required, exists:categories,id
    products[0][description]   string, nullable
    products[0][price]         numeric, required, gt:0
    products[0][cost_price]    numeric, nullable, gte:0
    products[0][stock]         integer, required, gte:0
    products[0][weight]        integer, nullable, gte:0
    products[0][images][]      file, nullable, max:5 per product, image|max:2048
    products[1][...], products[2][...] ... (min 1, max 20 products)
  Responses:
    201 SingleResponse<Product[]>   -> newly created products (with images)
    403 { success:false, message:'Toko Anda belum terverifikasi.
             Silakan lengkapi profil toko terlebih dahulu.' }
    422 ApiResponse with errors map:
         { 'products.0.name': ['Nama wajib diisi.'] }

Endpoint 2.2 — GET /api/v1/seller/products
  Auth: seller.
  Query: status (optional, one of pending|approved|rejected|active),
         search (optional), sort (newest|oldest, default newest),
         per_page (optional, default 15).
  Response: PaginatedResponse<Product> (with shop & category relations loaded).

Endpoint 2.3 — PUT /api/v1/seller/products/{product} (multipart)
  Auth: seller, owner of product.
  Allowed only if product.status IN ('pending', 'rejected'). Otherwise 422:
    'Produk hanya dapat diedit saat statusnya menunggu tinjauan atau ditolak.'
  Same field rules as 2.1 (single product, not array).
  On success, status resets to 'pending' if it was 'rejected'.
  Response: SingleResponse<Product>.

Endpoint 2.4 — DELETE /api/v1/seller/products/{product}
  Auth: seller, owner.
  Allowed only if status IN ('pending', 'rejected'). Otherwise 422:
    'Produk aktif tidak dapat dihapus.'
  Response: MessageResponse with 'Produk berhasil dihapus.'

Admin endpoints 2.5 (ALREADY EXIST — document only, do not re-implement):
  GET    /api/v1/products/pending
  PATCH  /api/v1/products/{product}/approve
  PATCH  /api/v1/products/{product}/reject  body: RejectProductRequest

Public endpoints 2.6 (ALREADY EXIST — document visibility rule):
  GET /api/v1/public/products  -> scopeVisible() = whereIn(status, ['active'])
                                 NOTE: 'approved' is legacy; treat as active too.

ALL backend "message" strings MUST be Bahasa Indonesia.

PHASE 3 — IMPLEMENTATION

3.1 BACKEND (Laravel 13 / PHP 8.5)
- app/Enums/ProductStatus.php: keep 4 existing cases; add helper
  `public static function visible(): array => ['active'];`
  and `public static function editable(): array => ['pending','rejected'];`.
- Product model:
    - belongsTo Shop, belongsTo Category, hasMany ProductImage
    - casts: status (ProductStatus::class), price 'decimal:2',
      cost_price 'decimal:2', verified_at 'datetime'
    - scopes: scopeVisible($q), scopeEditable($q), scopePending($q)
- FormRequests:
    - StoreProductsRequest (array-level validation, rule `products.*.name`
      etc., 'products' => 'required|array|min:1|max:20')
    - UpdateProductRequest (single product, authorize via ProductPolicy)
- Seller\ProductController:
    - store(): DB::transaction; ProductPolicy.authorize('create', product)
      checks owner AND shop.status==='verified'; generate slug via
      Str::slug(name) + '-{id}' on collision; persist images via
      $request->file("products.{$i}.images")->store('products','public');
      attach ProductImage rows; return 201 SingleResponse<Product[]>.
    - index(): paginate with scope filtering.
    - update(): authorize ownership + editable status; re-store images
      if provided (delete old ones first).
    - destroy(): authorize ownership + editable status.
- Admin\ProductReviewController: KEEP EXISTING (approve/reject/pending).
- Api\ProductController: public listing uses scopeVisible() only.
- routes/api.php: register seller group with `auth:sanctum` + `role:seller`
  middleware under /seller/products.
- ProductPolicy: `create()` -> $user->shop?->status === 'verified'.
  `update()/delete()` -> owner + editable status.
- Pest tests (5 cases minimum):
    1. Bulk insert success -> all products created with status pending.
    2. Bulk insert with ONE invalid row -> entire batch rolled back.
    3. Unverified shop attempts POST -> 403 Indonesian message.
    4. Approve via admin PATCH -> status active + verified_at set,
       product now returned by GET /public/products.
    5. Reject via admin PATCH with reason -> status rejected,
       rejection_reason stored, product hidden from public.

3.2 FRONTEND (Vue 3 + TS strict)
A) TYPES (src/types/schemas.ts) — ADD:
   export interface StoreProductInput {
     name: string;
     category_id: number | null;
     description: string | null;
     price: number;          // form-level number; entity uses string
     cost_price: number | null;
     stock: number;
     weight: number | null;
     images: File[];
   }
   export interface StoreProductBulkRequest {
     products: StoreProductInput[];
   }
   export interface UpdateProductRequest {
     name?: string;
     category_id?: number | null;
     description?: string | null;
     price?: number;
     cost_price?: number | null;
     stock?: number;
     weight?: number | null;
     images?: File[];
   }

B) NEW src/services/sellerProductService.ts:
   import apiClient from './apiClient';
   import type { StoreProductBulkRequest, UpdateProductRequest,
                 Product, PaginatedResponse, SingleResponse,
                 MessageResponse } from '@/types';

   export interface SellerProductListParams {
     status?: 'pending'|'approved'|'rejected'|'active';
     search?: string;
     sort?: 'newest'|'oldest';
     per_page?: number;
   }

   export const sellerProductService = {
     async list(params: SellerProductListParams = {}):
       Promise<PaginatedResponse<Product>> {
       const { data } = await apiClient.get('/seller/products',
         { params });
       return data;
     },
     async createBulk(payload: StoreProductBulkRequest):
       Promise<SingleResponse<Product[]>> {
       const fd = new FormData();
       payload.products.forEach((p, i) => {
         fd.append(`products[${i}][name]`, p.name);
         if (p.category_id !== null)
           fd.append(`products[${i}][category_id]`, String(p.category_id));
         if (p.description)
           fd.append(`products[${i}][description]`, p.description);
         fd.append(`products[${i}][price]`, String(p.price));
         if (p.cost_price !== null)
           fd.append(`products[${i}][cost_price]`, String(p.cost_price));
         fd.append(`products[${i}][stock]`, String(p.stock));
         if (p.weight !== null)
           fd.append(`products[${i}][weight]`, String(p.weight));
         p.images.forEach((img, j) =>
           fd.append(`products[${i}][images][${j}]`, img));
       });
       const { data } = await apiClient.post('/seller/products', fd, {
         headers: { 'Content-Type': 'multipart/form-data' }
       });
       return data;
     },
     async update(id: number, payload: UpdateProductRequest):
       Promise<SingleResponse<Product>> {
       const fd = new FormData();
       fd.append('_method', 'PUT');
       Object.entries(payload).forEach(([k, v]) => {
         if (v === null || v === undefined) return;
         if (Array.isArray(v)) {
           (v as File[]).forEach((img, j) =>
             fd.append(`images[${j}]`, img));
         } else {
           fd.append(k, String(v));
         }
       });
       const { data } = await apiClient.post(`/seller/products/${id}`, fd);
       return data;
     },
     async remove(id: number): Promise<MessageResponse> {
       const { data } = await apiClient.delete(`/seller/products/${id}`);
       return data;
     }
   };

C) EXTEND src/services/adminProductService.ts — ADD:
   async listPending(params): Promise<PaginatedResponse<Product>>
     -> GET /products/pending
   async approve(id): Promise<SingleResponse<Product>>
     -> PATCH /products/{id}/approve
   async reject(id, payload: RejectProductRequest):
     Promise<SingleResponse<Product>>
     -> PATCH /products/{id}/reject

D) EXTEND src/stores/product.ts (Pinia setup store):
   - state: sellerProducts, sellerLoading, sellerSubmitting,
            sellerError, pendingReviews, reviewLoading
   - actions:
       fetchSellerProducts(params) -> calls sellerProductService.list
       submitBulkProducts(payload) -> calls sellerProductService.createBulk;
         on success show toast 'Produk berhasil dikirim dan menunggu
         tinjauan admin.' then router.push('/seller/produk');
         on 422 map errors to per-row field messages.
       approveProduct(id) -> calls adminProductService.approve;
         success toast 'Produk lolos tinjauan dan kini tampil di web.'
       rejectProduct(id, reason) -> calls adminProductService.reject;
         success toast 'Produk tidak lolos tinjauan dan tidak
         ditampilkan di web.'
       fetchPendingReviews(params) -> calls adminProductService.listPending
   - Error handling: 403 -> toast 'Anda tidak memiliki izin untuk
     melakukan aksi ini.'; 500 -> 'Terjadi kesalahan. Silakan coba lagi.'

E) VIEWS (PrimeVue 4):
   - src/views/seller/page/SellerProductCreateView.vue
       * Uses existing src/components/product/ folder for sub-components.
       * Dynamic array of product rows (min 1, max 20); "+ Tambah Produk"
         button; "Hapus" per row (disabled when only 1 row).
       * Per row: InputText(name), Select(category_id, loaded via
         categoryService.list()), Textarea(description),
         InputNumber(price, cost_price, stock, weight), FileUpload
         (auto:false, multiple:true, accept='image/*', preview,
         max 5 files); live image previews via URL.createObjectURL.
       * Submit -> build StoreProductBulkRequest -> store.submitBulkProducts.
       * Map 422 errors: backend key `products.{i}.{field}` ->
         row i field error message in red below input.
   - src/views/seller/page/SellerProductListView.vue (already exists path)
       * DataTable with Tag badges per status:
           pending  -> 'Menunggu Tinjauan' severity="warn"
           active   -> 'Lolos'           severity="success"
           approved -> 'Lolos'           severity="success"
           rejected -> 'Tidak Lolos'     severity="danger"
       * For rejected rows, show rejection_reason in tooltip or row-expansion.
       * Action column: Edit (only if editable), Delete (only if editable).
   - NEW src/views/admin-internal/page/AdminProductReviewView.vue
       * DataTable of pending products: name, shop.name, category.name,
         price, stock, images (thumbnail), created_at.
       * "Setujui" button -> ConfirmDialog 'Produk lolos tinjauan dan kini
         tampil di web. Lanjutkan?' -> store.approveProduct.
       * "Tolak" button -> Dialog with REQUIRED Textarea (reason, min 10
         chars) -> store.rejectProduct(id, { rejection_reason }).

F) ROUTER UPDATE — src/router/index.ts
   Insert this child route BEFORE the 'admin-dynamic-slug' route inside
   the /admin children array:
     {
       path: 'produk-review',
       name: 'admin-product-review',
       component: () => import('../views/admin-internal/page/AdminProductReviewView.vue'),
       meta: {
         requiresAuth: true,
         role: 'admin',
         hideHeaderFooter: true,
         title: 'Tinjauan Produk - Kabita Internal'
       }
     }

G) apiClient.ts interceptor (verify / update):
   - 401 -> router.push('/login'), clear token.
   - 403 -> useToast 'Anda tidak memiliki izin untuk melakukan aksi ini.'
   - 422 -> do NOT toast; propagate errors object to component for
     field-level mapping.
   - 500 -> useToast 'Terjadi kesalahan. Silakan coba lagi.'

============================================================
MANDATORY Bahasa Indonesia strings (use EXACTLY — do not paraphrase)
============================================================
- Bulk submit OK : 'Produk berhasil dikirim dan menunggu tinjauan admin.'
- Approve OK      : 'Produk lolos tinjauan dan kini tampil di web.'
- Reject OK      : 'Produk tidak lolos tinjauan dan tidak ditampilkan di web.'
- No shop         : 'Toko Anda belum terverifikasi. Silakan lengkapi profil toko terlebih dahulu.'
- Forbidden       : 'Anda tidak memiliki izin untuk melakukan aksi ini.'
- Generic error   : 'Terjadi kesalahan. Silakan coba lagi.'
- Delete OK       : 'Produk berhasil dihapus.'
- Edit locked     : 'Produk hanya dapat diedit saat statusnya menunggu tinjauan atau ditolak.'
- Delete locked   : 'Produk aktif tidak dapat dihapus.'
- Confirm approve : 'Produk lolos tinjauan dan kini tampil di web. Lanjutkan?'
- Reject dialog title: 'Tolak Produk'
- Reject reason label: 'Alasan Penolakan (wajib)'

============================================================
PHASE 4 — TESTING & HANDOFF
============================================================
- Pest paths: tests/Feature/Seller/Product/CreateTest.php,
  tests/Feature/Seller/Product/UpdateTest.php,
  tests/Feature/Admin/Product/ReviewTest.php.
- Vitest: tests/unit/stores/product.spec.ts (Axios mocked with
  vi.mock('@/services/apiClient')).
- Manual E2E checklist matching flowchart:
  1. Login as seller with verified shop.
  2. Visit /seller/produk/tambah -> add 3 products, submit.
  3. Verify DB: 3 rows status=pending.
  4. Visit /public/produk -> none of them visible.
  5. Login as admin -> /admin/produk-review -> approve 2, reject 1
     with reason 'Foto tidak sesuai.'
  6. DB check: 2 rows status=active, 1 row status=rejected +
     rejection_reason set.
  7. Visit /public/produk -> only the 2 approved visible.
  8. Seller list page shows 'Tidak Lolos' badge with reason tooltip.
- Update api-contracts.md (add § Seller\ProductController) and
  README.md (add section "Penambahan Barang Flow").

============================================================
OUTPUT RULES
============================================================
- Explanation and code comments in English.
- ALL user-facing strings (labels, placeholders, toasts, dialogs,
  validation messages, backend "message") in Bahasa Indonesia,
  EXACTLY as listed above.
- Return FULL file contents, each prefixed with its absolute path
  (e.g. // FILE: frontend/src/services/sellerProductService.ts).
- One clearly headed section per Waterfall phase so it can be pasted
  directly into the thesis documentation.
- Do NOT use `any`, do NOT use @ts-ignore, do NOT use @ts-expect-error.
- Do NOT invent endpoints that contradict api-contracts.md.
- All TypeScript code must compile under `"strict": true`.