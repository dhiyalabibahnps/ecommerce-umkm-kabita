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

### POST /auth/login
**Request body**
- `email`: string, required
- `password`: string, required
- `remember`: boolean, optional

**Responses**
- `200`: `{ success: true, message: "Login berhasil.", data: { user: UserResource, token: string } }`
- `401`: `{ success: false, message: "Email atau password salah." }`
- `403`: `{ success: false, message: "Akun Anda tidak aktif atau telah diblokir." }`

**Example request**
```json
{
  "email": "buyer@example.com",
  "password": "secret123",
  "remember": true
}
```

**Example response**
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "name": "Dhiya",
      "email": "buyer@example.com",
      "role": "buyer",
      "phone": "081234567890",
      "status": "active",
      "email_verified_at": "2026-01-01 00:00:00",
      "created_at": "2026-01-01 00:00:00"
    },
    "token": "1|abcdef123456"
  }
}
```

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

**Example request**
```json
{
  "name": "Dhiya",
  "email": "buyer@example.com",
  "phone": "081234567890",
  "password": "secret123",
  "password_confirmation": "secret123",
  "role": "buyer"
}
```

**Example response**
```json
{
  "success": true,
  "message": "Registrasi berhasil.",
  "data": {
    "user": {
      "id": 2,
      "name": "Dhiya",
      "email": "buyer@example.com",
      "role": "buyer",
      "phone": "081234567890",
      "status": "pending",
      "email_verified_at": null,
      "created_at": "2026-01-01 00:00:00"
    },
    "shop": null,
    "verification_code": "123456"
  }
}
```

### POST /auth/verify-email
**Request body**
- `email`: string, required
- `code`: string, required, 6 digits

**Responses**
- `200`: `{ success: true, message: "Email berhasil diverifikasi. Silakan login." }`
- `400`: `{ success: false, message: "Kode verifikasi tidak valid atau sudah kedaluwarsa." }`

**Example request**
```json
{
  "email": "buyer@example.com",
  "code": "123456"
}
```

**Example response**
```json
{
  "success": true,
  "message": "Email berhasil diverifikasi. Silakan login."
}
```

### POST /auth/resend-code
**Request body**
- `email`: string, required

**Responses**
- `200`: `{ success: true, message: "...", data: { verification_code: string } }`
- `400`: `{ success: false, message: "Email sudah diverifikasi." }`

**Example request**
```json
{
  "email": "buyer@example.com"
}
```

**Example response**
```json
{
  "success": true,
  "message": "Kode verifikasi baru telah dikirim.",
  "data": {
    "verification_code": "654321"
  }
}
```

### POST /auth/logout
**Responses**
- `200`: `{ success: true, message: "Logout berhasil" }`

**Example response**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

### GET /auth/me
**Responses**
- `200`: `{ success: true, data: UserResource }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Dhiya",
    "email": "buyer@example.com",
    "role": "buyer",
    "phone": "081234567890",
    "status": "active",
    "email_verified_at": "2026-01-01 00:00:00",
    "created_at": "2026-01-01 00:00:00"
  }
}
```

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

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Pakaian",
      "slug": "pakaian",
      "product_count": 12,
      "created_at": "2026-01-01 00:00:00",
      "updated_at": "2026-01-01 00:00:00"
    }
  ]
}
```

