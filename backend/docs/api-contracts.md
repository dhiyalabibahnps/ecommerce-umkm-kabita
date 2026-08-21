# Kabita Ecommerce API Contracts

**Base URL:** `/api/v1`  
**Auth:** Bearer token via `auth:sanctum` on protected routes.  
**Content-Type:** `application/json`  
**Pagination:** List endpoints return `meta` with `current_page`, `per_page`, `total`, `last_page`.  
**Envelope:** Most responses use `{ success, message?, data, meta? }`.  
**Note:** Some endpoints return dev-only fields such as `verification_code`; do not rely on them in production.

---

## AuthController
**Path prefix:** `/auth`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| POST | `/auth/login` | no | Login |
| POST | `/auth/register` | no | Register buyer/seller |
| POST | `/auth/verify-email` | no | Verify email code |
| POST | `/auth/resend-code` | no | Resend verification code |
| POST | `/auth/logout` | yes | Logout |
| GET | `/auth/me` | yes | Current user |
| PUT | `/auth/me/profile` | yes | Update buyer profile |

### POST /auth/login
**Request body**
- `email`: string, required
- `password`: string, required
- `remember`: boolean, optional

**Responses**
- `200`: `{ success: true, message: "Login berhasil.", data: { user: UserResource, token: string } }`
- `401`: `{ success: false, message: "Email atau password salah." }`
- `403`: `{ success: false, message: "Akun Anda tidak aktif atau telah diblokir." }`

### POST /auth/register
**Request body**
- `name`: string, required
- `email`: string, required
- `phone`: string, required
- `password`: string, required, min 8
- `password_confirmation`: string, required
- `role`: `buyer` | `seller`, required
- `shop_name`: string, required if `role=seller`

**Responses**
- `201`: `{ success: true, message: "...", data: { user: UserResource, shop: ShopResource|null, verification_code: string } }`
  - `verification_code` is dev-only.

### POST /auth/verify-email
**Request body**
- `email`: string, required
- `code`: string, required, 6 digits

**Responses**
- `200`: `{ success: true, message: "Email berhasil diverifikasi. Silakan login." }`
- `400`: `{ success: false, message: "Kode verifikasi tidak valid atau sudah kedaluwarsa." }`

### POST /auth/resend-code
**Request body**
- `email`: string, required

**Responses**
- `200`: `{ success: true, message: "...", data: { verification_code: string } }`
- `400`: `{ success: false, message: "Email sudah diverifikasi." }`

### POST /auth/logout
**Responses**
- `200`: `{ success: true, message: "Logout berhasil" }`

### GET /auth/me
**Responses**
- `200`: `{ success: true, data: UserResource }`

### PUT /auth/me/profile
**Request body** (`multipart/form-data`)
- `name`: string, required
- `phone`: string, required
- `address`: string, optional
- `email`: string, required, email
- `photo`: file, optional, image, max 2MB

**Responses**
- `200`: `{ success: true, message: "Profil berhasil diperbarui.", data: UserResource }`
- `422`: `{ success: false, message: "Validasi gagal.", errors: {} }`

---

## CategoryController
**Path prefix:** `/categories`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/categories` | no | List categories |
| GET | `/categories/{slug}` | no | Category detail |
| POST | `/categories` | yes, admin | Create category |
| PUT | `/categories/{category}` | yes, admin | Update category |
| DELETE | `/categories/{category}` | yes, admin | Delete category |

### GET /categories
**Query**
- none

**Responses**
- `200`: `{ success: true, data: CategoryResource[] }`

### GET /categories/{slug}
**Responses**
- `200`: `{ success: true, data: CategoryResource }`
- `404`: `{ success: false, message: "Kategori tidak ditemukan." }`

### POST /categories
**Request body**
- `name`: string, required
- `slug`: string, optional; auto-generated from name if empty

**Responses**
- `201`: `{ success: true, message: "Kategori berhasil dibuat.", data: CategoryResource }`

### PUT /categories/{category}
**Request body**
- `name`: string, optional
- `slug`: string, optional

**Responses**
- `200`: `{ success: true, message: "Kategori berhasil diperbarui.", data: CategoryResource }`

### DELETE /categories/{category}
**Responses**
- `200`: `{ success: true, message: "Kategori berhasil dihapus." }`
- `409`: `{ success: false, message: "Kategori tidak dapat dihapus karena masih memiliki produk." }`

---

## ShippingController
**Path prefix:** `/shipping`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/shipping/options` | yes, buyer | List shipping options |
| POST | `/shipping/calculate` | yes, buyer | Calculate shipping cost |

