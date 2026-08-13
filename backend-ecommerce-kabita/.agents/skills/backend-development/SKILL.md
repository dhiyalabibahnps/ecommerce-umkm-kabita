---
name: backend-development
description: These skills are used to develop PHP Laravel backends quickly and efficiently. Use this whenever a user requests the creation of an API, database connection, or database schema, or asks for backend code fixes.
---

<!-- Tip: Use /create-skill in chat to generate content with agent assistance -->

# BACKEND DEVELOPMENT GUIDE - KABITA E-COMMERCE

## 1. Tech Stack & Environment
- **Language:** PHP 8.5 (Strict Typing `declare(strict_types=1);` in every file).
- **Framework:** Laravel 13 (RESTful API).
- **Database:** MariaDB (InnoDB Engine).
- **Authentication:** Laravel Sanctum (API Token).
- **Methodology:** Waterfall (Implementation & Testing Phase).

## 2. Architecture & Design Decisions (Crucial for Thesis Chapter 4)
### A. Strict Typing with PHP Enums
- Avoid using *magic strings* (e.g., `'admin'`, `'active'`) for statuses/roles.
- Use **PHP 8.1+ Backed Enums** (`App\Enums\*`).
- Casting is handled in the Model via `protected function casts(): array`.
- *Benefit:* Prevents typo bugs, makes code *self-documenting*, and is favored by examiners for implementing modern *best practices*.

### B. Data Integrity (Price Snapshots)
- The `order_items` table stores `price_snapshot` and `cost_snapshot`.
- *Benefit:* If an admin/seller changes product prices in the future, historical sales reports and profit data remain unchanged (historical accuracy is preserved).

### C. Performance Optimization (Materialized View)
- The `daily_product_sales` table is used for the **Product Portfolio Analytics Dashboard**.
- Data is aggregated (SUM qty, revenue, profit) via a Laravel Command/Scheduler daily.
- *Benefit:* Dashboard queries become extremely fast (O(1)) and do not burden the database with real-time `SUM()` and `GROUP BY` operations on millions of `order_items` rows.

## 3. Folder Structure & Naming Conventions
### Controllers & API
- Location: `app/Http/Controllers/Api/V1/`
- Naming: `ProductController.php`, `OrderController.php` (Singular).
- Routing: Defined in `routes/api.php` with the `v1` prefix.

### Validation & Resources
- **Form Requests:** `app/Http/Requests/` (Example: `StoreProductRequest.php`). *Mandatory usage; do not validate inside the Controller.*
- **API Resources:** `app/Http/Resources/` (Example: `ProductResource.php`). *Used to format JSON responses before sending them to the Frontend.*

### Business Logic (Services)
- Location: `app/Services/`
- Used for complex logic involving multiple models (Example: `OrderProcessingService.php` to handle checkout, reduce stock, and create orders simultaneously).

## 4. API Response Standards
Always use a consistent JSON format to ensure easy parsing by Vue.js (Axios):

**Success Response:**
```json
{
  "success": true,
  "message": "Product added successfully",
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 5 } 
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": { "name": ["The product name field is required."] }
}
```
*Tip: Use Laravel's built-in `ApiResponderTrait` or `JsonResource` to standardize this.*

## 5. Database Conventions & Migrations
- **Table Names:** Plural, snake_case (Example: `order_items`, `daily_product_sales`).
- **Foreign Keys:** Always use `foreignId('shop_id')->constrained()->cascadeOnDelete();` to maintain relational integrity.
- **Indexing:** Add `->index()` to columns that are frequently queried or filtered (especially `shop_id` and `date` in the `daily_product_sales` table for analytics).
- **Timestamps:** Always include `$table->timestamps();` except for pure pivot tables.

## 6. Security (Security Checklist)
- [ ] **Mass Assignment:** Always define `$fillable` in Models; never use `$guarded = []`.
- [ ] **Passwords:** Use `Hash::make()` and the `'hashed'` cast in the Model.
- [ ] **Authorization:** Use **Policies** (`app/Policies/`) to ensure Seller A cannot edit/delete products belonging to Seller B.
- [ ] **Middleware:** Use Middleware to restrict access based on Role (`admin`, `seller`, `buyer`).

## 7. Development Workflow (Backend)
1. Create Migration -> 2. Create Model + Enum -> 3. Create Form Request -> 4. Create Controller -> 5. Create API Resource -> 6. Test via Postman/Insomnia -> 7. Document in Swagger/Scribe.

### Supervisor's Notes:
1.  **Focus on "Design Decisions":** Point number 2 in this document is "gold" for your thesis. During the defense, if an examiner asks *"Why did you create the `daily_product_sales` table? Why not query directly from `order_items`?"*, you can simply refer to this document: *"To implement the Materialized View concept so that the Analytics Dashboard performance remains optimal even when transaction data reaches thousands of records, Sir/Ma'am."*
2.  **API Standardization:** Point number 4 is crucial. The Frontend (Vue.js) will be much easier to build if the Backend always returns the same JSON structure.
3.  **Security:** Point number 6 demonstrates that you are building a *production-ready* application, not just a typical student project.