### GET /categories/{slug}
**Responses**
- `200`: `{ success: true, data: CategoryResource }`
- `404`: `{ success: false, message: "Kategori tidak ditemukan." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Pakaian",
    "slug": "pakaian",
    "product_count": 12,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### POST /categories
**Request body**
- `name`: string, required
- `slug`: string, optional; auto-generated from name if empty

**Responses**
- `201`: `{ success: true, message: "Kategori berhasil dibuat.", data: CategoryResource }`

**Example request**
```json
{
  "name": "Pakaian",
  "slug": "pakaian"
}
```

**Example response**
```json
{
  "success": true,
  "message": "Kategori berhasil dibuat.",
  "data": {
    "id": 3,
    "name": "Pakaian",
    "slug": "pakaian",
    "product_count": null,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### PUT /categories/{category}
**Request body**
- `name`: string, optional
- `slug`: string, optional

**Responses**
- `200`: `{ success: true, message: "Kategori berhasil diperbarui.", data: CategoryResource }`

**Example request**
```json
{
  "name": "Pakaian Wanita"
}
```

**Example response**
```json
{
  "success": true,
  "message": "Kategori berhasil diperbarui.",
  "data": {
    "id": 1,
    "name": "Pakaian Wanita",
    "slug": "pakaian-wanita",
    "product_count": 12,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-02 00:00:00"
  }
}
```

### DELETE /categories/{category}
**Responses**
- `200`: `{ success: true, message: "Kategori berhasil dihapus." }`
- `409`: `{ success: false, message: "Kategori tidak dapat dihapus karena masih memiliki produk." }`

**Example response**
```json
{
  "success": true,
  "message": "Kategori berhasil dihapus."
}
```

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

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": "cod",
      "name": "COD",
      "cost": 0,
      "base_cost": 0,
      "estimated_days": null
    },
    {
      "id": "kurir_reguler",
      "name": "Kurir Reguler",
      "cost": 12000,
      "base_cost": 10000,
      "estimated_days": "2-3 hari"
    }
  ]
}
```

### POST /shipping/calculate
**Request body**
- `weight`: number, required, grams
- `shipping_method`: string, required
- `courier_type`: `reguler` | `express`, optional, default `reguler`

**Responses**
- `200`: `{ success: true, data: { shipping_method, courier_type, estimated_cost, estimated_days } }`

**Example request**
```json
{
  "weight": 500,
  "shipping_method": "kurir",
  "courier_type": "reguler"
}
```

**Example response**
```json
{
  "success": true,
  "data": {
    "shipping_method": "kurir",
    "courier_type": "reguler",
    "estimated_cost": 12000,
    "estimated_days": "2-3 hari"
  }
}
```

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

**Example response**
```json
{
  "success": true,
  "data": {
    "total_users": 120,
    "users_by_role": {
      "buyer": 100,
      "seller": 18,
      "admin": 2
    },
    "total_shops": 18,
    "shops_by_status": {
      "pending": 2,
      "verified": 16
    },
    "verified_shops": 16,
    "pending_shops": 2,
    "total_products": 340,
    "monthly_transactions": [
      {
        "month": 1,
        "transactions": 10,
        "revenue": "1000000.00"
      }
    ],
    "platform_revenue": "5000000.00"
  }
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

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "date": "2026-01-01",
      "revenue": "50000.00",
      "orders_count": 5
    },
    {
      "date": "2026-01-02",
      "revenue": "75000.00",
      "orders_count": 8
    }
  ]
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

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Toko Cantik",
      "total_orders": 10,
      "total_revenue": "500000.00"
    }
  ]
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

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kemeja Batik",
      "slug": "kemeja-batik",
      "price": "10000.00",
      "total_qty_sold": 50,
      "total_revenue": "500000.00"
    }
  ]
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

**Example request**
```
GET /api/v1/payments/pending?sort=newest&per_page=15
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_id": 10,
      "amount": 50000,
      "status": "pending",
      "proof_image": "https://example.com/storage/proofs/1.jpg",
      "created_at": "2026-01-01 00:00:00",
      "updated_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### GET /payments/{payment}
**Responses**
- `200`: `{ success: true, data: PaymentResource }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_id": 10,
    "amount": 50000,
    "status": "pending",
    "proof_image": "https://example.com/storage/proofs/1.jpg",
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### PATCH /payments/{payment}/verify
**Responses**
- `200`: `{ success: true, message: "Pembayaran berhasil diverifikasi.", data: OrderResource }`
- `422`: `{ success: false, message: "Pembayaran tidak dalam status pending." }`