### GET /shipping/options
**Responses**
- `200`: `{ success: true, data: ShippingOption[] }`

**ShippingOption**
```json
{
  "id": "cod | kurir_reguler | kurir_express",
  "name": "string",
  "cost": 0,
  "base_cost": 10000,
  "estimated_days": "string | null"
}
```

### POST /shipping/calculate
**Request body**
- `weight`: number, required, grams
- `shipping_method`: string, required
- `courier_type`: `reguler` | `express`, optional, default `reguler`

**Responses**
- `200`: `{ success: true, data: { shipping_method, courier_type, estimated_cost, estimated_days } }`

---

## Admin\AnalyticsController
**Path prefix:** `/analytics`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/analytics/platform` | yes, admin | Platform stats |
| GET | `/analytics/sales` | yes, admin | Sales by period |
| GET | `/analytics/top-sellers` | yes, admin | Top sellers |
| GET | `/analytics/top-products` | yes, admin | Top products |

### GET /analytics/platform
**Query**
- none

**Responses**
- `200`: `{ success: true, data: { total_users, users_by_role, total_shops, shops_by_status, verified_shops, pending_shops, total_products, monthly_transactions, platform_revenue } }`

**monthly_transactions item**
```json
{
  "month": 1,
  "transactions": 10,
  "revenue": "1000000.00"
}
```

### GET /analytics/sales
**Query**
- `period`: `daily` | `weekly` | `monthly`, optional, default `monthly`

**Responses**
- `200`: `{ success: true, data: SalesRow[] }`

**SalesRow**
```json
{
  "date": "2026-01-01",
  "revenue": "50000.00",
  "orders_count": 5
}
```

### GET /analytics/top-sellers
**Query**
- `per_page`: integer, optional, default `10`

**Responses**
- `200`: `{ success: true, data: TopSeller[] }`

**TopSeller**
```json
{
  "id": 1,
  "name": "string",
  "total_orders": 10,
  "total_revenue": "500000.00"
}
```

### GET /analytics/top-products
**Query**
- `per_page`: integer, optional, default `10`

**Responses**
- `200`: `{ success: true, data: TopProduct[] }`

**TopProduct**
```json
{
  "id": 1,
  "name": "string",
  "slug": "string",
  "price": "10000.00",
  "total_qty_sold": 50,
  "total_revenue": "500000.00"
}
```

---

## Admin\PaymentController
**Path prefix:** `/payments`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/payments/pending` | yes, admin | List pending payments |
| GET | `/payments/{payment}` | yes, admin | Payment detail |
| PATCH | `/payments/{payment}/verify` | yes, admin | Verify payment |
| PATCH | `/payments/{payment}/reject` | yes, admin | Reject payment |

### GET /payments/pending
**Query**
- `shop_id`: integer, optional
- `buyer_id`: integer, optional
- `search`: string, optional, order number like
- `sort`: `newest` | `oldest`, optional, default `newest`
- `per_page`: integer, optional, default `15`

**Responses**
- `200`: `{ success: true, data: PaymentResource[], meta: PaginationMeta }`

### GET /payments/{payment}
**Responses**
- `200`: `{ success: true, data: PaymentResource }`

### PATCH /payments/{payment}/verify
**Responses**
- `200`: `{ success: true, message: "Pembayaran berhasil diverifikasi.", data: OrderResource }`
- `422`: `{ success: false, message: "Pembayaran tidak dalam status pending." }`

### PATCH /payments/{payment}/reject
**Request body**
- `rejection_reason`: string, optional

**Responses**
- `200`: `{ success: true, message: "Pembayaran berhasil ditolak.", data: PaymentResource }`
- `422`: `{ success: false, message: "Pembayaran tidak dalam status pending." }`

---

