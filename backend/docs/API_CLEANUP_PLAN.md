# Task: Refine Laravel API Documentation (OpenAPI 3.1)

## Context
The current `api.json` (OpenAPI 3.1) has inconsistent titles, missing/incorrect response schemas (many endpoints return `"type": "integer", "const": 200` instead of actual JSON structures), and no realistic response examples. This breaks the Stoplight/Swagger UI preview and makes the docs unusable for frontend integration.

## Goal
Produce a cleaned, accurate, and developer-friendly OpenAPI spec where:
1. Every endpoint has a clear, descriptive `summary` and `operationId`.
2. Every `200` response returns a properly typed JSON schema matching the real Laravel API output (not just `integer const 200`).
3. Every endpoint includes a realistic `example` value in the response so the docs UI renders meaningful sample data.
4. Reusable components (`schemas`, `responses`) are leveraged to avoid duplication.
5. No hallucinated fields — only use fields confirmed by the database schema and existing controller logic.

## Reference Sources (DO NOT invent fields beyond these)
- Database relations: `users`, `shops`, `products`, `categories`, `carts`, `cart_items`, `orders`, `order_items`, `payments`, `cod_locations`, `daily_product_sales`.
- Existing schemas already defined in `components/schemas`: `User`, `UserRole`, `UserStatus`, `PaymentResource`, `PaymentStatus`, plus all `*Request` schemas.
- Business rules from the flow diagram: email verification, shop verification, product approval, checkout (COD/kurir), payment proof upload & admin verification, order status lifecycle (pending → processing → shipped → delivered / cancelled).

## Step-by-Step Plan

### Step 1 — Audit & Inventory
- [ ] List every path in `paths` and mark which ones currently have a broken response schema (`integer const 200`).
- [ ] Group endpoints by tag: Auth, User, Shop, Category, Product, Cart, Checkout, Order, Payment, Shipping, CodLocation, Analytics.

### Step 2 — Define Missing Response Schemas in `components/schemas`
Create reusable resource schemas based on the DB + existing request schemas. Required new schemas:
- [ ] `Category` { id, name, slug, created_at, updated_at }
- [ ] `Shop` { id, seller_id, name, slug, description, logo, status, verified_at, created_at, updated_at }
- [ ] `Product` { id, shop_id, category_id, name, slug, description, price, cost_price, stock, weight, status, verified_at, created_at, updated_at }
- [ ] `CartItem` { id, cart_id, product_id, quantity, product: Product }
- [ ] `Cart` { id, buyer_id, items: CartItem[], subtotal, created_at, updated_at }
- [ ] `OrderItem` { id, order_id, product_id, quantity, price_snapshot, cost_snapshot, product?: Product }
- [ ] `Order` { id, order_number, buyer_id, shop_id, subtotal, shipping_cost, total_amount, shipping_method, payment_method, status, shipping_address, tracking_number, notes, items: OrderItem[], created_at, updated_at }
- [ ] `CodLocation` { id, name, address, latitude, longitude, is_default, created_at, updated_at }
- [ ] `ShippingOption` / `ShippingCalculation` { method, courier_type, cost, estimated_days }
- [ ] `AnalyticsSalesRow` { date, revenue, orders_count }  (already inline — extract)
- [ ] `TopSeller` { id, name, total_orders, total_revenue }  (already inline — extract)
- [ ] `TopProduct` { id, name, slug, price, total_qty_sold, total_revenue }  (already inline — extract)
- [ ] `SellerTopProduct` { id, name, slug, total_qty_sold, revenue, profit }  (already inline — extract)
- [ ] `LowStockProduct` { id, name, slug, stock, price, cost_price }  (already inline — extract)
- [ ] `PlatformStats` { total_users, total_sellers, total_products, total_orders, total_revenue }
- [ ] `SellerOverview` { total_products, total_orders, total_revenue, total_profit, low_stock_count }
- [ ] `PaginatedResponse<T>` wrapper { success, data: T[], meta: { current_page, last_page, per_page, total } }
- [ ] `SingleResponse<T>` wrapper { success, data: T }
- [ ] `MessageResponse` { success, message }

### Step 3 — Fix Each Endpoint Response Schema
For every path, replace the placeholder `"type": "integer", "const": 200` with the correct wrapper:
- [ ] List endpoints → `PaginatedResponse<Resource>` or `SingleResponse<Resource[]>`
- [ ] Show/detail endpoints → `SingleResponse<Resource>`
- [ ] Create/Update/Delete/Action endpoints → `SingleResponse<Resource>` or `MessageResponse`
- [ ] Keep existing `401`, `403`, `404`, `422` `$ref` responses intact.

Mapping reference:
| Endpoint | Success Schema |
|---|---|
| GET /v1/me | SingleResponse<User> |
| POST /v1/auth/login, register, verify-email, resend-code, logout | MessageResponse (+ token on login/register) |
| GET /v1/categories | SingleResponse<Category[]> |
| POST/PUT /v1/categories | SingleResponse<Category> |
| GET /v1/categories/{slug} | SingleResponse<Category> |
| GET /v1/public/products | PaginatedResponse<Product> |
| GET /v1/public/products/{slug} | SingleResponse<Product> |
| GET /v1/products/pending, PATCH approve/reject | PaginatedResponse<Product> / MessageResponse |
| POST /v1/shops, GET my-shop, PUT /v1/shops/{shop} | SingleResponse<Shop> |
| GET /v1/shops/{slug}, pending, verify, reject | SingleResponse<Shop> / MessageResponse |
| GET/POST/PUT/DELETE /v1/cart* | SingleResponse<Cart> / MessageResponse |
| POST /v1/checkout | SingleResponse<Order> |
| GET /v1/orders, /v1/seller/orders | PaginatedResponse<Order> |
| GET /v1/orders/{order}, seller show | SingleResponse<Order> |
| PATCH confirm/cancel/process/ship, POST cod-confirm | SingleResponse<Order> / MessageResponse |
| POST /v1/payments/{id}/upload | SingleResponse<PaymentResource> |
| GET /v1/payments/pending, show, verify, reject | PaginatedResponse<PaymentResource> / SingleResponse<PaymentResource> |
| GET/POST /v1/shipping/options, calculate | SingleResponse<ShippingOption[]> / SingleResponse<ShippingCalculation> |
| GET/POST/PUT/DELETE /v1/cod-locations | SingleResponse<CodLocation[]> / SingleResponse<CodLocation> / MessageResponse |
| GET /v1/users, suspend, activate | PaginatedResponse<User> / MessageResponse |
| GET /v1/analytics/* | SingleResponse<PlatformStats / AnalyticsSalesRow[] / TopSeller[] / TopProduct[]> |
| GET /v1/seller/analytics/* | SingleResponse<SellerOverview / AnalyticsSalesRow[] / SellerTopProduct[] / LowStockProduct[]> |

### Step 4 — Add Realistic Examples
For every `200` response, add an `examples` block with one realistic sample using Indonesian-context dummy data (Rupiah, local names). Example format:
```json
"examples": {
  "default": {
    "value": {
      "success": true,
      "data": { ... }
    }
  }
}