**Example response**
```json
{
  "success": true,
  "message": "Pembayaran berhasil diverifikasi.",
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "buyer_id": 1,
    "shop_id": 1,
    "subtotal": 45000,
    "shipping_cost": 5000,
    "total_amount": 50000,
    "shipping_method": "kurir",
    "payment_method": "transfer",
    "status": "paid",
    "shipping_address": "Jl. Mawar No. 1",
    "tracking_number": null,
    "notes": null,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### PATCH /payments/{payment}/reject
**Request body**
- `rejection_reason`: string, optional

**Responses**
- `200`: `{ success: true, message: "Pembayaran berhasil ditolak.", data: PaymentResource }`
- `422`: `{ success: false, message: "Pembayaran tidak dalam status pending." }`

**Example request**
```json
{
  "rejection_reason": "Bukti pembayaran tidak jelas."
}
```

**Example response**
```json
{
  "success": true,
  "message": "Pembayaran berhasil ditolak.",
  "data": {
    "id": 1,
    "order_id": 10,
    "amount": 50000,
    "status": "rejected",
    "proof_image": "https://example.com/storage/proofs/1.jpg",
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

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

**Example request**
```
GET /api/v1/products/pending?sort=newest&per_page=15
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kemeja Batik",
      "slug": "kemeja-batik",
      "description": "Kemeja batik premium",
      "price": "100000.00",
      "cost_price": "60000.00",
      "stock": 10,
      "weight": 300,
      "status": "pending",
      "verified_at": null,
      "rejection_reason": null,
      "shop": {
        "id": 1,
        "name": "Toko Cantik",
        "slug": "toko-cantik",
        "seller": {
          "id": 1,
          "name": "Sari",
          "email": "seller@example.com"
        }
      },
      "category": {
        "id": 1,
        "name": "Pakaian"
      },
      "images": [
        {
          "id": 1,
          "url": "https://example.com/storage/products/1.jpg"
        }
      ],
      "created_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### PATCH /products/{product}/approve
**Responses**
- `200`: `{ success: true, message: "Produk berhasil disetujui.", data: ProductResource }`
- `422`: `{ success: false, message: "Produk tidak dalam status pending." }`

**Example response**
```json
{
  "success": true,
  "message": "Produk berhasil disetujui.",
  "data": {
    "id": 1,
    "name": "Kemeja Batik",
    "slug": "kemeja-batik",
    "status": "active",
    "verified_at": "2026-01-01 00:00:00",
    "rejection_reason": null
  }
}
```

### PATCH /products/{product}/reject
**Request body**
- `rejection_reason`: string, required

**Responses**
- `200`: `{ success: true, message: "Produk berhasil ditolak.", data: ProductResource }`
- `422`: `{ success: false, message: "Produk tidak dalam status pending." }`

**Example request**
```json
{
  "rejection_reason": "Foto produk tidak sesuai."
}
```

**Example response**
```json
{
  "success": true,
  "message": "Produk berhasil ditolak.",
  "data": {
    "id": 1,
    "name": "Kemeja Batik",
    "slug": "kemeja-batik",
    "status": "rejected",
    "verified_at": null,
    "rejection_reason": "Foto produk tidak sesuai."
  }
}
```

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

**Example request**
```
GET /api/v1/shops/pending?sort=newest&per_page=15
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Toko Cantik",
      "slug": "toko-cantik",
      "description": "Toko pakaian wanita",
      "logo": "https://example.com/storage/shops/logo-1.jpg",
      "status": "pending",
      "product_count": 5,
      "created_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### PATCH /shops/{shop}/verify
**Responses**
- `200`: `{ success: true, message: "Toko berhasil diverifikasi.", data: ShopResource }`
- `422`: `{ success: false, message: "Toko tidak dalam status pending." }`

**Example response**
```json
{
  "success": true,
  "message": "Toko berhasil diverifikasi.",
  "data": {
    "id": 1,
    "name": "Toko Cantik",
    "slug": "toko-cantik",
    "status": "verified",
    "verified_at": "2026-01-01 00:00:00",
    "rejection_reason": null
  }
}
```

### PATCH /shops/{shop}/reject
**Request body**
- `rejection_reason`: string, required

**Responses**
- `200`: `{ success: true, message: "Toko berhasil ditolak.", data: ShopResource }`
- `422`: `{ success: false, message: "Toko tidak dalam status pending." }`

**Example request**
```json
{
  "rejection_reason": "Dokumen toko tidak lengkap."
}
```

**Example response**
```json
{
  "success": true,
  "message": "Toko berhasil ditolak.",
  "data": {
    "id": 1,
    "name": "Toko Cantik",
    "slug": "toko-cantik",
    "status": "rejected",
    "verified_at": null,
    "rejection_reason": "Dokumen toko tidak lengkap."
  }
}
```

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

**Example request**
```
GET /api/v1/users?role=seller&status=active&sort=newest&per_page=15
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Sari",
      "email": "seller@example.com",
      "role": "seller",
      "phone": "081234567890",
      "status": "active",
      "email_verified_at": "2026-01-01 00:00:00",
      "created_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### PATCH /users/{user}/suspend
**Responses**
- `200`: `{ success: true, message: "Pengguna berhasil disuspend.", data: UserResource }`
- `422`: `{ success: false, message: "Pengguna sudah dalam status suspended." }`

**Example response**
```json
{
  "success": true,
  "message": "Pengguna berhasil disuspend.",
  "data": {
    "id": 1,
    "name": "Sari",
    "email": "seller@example.com",
    "role": "seller",
    "phone": "081234567890",
    "status": "suspended",
    "email_verified_at": "2026-01-01 00:00:00",
    "created_at": "2026-01-01 00:00:00"
  }
}
```

### PATCH /users/{user}/activate
**Responses**
- `200`: `{ success: true, message: "Pengguna berhasil diaktifkan.", data: UserResource }`
- `422`: `{ success: false, message: "Pengguna tidak dalam status suspended." }`

**Example response**
```json
{
  "success": true,
  "message": "Pengguna berhasil diaktifkan.",
  "data": {
    "id": 1,
    "name": "Sari",
    "email": "seller@example.com",
    "role": "seller",
    "phone": "081234567890",
    "status": "active",
    "email_verified_at": "2026-01-01 00:00:00",
    "created_at": "2026-01-01 00:00:00"
  }
}
```

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

### GET /cart
**Responses**
- `200`: `{ success: true, data: CartResource }`

**CartResource includes**
- `items`: CartItemResource[]
- `subtotal`: number
- `total`: number
- `total_items`: integer
- `groups_by_shop`: array
- `stock_status`: object with `available` and `unavailable_items`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "buyer_id": 1,
    "items": [
      {
        "id": 1,
        "cart_id": 1,
        "product_id": 1,
        "quantity": 2,
        "subtotal": 200000,
        "product": {
          "id": 1,
          "name": "Kemeja Batik",
          "price": "100000.00",
          "stock": 10
        },
        "shop": {
          "id": 1,
          "name": "Toko Cantik",
          "slug": "toko-cantik",
          "logo": "https://example.com/storage/shops/logo-1.jpg"
        },
        "created_at": "2026-01-01 00:00:00",
        "updated_at": "2026-01-01 00:00:00"
      }
    ],
    "subtotal": 200000,
    "total": 212000,
    "total_items": 1,
    "groups_by_shop": [],
    "stock_status": {
      "available": true,
      "unavailable_items": []
    },
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### POST /cart/items
**Request body**
- `product_id`: integer, required
- `quantity`: integer, required, min 1

**Responses**
- `201`: `{ success: true, message: "Barang berhasil ditambahkan ke keranjang.", data: CartResource }`
- `422`: `{ success: false, message: "Jumlah total melebihi stok yang tersedia." }`

**Example request**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Example response**
```json
{
  "success": true,
  "message": "Barang berhasil ditambahkan ke keranjang.",
  "data": {
    "id": 1,
    "buyer_id": 1,
    "items": [],
    "subtotal": 200000,
    "total": 212000,
    "total_items": 1,
    "groups_by_shop": [],
    "stock_status": {
      "available": true,
      "unavailable_items": []
    },
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### PUT /cart/items/{cartItem}
**Request body**
- `quantity`: integer, required

**Responses**
- `200`: `{ success: true, message: "Jumlah barang berhasil diperbarui.", data: CartResource }`
- `403`: `{ success: false, message: "Akses ditolak. Item keranjang ini bukan milik Anda." }`

**Example request**
```json
{
  "quantity": 3
}
```

**Example response**
```json
{
  "success": true,
  "message": "Jumlah barang berhasil diperbarui.",
  "data": {
    "id": 1,
    "buyer_id": 1,
    "items": [],
    "subtotal": 300000,
    "total": 312000,
    "total_items": 1,
    "groups_by_shop": [],
    "stock_status": {
      "available": true,
      "unavailable_items": []
    },
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### DELETE /cart/items/{cartItem}
**Responses**
- `200`: `{ success: true, message: "Barang berhasil dihapus dari keranjang.", data: CartResource }`
- `403`: `{ success: false, message: "Akses ditolak. Item keranjang ini bukan milik Anda." }`

**Example response**
```json
{
  "success": true,
  "message": "Barang berhasil dihapus dari keranjang.",
  "data": {
    "id": 1,
    "buyer_id": 1,
    "items": [],
    "subtotal": 0,
    "total": 0,
    "total_items": 0,
    "groups_by_shop": [],
    "stock_status": {
      "available": true,
      "unavailable_items": []
    },
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### DELETE /cart/clear
**Responses**
- `200`: `{ success: true, message: "Keranjang belanja berhasil dikosongkan.", data: CartResource }`
- `403`: `{ success: false, message: "Akses ditolak." }`

**Example response**
```json
{
  "success": true,
  "message": "Keranjang belanja berhasil dikosongkan.",
  "data": {
    "id": 1,
    "buyer_id": 1,
    "items": [],
    "subtotal": 0,
    "total": 0,
    "total_items": 0,
    "groups_by_shop": [],
    "stock_status": {
      "available": true,
      "unavailable_items": []
    },
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

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
- `cod_location`: string, required if `payment_method=cod`, max 255
- `notes`: string, optional, max 500

**Responses**
- `200`: `{ message: "Checkout successful.", order: OrderResource }`
- `422`: `{ message: "Some items in your cart are out of stock or unavailable.", unavailable_items: [] }`

**Example request**
```json
{
  "cart_items": [1, 2],
  "shipping_method": "kurir",
  "payment_method": "transfer",
  "shipping_address": "Jl. Mawar No. 1",
  "notes": "Pack carefully"
}
```

**Example response**
```json
{
  "message": "Checkout successful.",
  "order": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "buyer_id": 1,
    "shop_id": 1,
    "subtotal": 200000,
    "shipping_cost": 12000,
    "total_amount": 212000,
    "shipping_method": "kurir",
    "payment_method": "transfer",
    "status": "pending",
    "shipping_address": "Jl. Mawar No. 1",
    "tracking_number": null,
    "notes": "Pack carefully",
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

---

## Buyer\CodLocationController
**Path prefix:** `/cod-locations`

| Method | Path | Auth | Description |
| --- | --- | --- | --- |
| GET | `/cod-locations` | yes, buyer | List COD locations |
| POST | `/cod-locations` | yes, buyer | Save COD location |
| PUT | `/cod-locations/{codLocation}` | yes, buyer | Update COD location |
| DELETE | `/cod-locations/{codLocation}` | yes, buyer | Delete COD location |

### GET /cod-locations
**Responses**
- `200`: `{ success: true, data: CodLocationResource[] }`

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "name": "Kantor",
      "address": "Jl. Mawar No. 1",
      "latitude": -6.200000,
      "longitude": 106.816666,
      "is_default": true,
      "created_at": "2026-01-01 00:00:00",
      "updated_at": "2026-01-01 00:00:00"
    }
  ]
}
```

### POST /cod-locations
**Request body**
- `name`: string, required
- `address`: string, required
- `latitude`: number, optional
- `longitude`: number, optional
- `is_default`: boolean, optional

**Responses**
- `201`: `{ success: true, message: "Lokasi COD berhasil disimpan.", data: CodLocationResource }`

**Example request**
```json
{
  "name": "Kantor",
  "address": "Jl. Mawar No. 1",
  "latitude": -6.200000,
  "longitude": 106.816666,
  "is_default": true
}
```

**Example response**
```json
{
  "success": true,
  "message": "Lokasi COD berhasil disimpan.",
  "data": {
    "id": 2,
    "user_id": 1,
    "name": "Kantor",
    "address": "Jl. Mawar No. 1",
    "latitude": -6.200000,
    "longitude": 106.816666,
    "is_default": true,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

### PUT /cod-locations/{codLocation}
**Request body**
- `name`: string, required
- `address`: string, required
- `latitude`: number, optional
- `longitude`: number, optional
- `is_default`: boolean, optional

**Responses**
- `200`: `{ success: true, message: "Lokasi COD berhasil diperbarui.", data: CodLocationResource }`
- `403`: `{ success: false, message: "Akses ditolak." }`

**Example request**
```json
{
  "name": "Rumah",
  "address": "Jl. Melati No. 5",
  "is_default": true
}
```

**Example response**
```json
{
  "success": true,
  "message": "Lokasi COD berhasil diperbarui.",
  "data": {
    "id": 2,
    "user_id": 1,
    "name": "Rumah",
    "address": "Jl. Melati No. 5",
    "latitude": null,
    "longitude": null,
    "is_default": true,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-02 00:00:00"
  }
}
```

### DELETE /cod-locations/{codLocation}
**Responses**
- `200`: `{ success: true, message: "Lokasi COD berhasil dihapus." }`
- `403`: `{ success: false, message: "Akses ditolak." }`

**Example response**
```json
{
  "success": true,
  "message": "Lokasi COD berhasil dihapus."
}
```

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

**Example request**
```
GET /api/v1/orders?status=pending&sort=newest&per_page=15
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "order_number": "ORD-20260101-00001",
      "buyer_id": 1,
      "shop_id": 1,
      "subtotal": 200000,
      "shipping_cost": 12000,
      "total_amount": 212000,
      "shipping_method": "kurir",
      "payment_method": "transfer",
      "status": "pending",
      "shipping_address": "Jl. Mawar No. 1",
      "tracking_number": null,
      "notes": null,
      "created_at": "2026-01-01 00:00:00",
      "updated_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### GET /orders/{order}
**Responses**
- `200`: `{ success: true, data: OrderResource }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "buyer_id": 1,
    "shop_id": 1,
    "subtotal": 200000,
    "shipping_cost": 12000,
    "total_amount": 212000,
    "shipping_method": "kurir",
    "payment_method": "transfer",
    "status": "shipped",
    "shipping_address": "Jl. Mawar No. 1",
    "tracking_number": "SHP123456",
    "notes": null,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-02 00:00:00"
  }
}
```

### PATCH /orders/{order}/confirm
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Pesanan berhasil dikonfirmasi sebagai diterima." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status shipped yang dapat dikonfirmasi." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "status": "received",
    "updated_at": "2026-01-03 00:00:00"
  },
  "message": "Pesanan berhasil dikonfirmasi sebagai diterima."
}
```

### PATCH /orders/{order}/cancel
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Order berhasil dibatalkan. Stok telah dikembalikan dan refund diproses." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status pending yang dapat dibatalkan." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "status": "cancelled",
    "updated_at": "2026-01-02 00:00:00"
  },
  "message": "Order berhasil dibatalkan. Stok telah dikembalikan dan refund diproses."
}
```

### POST /orders/{order}/cod-confirm
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Pembayaran COD berhasil dikonfirmasi. Pesanan sedang diproses." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan metode pembayaran COD yang dapat dikonfirmasi." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "status": "processing",
    "updated_at": "2026-01-02 00:00:00"
  },
  "message": "Pembayaran COD berhasil dikonfirmasi. Pesanan sedang diproses."
}
```

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

**Example request**
```http
POST /api/v1/payments/1/upload
Content-Type: multipart/form-data

proof_image: <file>
```

**Example response**
```json
{
  "success": true,
  "message": "Bukti pembayaran berhasil diunggah.",
  "data": {
    "id": 1,
    "order_id": 10,
    "amount": 50000,
    "status": "paid",
    "proof_image": "https://example.com/storage/proofs/1.jpg",
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-02 00:00:00"
  }
}
```

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

**Example request**
```
GET /api/v1/public/products?category_id=1&sort=price_asc&per_page=12
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kemeja Batik",
      "slug": "kemeja-batik",
      "description": "Kemeja batik premium",
      "price": "100000.00",
      "cost_price": "60000.00",
      "stock": 10,
      "weight": 300,
      "status": "active",
      "verified_at": "2026-01-01 00:00:00",
      "rejection_reason": null,
      "shop": {
        "id": 1,
        "name": "Toko Cantik",
        "slug": "toko-cantik",
        "seller": {
          "id": 1,
          "name": "Sari",
          "email": "seller@example.com"
        }
      },
      "category": {
        "id": 1,
        "name": "Pakaian"
      },
      "images": [
        {
          "id": 1,
          "url": "https://example.com/storage/products/1.jpg"
        }
      ],
      "created_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 12,
    "total": 1,
    "last_page": 1
  }
}
```

### GET /public/products/{slug}
**Responses**
- `200`: `{ success: true, data: ProductResource }`
- `404`: `{ success: false, message: "Produk tidak ditemukan." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Kemeja Batik",
    "slug": "kemeja-batik",
    "description": "Kemeja batik premium",
    "price": "100000.00",
    "cost_price": "60000.00",
    "stock": 10,
    "weight": 300,
    "status": "active",
    "verified_at": "2026-01-01 00:00:00",
    "rejection_reason": null,
    "shop": {
      "id": 1,
      "name": "Toko Cantik",
      "slug": "toko-cantik",
      "seller": {
        "id": 1,
        "name": "Sari",
        "email": "seller@example.com"
      }
    },
    "category": {
      "id": 1,
      "name": "Pakaian"
    },
    "images": [
      {
        "id": 1,
        "url": "https://example.com/storage/products/1.jpg"
      }
    ],
    "created_at": "2026-01-01 00:00:00"
  }
}
```

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

**Example response**
```json
{
  "success": true,
  "data": {
    "total_products": 12,
    "total_orders": 45,
    "total_revenue": 2500000,
    "pending_orders_count": 3
  }
}
```

### GET /seller/analytics/sales
**Query**
- `period`: `daily` | `weekly` | `monthly`, optional, default `daily`
- `start_date`: `Y-m-d`, optional
- `end_date`: `Y-m-d`, optional

**Responses**
- `200`: `{ success: true, data: SalesRow[] }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

**Example request**
```
GET /api/v1/seller/analytics/sales?period=daily&start_date=2026-01-01&end_date=2026-01-31
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "date": "2026-01-01",
      "total_qty_sold": 5,
      "total_revenue": 500000,
      "total_profit": 150000
    }
  ]
}
```

### GET /seller/analytics/products/top
**Responses**
- `200`: `{ success: true, data: SellerTopProduct[] }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kemeja Batik",
      "slug": "kemeja-batik",
      "total_qty_sold": 10,
      "revenue": "1000000.00",
      "profit": "300000.00"
    }
  ]
}
```

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

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kemeja Batik",
      "slug": "kemeja-batik",
      "stock": 3,
      "price": "100000.00",
      "cost_price": "60000.00"
    }
  ]
}
```

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