## Admin\ProductController
**Path prefix:** `/products`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/products/pending` | yes, admin | List pending products |
| PATCH | `/products/{product}/approve` | yes, admin | Approve product |
| PATCH | `/products/{product}/reject` | yes, admin | Reject product |

### GET /products/pending
**Query**
- `shop_id`: integer, optional
- `category_id`: integer, optional
- `search`: string, optional
- `sort`: `newest` | `oldest`, optional, default `newest`
- `per_page`: integer, optional, default `15`

**Responses**
- `200`: `{ success: true, data: ProductResource[], meta: PaginationMeta }`

### PATCH /products/{product}/approve
**Responses**
- `200`: `{ success: true, message: "Produk berhasil disetujui.", data: ProductResource }`
- `422`: `{ success: false, message: "Produk tidak dalam status pending." }`

### PATCH /products/{product}/reject
**Request body**
- `rejection_reason`: string, required

**Responses**
- `200`: `{ success: true, message: "Produk berhasil ditolak.", data: ProductResource }`
- `422`: `{ success: false, message: "Produk tidak dalam status pending." }`

---

## Admin\ShopController
**Path prefix:** `/shops`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/shops/pending` | yes, admin | List pending shops |
| PATCH | `/shops/{shop}/verify` | yes, admin | Verify shop |
| PATCH | `/shops/{shop}/reject` | yes, admin | Reject shop |

### GET /shops/pending
**Query**
- `search`: string, optional
- `sort`: `newest` | `oldest`, optional, default `newest`
- `per_page`: integer, optional, default `15`

**Responses**
- `200`: `{ success: true, data: ShopResource[], meta: PaginationMeta }`

### PATCH /shops/{shop}/verify
**Responses**
- `200`: `{ success: true, message: "Toko berhasil diverifikasi.", data: ShopResource }`
- `422`: `{ success: false, message: "Toko tidak dalam status pending." }`

### PATCH /shops/{shop}/reject
**Request body**
- `rejection_reason`: string, required

**Responses**
- `200`: `{ success: true, message: "Toko berhasil ditolak.", data: ShopResource }`
- `422`: `{ success: false, message: "Toko tidak dalam status pending." }`

---

## Admin\UserController
**Path prefix:** `/users`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/users` | yes, admin | List users |
| PATCH | `/users/{user}/suspend` | yes, admin | Suspend user |
| PATCH | `/users/{user}/activate` | yes, admin | Activate user |

### GET /users
**Query**
- `role`: string, optional
- `status`: string, optional
- `search`: string, optional
- `sort`: `newest` | `oldest`, optional, default `newest`
- `per_page`: integer, optional, default `15`

**Responses**
- `200`: `{ success: true, data: UserResource[], meta: PaginationMeta }`

### PATCH /users/{user}/suspend
**Responses**
- `200`: `{ success: true, message: "Pengguna berhasil disuspend.", data: UserResource }`
- `422`: `{ success: false, message: "Pengguna sudah dalam status suspended." }`

### PATCH /users/{user}/activate
**Responses**
- `200`: `{ success: true, message: "Pengguna berhasil diaktifkan.", data: UserResource }`
- `422`: `{ success: false, message: "Pengguna tidak dalam status suspended." }`

---

## Buyer\CartController
**Path prefix:** `/cart`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/cart` | yes, buyer | Get cart |
| POST | `/cart/items` | yes, buyer | Add item |
| PUT | `/cart/items/{cartItem}` | yes, buyer | Update item qty |
| DELETE | `/cart/items/{cartItem}` | yes, buyer | Remove item |
| DELETE | `/cart/clear` | yes, buyer | Clear cart |
| GET | `/cart/validate` | yes, buyer | Validate cart for checkout |

### GET /cart
**Responses**
- `200`: `{ success: true, data: CartResource }`

**CartResource includes**
- `groups_by_shop`: ShopCartGroupResource[]
- `subtotal`: number
- `total`: number
- `total_items`: integer
- `stock_status`: object with `available` and `unavailable_items`

### POST /cart/items
**Request body**
- `product_id`: integer, required
- `quantity`: integer, required, min 1

**Responses**
- `201`: `{ success: true, message: "Barang berhasil ditambahkan ke keranjang.", data: CartResource }`
- `422`: `{ success: false, message: "Jumlah total melebihi stok yang tersedia." }`

### PUT /cart/items/{cartItem}
**Request body**
- `quantity`: integer, required

**Responses**
- `200`: `{ success: true, message: "Jumlah barang berhasil diperbarui.", data: CartResource }`
- `403`: `{ success: false, message: "Akses ditolak. Item keranjang ini bukan milik Anda." }`

### DELETE /cart/items/{cartItem}
**Responses**
- `200`: `{ success: true, message: "Barang berhasil dihapus dari keranjang.", data: CartResource }`
- `403`: `{ success: false, message: "Akses ditolak. Item keranjang ini bukan milik Anda." }`

### DELETE /cart/clear
**Responses**
- `200`: `{ success: true, message: "Keranjang belanja berhasil dikosongkan.", data: CartResource }`
- `403`: `{ success: false, message: "Akses ditolak." }`

### GET /cart/validate
**Responses**
- `200`: `{ success: true, data: { available: boolean, unavailable_items: [], groups_by_shop: ShopCartGroupResource[], subtotal: number, total: number, total_items: integer } }`

---

## Buyer\CheckoutController
**Path prefix:** `/checkout`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| POST | `/checkout` | yes, buyer | Checkout cart |

### POST /checkout
**Request body**
- `cart_items`: integer[], required
- `shipping_method`: `cod` | `kurir`, required
- `payment_method`: `transfer` | `cod`, required
- `shipping_address`: string, required if `shipping_method=kurir`, max 255
- `location_id`: integer, optional, must belong to the authenticated buyer
- `shipping_address`: string, required if `shipping_method=kurir` and `location_id` is not provided, max 255
- `notes`: string, optional, max 500

**Responses**
- `200`: `{ message: "Checkout successful.", order: OrderResource }`
- `422`: `{ message: "Some items in your cart are out of stock or unavailable.", unavailable_items: [] }`

---

## Buyer\LocationController
**Path prefix:** `/locations`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/locations` | yes, buyer | List shipping locations |
| POST | `/locations` | yes, buyer | Save shipping location |
| PUT | `/locations/{location}` | yes, buyer | Update shipping location |
| PATCH | `/locations/{location}/default` | yes, buyer | Set default shipping location |
| DELETE | `/locations/{location}` | yes, buyer | Delete shipping location |

### GET /locations
**Responses**
- `200`: `{ success: true, data: LocationResource[] }`

### POST /locations
**Request body**
- `name`: string, required
- `address`: string, required
- `latitude`: number, optional
- `longitude`: number, optional
- `is_default`: boolean, optional

**Responses**
- `201`: `{ success: true, message: "Lokasi pengiriman berhasil disimpan.", data: LocationResource }`

### PUT /locations/{location}
**Request body**
- `name`: string, required
- `address`: string, required
- `latitude`: number, optional
- `longitude`: number, optional
- `is_default`: boolean, optional

**Responses**
- `200`: `{ success: true, message: "Lokasi pengiriman berhasil diperbarui.", data: LocationResource }`
- `403`: `{ success: false, message: "Akses ditolak." }`

### PATCH /locations/{location}/default
**Responses**
- `200`: `{ success: true, message: "Lokasi pengiriman default berhasil diperbarui.", data: LocationResource }`
- `403`: `{ success: false, message: "Akses ditolak." }`

### DELETE /locations/{location}
**Responses**
- `200`: `{ success: true, message: "Lokasi pengiriman berhasil dihapus." }`
- `403`: `{ success: false, message: "Akses ditolak." }`

---

## Buyer\OrderController
**Path prefix:** `/orders`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/orders` | yes, buyer | List buyer orders |
| GET | `/orders/{order}` | yes, buyer | Order detail |
| PATCH | `/orders/{order}/confirm` | yes, buyer | Confirm received |
| PATCH | `/orders/{order}/cancel` | yes, buyer | Cancel order |
| POST | `/orders/{order}/cod-confirm` | yes, buyer | Confirm COD payment |

### GET /orders
**Query**
- `status`: string, optional
- `start_date`: `Y-m-d`, optional
- `end_date`: `Y-m-d`, optional
- `sort`: `newest` | `oldest` | `total_asc` | `total_desc`, optional, default `newest`
- `per_page`: integer, optional, default `15`

**Responses**
- `200`: `{ success: true, data: OrderResource[], meta: PaginationMeta }`

### GET /orders/{order}
**Responses**
- `200`: `{ success: true, data: OrderResource }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`