**Example request**
```
GET /api/v1/seller/orders?status=pending&per_page=15
```

**Example response**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "order_number": "ORD-20260101-00001",
      "status": "pending",
      "total_amount": "75000.00",
      "buyer": {
        "id": 1,
        "name": "Budi",
        "email": "buyer@example.com"
      },
      "items": [
        {
          "id": 1,
          "product_id": 1,
          "quantity": 2,
          "price_snapshot": "25000.00",
          "product": {
            "id": 1,
            "name": "Kemeja Batik",
            "slug": "kemeja-batik"
          }
        }
      ],
      "created_at": "2026-01-01 00:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### GET /seller/orders/{order}
**Responses**
- `200`: `{ success: true, data: OrderResource }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik toko Anda." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "status": "pending",
    "total_amount": "75000.00",
    "buyer": {
      "id": 1,
      "name": "Budi",
      "email": "buyer@example.com"
    },
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "price_snapshot": "25000.00",
        "product": {
          "id": 1,
          "name": "Kemeja Batik",
          "slug": "kemeja-batik"
        }
      }
    ],
    "created_at": "2026-01-01 00:00:00"
  }
}
```

### PATCH /seller/orders/{order}/process
**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Order berhasil diproses." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik toko Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status pending yang dapat diproses." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "status": "processing",
    "updated_at": "2026-01-02 00:00:00"
  },
  "message": "Order berhasil diproses."
}
```

### PATCH /seller/orders/{order}/ship
**Request body**
- `tracking_number`: string, optional

**Responses**
- `200`: `{ success: true, data: OrderResource, message: "Order berhasil dikirim." }`
- `403`: `{ success: false, message: "Akses ditolak. Order ini bukan milik toko Anda." }`
- `422`: `{ success: false, message: "Hanya order dengan status processing yang dapat dikirim." }`

**Example request**
```json
{
  "tracking_number": "JNE-1234567890"
}
```

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "order_number": "ORD-20260101-00001",
    "status": "shipped",
    "updated_at": "2026-01-03 00:00:00"
  },
  "message": "Order berhasil dikirim."
}
```

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