### PATCH /orders/{order}/confirm
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Pesanan berhasil dikonfirmasi sebagai diterima." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status shipped yang dapat dikonfirmasi." }`

### PATCH /orders/{order}/cancel
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Order berhasil dibatalkan. Stok telah dikembalikan dan refund diproses." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status pending yang dapat dibatalkan." }`

### POST /orders/{order}/cod-confirm
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Pembayaran COD berhasil dikonfirmasi. Pesanan sedang diproses." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan metode pembayaran COD yang dapat dikonfirmasi." }`

---

## Buyer\PaymentController
**Path prefix:** `/payments`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| POST | `/payments/{payment}/upload` | yes, buyer | Upload payment proof |

### POST /payments/{payment}/upload
**Request body** (`multipart/form-data`)
- `proof_image`: file, required, image, max 2MB, mimes `jpeg,png,jpg,webp`

**Responses**
- `200`: `{ success: true, message: "Bukti pembayaran berhasil diunggah.", data: PaymentResource }`
- `403`: `{ success: false, message: "Akses ditolak. Pembayaran ini bukan milik Anda." }`

---

## Buyer\PaymentSettingController
**Path prefix:** `/buyer/payment-settings`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/buyer/payment-settings` | yes, buyer | Get active payment setting |

### GET /buyer/payment-settings
**Responses**
- `200`: `{ success: true, data: PaymentSettingResource }`
- `404`: `{ success: false, message: "Pengaturan pembayaran belum diatur." }`

---

## Admin\PaymentSettingController
**Path prefix:** `/payment-settings`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/payment-settings` | yes, admin | Get current payment setting |
| POST | `/payment-settings` | yes, admin | Create payment setting |
| PUT | `/payment-settings/{paymentSetting}` | yes, admin | Update payment setting |

### GET /payment-settings
**Responses**
- `200`: `{ success: true, data: PaymentSettingResource }`
- `404`: `{ success: false, message: "Pengaturan pembayaran belum diatur." }`

### POST /payment-settings
**Request body**
- `bank_name`: string, required
- `account_number`: string, required, unique
- `account_holder_name`: string, required
- `is_active`: boolean, optional

**Responses**
- `201`: `{ success: true, message: "Pengaturan pembayaran berhasil dibuat.", data: PaymentSettingResource }`
- `422`: `{ success: false, message: "Pengaturan pembayaran sudah ada." }`

### PUT /payment-settings/{paymentSetting}
**Request body**
- `bank_name`: string, required
- `account_number`: string, required, unique
- `account_holder_name`: string, required
- `is_active`: boolean, optional

**Responses**
- `200`: `{ success: true, message: "Pengaturan pembayaran berhasil diperbarui.", data: PaymentSettingResource }`
- `404`: `{ success: false, message: "Pengaturan pembayaran belum diatur." }`

---

## Buyer\ProductController
**Path prefix:** `/public/products`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/public/products` | no | Browse products |
| GET | `/public/products/{slug}` | no | Product detail |

### GET /public/products
**Query**
- `search`: string, optional
- `category_id`: integer, optional
- `shop_id`: integer, optional
- `min_price`: integer, optional
- `max_price`: integer, optional
- `sort`: `newest` | `price_asc` | `price_desc`, optional, default `newest`
- `per_page`: integer, optional, default `12`

**Responses**
- `200`: `{ success: true, data: ProductResource[], meta: PaginationMeta }`

### GET /public/products/{slug}
**Responses**
- `200`: `{ success: true, data: ProductResource }`
- `404`: `{ success: false, message: "Produk tidak ditemukan." }`

---

## Seller\AnalyticsController
**Path prefix:** `/seller/analytics`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/seller/analytics/overview` | yes, seller | Shop overview |
| GET | `/seller/analytics/sales` | yes, seller | Sales analytics |
| GET | `/seller/analytics/products/top` | yes, seller | Top products |
| GET | `/seller/analytics/products/low-stock` | yes, seller | Low stock products |

### GET /seller/analytics/overview
**Responses**
- `200`: `{ success: true, data: { total_products, total_orders, total_revenue, pending_orders_count } }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

### GET /seller/analytics/sales
**Query**
- `period`: `daily` | `weekly` | `monthly`, optional, default `daily`
- `start_date`: `Y-m-d`, optional
- `end_date`: `Y-m-d`, optional

**Responses**
- `200`: `{ success: true, data: SalesRow[] }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

### GET /seller/analytics/products/top
**Responses**
- `200`: `{ success: true, data: SellerTopProduct[] }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

**SellerTopProduct**
```json
{
  "id": 1,
  "name": "string",
  "slug": "string",
  "total_qty_sold": 10,
  "revenue": "100000.00",
  "profit": "30000.00"
}
```

### GET /seller/analytics/products/low-stock
**Responses**
- `200`: `{ success: true, data: LowStockProduct[] }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

**LowStockProduct**
```json
{
  "id": 1,
  "name": "string",
  "slug": "string",
  "stock": 3,
  "price": "10000.00",
  "cost_price": "7000.00"
}
```

---

## Seller\OrderController
**Path prefix:** `/seller/orders`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/seller/orders` | yes, seller | List shop orders |
| GET | `/seller/orders/{order}` | yes, seller | Order detail |
| PATCH | `/seller/orders/{order}/process` | yes, seller | Process order |
| PATCH | `/seller/orders/{order}/ship` | yes, seller | Ship order |

### GET /seller/orders
**Query**
- `status`: string, optional
- `start_date`: `Y-m-d`, optional
- `end_date`: `Y-m-d`, optional
- `sort`: `newest` | `oldest` | `total_asc` | `total_desc`, optional, default `newest`
- `per_page`: integer, optional, default `15`

**Responses**
- `200`: `{ success: true, data: OrderResource[], meta: PaginationMeta }`

### GET /seller/orders/{order}
**Responses**
- `200`: `{ success: true, data: OrderResource }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik toko Anda." }`

### PATCH /seller/orders/{order}/process
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Order berhasil diproses." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik toko Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status pending yang dapat diproses." }`

### PATCH /seller/orders/{order}/ship
**Request body**
- `tracking_number`: string, optional

**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Order berhasil dikirim." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik toko Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status processing yang dapat dikirim." }`

---

## Seller\ShopController
**Path prefix:** `/shops`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| POST | `/shops` | yes, seller | Create shop |
| GET | `/shops/my-shop` | yes, seller | My shop |
| PUT | `/shops/{shop}` | yes, seller | Update shop |
| GET | `/shops/{slug}` | no | Public shop detail |

### POST /shops
**Request body** (`multipart/form-data`)
- `name`: string, required
- `description`: string, optional
- `logo`: file, optional
- plus any other shop fields accepted by `CreateShopRequest`

**Responses**
- `201`: `{ success: true, message: "Toko berhasil dibuat. Menunggu verifikasi admin.", data: ShopResource }`