**Example request**
```
POST /api/v1/shops
Content-Type: multipart/form-data

name=Toko Cantik
description=Toko batik premium
logo=<file>
```

**Example response**
```json
{
  "success": true,
  "message": "Toko berhasil dibuat. Menunggu verifikasi admin.",
  "data": {
    "id": 1,
    "name": "Toko Cantik",
    "slug": "toko-cantik",
    "description": "Toko batik premium",
    "logo": "https://example.com/storage/shops/1.jpg",
    "status": "pending",
    "seller": {
      "id": 1,
      "name": "Sari",
      "email": "seller@example.com"
    },
    "created_at": "2026-01-01 00:00:00"
  }
}
```

### GET /shops/my-shop
**Responses**
- `200`: `{ success: true, data: ShopResource }`
- `404`: `{ success: false, message: "Anda belum memiliki toko." }`

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Toko Cantik",
    "slug": "toko-cantik",
    "description": "Toko batik premium",
    "logo": "https://example.com/storage/shops/1.jpg",
    "status": "active",
    "seller": {
      "id": 1,
      "name": "Sari",
      "email": "seller@example.com"
    },
    "created_at": "2026-01-01 00:00:00"
  }
}
```

### PUT /shops/{shop}
**Request body** (`multipart/form-data`)
- shop profile fields accepted by `UpdateShopRequest`
- `logo`: file, optional

**Responses**
- `200`: `{ success: true, message: "Profil toko berhasil diperbarui.", data: ShopResource }`
- `403`: `{ success: false, message: "Akses ditolak. Toko ini bukan milik Anda." }`

**Example request**
```
PUT /api/v1/shops/1
Content-Type: multipart/form-data