### GET /shops/my-shop
**Responses**
- `200`: `{ success: true, data: ShopResource }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

### PUT /shops/{shop}
**Request body** (`multipart/form-data`)
- shop profile fields accepted by `UpdateShopRequest`
- `logo`: file, optional

**Responses**
- `200`: `{ success: true, message: "Profil toko berhasil diperbarui.", data: ShopResource }`
- `403`: `{ success: false, message: "Akses ditolak. Toko ini bukan milik Anda." }`

### GET /shops/{slug}
**Responses**
- `200`: `{ success: true, data: ShopResource }`
- `404`: `{ success: false, message: "Toko tidak ditemukan." }`

---

## Resource Shapes

**UserResource**
```json
{
  "id": 1,
  "name": "string",
  "email": "string",
  "role": "buyer | seller",
  "phone": "string",
  "status": "string",
  "email_verified_at": "Y-m-d H:i:s | null",
  "created_at": "Y-m-d H:i:s | null"
}
```

**ShopResource**
```json
{
  "id": 1,
  "name": "string",
  "slug": "string",
  "description": "string | null",
  "logo": "string | null",
  "status": "string",
  "seller": UserResource | null,
  "product_count": "integer | null",
  "products": ProductResource[] | null,
  "verifier": UserResource | null,
  "verified_at": "Y-m-d H:i:s | null",
  "rejection_reason": "string | null",
  "created_at": "Y-m-d H:i:s | null"
}
```

**ProductResource**
```json
{
  "id": 1,
  "name": "string",
  "slug": "string",
  "description": "string | null",
  "price": "number",
  "cost_price": "number | null",
  "stock": "integer",
  "weight": "number | null",
  "status": "string",
  "verified_at": "Y-m-d H:i:s | null",
  "rejection_reason": "string | null",
  "shop": {
    "id": 1,
    "name": "string",
    "slug": "string",
    "seller": {
      "id": 1,
      "name": "string",
      "email": "string"
    } | null
  } | null,
  "category": {
    "id": 1,
    "name": "string"
  } | null,
  "images": [
    {
      "id": 1,
      "url": "string | null"
    }
  ] | null,
  "created_at": "Y-m-d H:i:s | null"
}
```

**PaymentResource**
```json
{
  "id": 1,
  "order_id": 1,
  "amount": "number",
  "status": "string",
  "proof_image": "string | null",
  "created_at": "string",
  "updated_at": "string"
}
```

**PaymentSettingResource**
```json
{
  "id": 1,
  "bank_name": "string",
  "account_number": "string",
  "account_holder_name": "string",
  "is_active": "boolean"
}
```

**OrderResource**
```json
{
  "id": 1,
  "order_number": "ORD-YYYYMMDD-XXXXX",
  "buyer_id": 1,
  "shop_id": 1,
  "subtotal": "number",
  "shipping_cost": "number",
  "total_amount": "number",
  "shipping_method": "cod | kurir",
  "payment_method": "transfer | cod",
  "status": "string",
  "shipping_address": "string | null",
  "tracking_number": "string | null",
  "notes": "string | null",
  "created_at": "string",
  "updated_at": "string",
  "buyer": UserResource | null,
  "shop": ShopResource | null,
  "items": OrderItemResource[] | null,
  "payment": PaymentResource | null
}
```

**OrderItemResource**
```json
{
  "id": 1,
  "order_id": 1,
  "product_id": 1,
  "quantity": 1,
  "price_snapshot": "number",
  "cost_snapshot": "number",
  "created_at": "string",
  "updated_at": "string",
  "product": ProductResource | null
}
```

**CodLocationResource**
```json
{
  "id": 1,
  "user_id": 1,
  "name": "string",
  "address": "string",
  "latitude": "number | null",
  "longitude": "number | null",
  "is_default": "boolean",
  "created_at": "Y-m-d H:i:s | null",
  "updated_at": "Y-m-d H:i:s | null"
}
```

**CategoryResource**
```json
{
  "id": 1,
  "name": "string",
  "slug": "string",
  "product_count": "integer | null",
  "products": ProductResource[] | null,
  "created_at": "Y-m-d H:i:s | null"
}
```

**CartResource**
```json
{
  "id": 1,
  "buyer_id": 1,
  "groups_by_shop": ShopCartGroupResource[],
  "subtotal": "number | null",
  "total": "number | null",
  "total_items": "integer | null",
  "stock_status": {
    "available": "boolean",
    "unavailable_items": []
  },
  "created_at": "Y-m-d H:i:s | null",
  "updated_at": "Y-m-d H:i:s | null"
}
```

**ShopCartGroupResource**
```json
{
  "shop": {
    "id": 1,
    "name": "string",
    "slug": "string",
    "logo": "string | null"
  },
  "items": CartItemResource[],
  "subtotal": "number"
}
```

**CartItemResource**
```json
{
  "id": 1,
  "cart_id": 1,
  "product_id": 1,
  "quantity": 1,
  "product": ProductResource | null,
  "subtotal": "number | null",
  "created_at": "Y-m-d H:i:s | null",
  "updated_at": "Y-m-d H:i:s | null"
}
```

---

## Notes for Frontend Integration
- Use `Authorization: Bearer <token>` for protected routes.
- `POST /auth/register` may include `verification_code` in dev; ignore it in production.
- `GET /public/products` and `GET /public/products/{slug}` are public.
- `GET /shops/{slug}` is public.
- `POST /checkout` returns `order` directly, not wrapped in `data`.
- `POST /payments/{payment}/upload` requires `multipart/form-data`.
- `PATCH /seller/orders/{order}/ship` accepts optional `tracking_number`.
- `PUT /shops/{shop}` and `POST /shops` may accept `logo` upload.
- Pagination meta fields: `current_page`, `per_page`, `total`, `last_page`.