description=Toko batik premium updated
logo=<file>
```

**Example response**
```json
{
  "success": true,
  "message": "Profil toko berhasil diperbarui.",
  "data": {
    "id": 1,
    "name": "Toko Cantik",
    "slug": "toko-cantik",
    "description": "Toko batik premium updated",
    "logo": "https://example.com/storage/shops/1.jpg",
    "status": "active",
    "seller": {
      "id": 1,
      "name": "Sari",
      "email": "seller@example.com"
    },
    "created_at": "2026-01-01 00:00:00"
  }
}
```

### GET /shops/{slug}
**Responses**
- `200`: `{ success: true, data: ShopResource }`
- `404`: `{ success: false, message: "Toko tidak ditemukan." }`

**Example request**
```
GET /api/v1/shops/toko-cantik
```

**Example response**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Toko Cantik",
    "slug": "toko-cantik",
    "description": "Toko batik premium",
    "logo": "https://example.com/storage/shops/1.jpg",
    "status": "active",
    "seller": {
      "id": 1,
      "name": "Sari",
      "email": "seller@example.com"
    },
    "product_count": 12,
    "products": [
      {
        "id": 1,
        "name": "Kemeja Batik",
        "slug": "kemeja-batik",
        "price": "100000.00",
        "stock": 10
      }
    ],
    "created_at": "2026-01-01 00:00:00"
  }
}
```

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
  "items": CartItemResource[] | null,
  "subtotal": "number | null",
  "total": "number | null",
  "total_items": "integer | null",
  "groups_by_shop": "array",
  "stock_status": {
    "available": "boolean",
    "unavailable_items": []
  },
  "created_at": "Y-m-d H:i:s | null",
  "updated_at": "Y-m-d H:i:s | null"
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
  "shop": {
    "id": 1,
    "name": "string",
    "slug": "string",
    "logo": "string | null"
  } | null,
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
