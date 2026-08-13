# API Documentation - Kabita E-Commerce

This documentation covers the Scramble/OpenAPI setup for the Kabita E-Commerce backend API.

## Table of Contents

- [Overview](#overview)
- [Accessing Documentation](#accessing-documentation)
- [Authentication](#authentication)
- [API Response Format](#api-response-format)
- [Error Handling](#error-handling)
- [API Groups/Tabs](#api-groupstabs)
- [Scramble Configuration](#scramble-configuration)

---

## Overview

The Kabita E-Commerce API is documented using **[Scramble](https://scramble.dedoc.co)**, an OpenAPI 3.1 documentation generator for Laravel. Scramble automatically generates documentation from your controllers, routes, and PHPDoc annotations.

### Key Features

- **Automatic Documentation**: Generated from route definitions and controller methods
- **Interactive UI**: Swagger UI for testing endpoints directly in the browser
- **OpenAPI 3.1 Compliance**: Standard format compatible with Postman, Insomnia, etc.
- **Type Safety**: Request validation via FormRequests is reflected in the schema
- **Security Schemes**: Bearer token authentication documented automatically

---

## Accessing Documentation

### Local Development

```
http://localhost:8000/docs/api
```

### OpenAPI JSON Specification

```
http://localhost:8000/docs/api.json
```

### Production

Access is restricted by default. Configure the `viewApiDocs` gate in your application to allow access.

---

## Authentication

All protected endpoints require Bearer token authentication via Laravel Sanctum.

### Obtaining a Token

**POST** `/api/v1/auth/login`

```json
{
  "email": "user@example.com",
  "password": "secret",
  "remember": false
}
```

**Response:**

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {},
    "token": "1|abc123..."
  }
}
```

### Using the Token

Include the token in the `Authorization` header:

```
Authorization: Bearer {token}
```

Or use the Swagger UI "Authorize" button to set your token globally.

---

## API Response Format

All responses follow a consistent JSON structure:

### Success Response

```json
{
  "success": true,
  "message": "Operation successful.",
  "data": {},
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description."
}
```

### Pagination Meta

 paginated responses include a `meta` object with pagination information.

---

## Error Handling

| HTTP Status | Meaning | Example Message |
|-------------|---------|-----------------|
| `400` | Bad Request | Invalid verification code |
| `401` | Unauthorized | Email or password is wrong |
| `403` | Forbidden | Access denied |
| `404` | Not Found | Resource not found |
| `409` | Conflict | Resource already exists |
| `422` | Unprocessable Entity | Validation failed |
| `500` | Internal Server Error | Something went wrong |

---

## API Groups/Tabs

The API is organized into the following groups in the documentation:

### Auth
User authentication & registration - Login, register, and email verification

### Shop
Shop management for sellers - Create, update, and view shop profiles

### Category
Product categories management - Manage product categories

### Product
Product catalog & management - Browse, verify, and manage products

### Cart
Shopping cart operations - Add, update, and manage cart items

### Checkout
Checkout process - Create orders from cart items

### Order
Order management - View, process, and track orders

### Payment
Payment operations - Upload proof and verify payments

### Shipping
Shipping options & calculation - Get shipping rates and methods

### CodLocation
Cash on delivery locations - Manage COD delivery addresses

### Analytics
Business analytics & reports - Sales and platform statistics

### User
User management - Admin user operations

---

## Scramble Configuration

### Config File

Configuration is located at `config/scramble.php`:

```php
return [
    'controllers_path' => [
        app_path('Http/Controllers/Api'),
    ],
    'routes' => ['api'],
    'docs_path' => '/docs/api',
    'title' => 'Kabita E-Commerce API',
    'version' => '1.0.0',
    'description' => 'API Documentation for Kabita UMKM E-Commerce Platform',
];
```

### Custom Extensions

#### AutoGroupTransformer

Located at `app/Extensions/AutoGroupTransformer.php`, this transformer adds descriptions to each tag group in the OpenAPI document.

#### ScrambleExtensionProvider

Located at `app/Providers/ScrambleExtensionProvider.php`, this provider:
- Registers the `AutoGroupTransformer`
- Configures Bearer token security scheme

### Adding New Tags

To add a new tag description, update the `$tagDescriptions` array in `AutoGroupTransformer`:

```php
protected array $tagDescriptions = [
    'NewGroup' => 'Description for the new group',
    // ... existing tags
];
```

---

## Development Notes

### PHPDoc Annotations

The documentation uses standard PHPDoc annotations that Scramble recognizes:

- `@group` - Assigns endpoint to a documentation group
- `@tag` - Assigns OpenAPI tag
- `@authenticated` - Marks endpoint as requiring authentication
- `@unauthenticated` - Marks endpoint as public
- `@response` - Documents expected response schema
- `@requestBody` - Indicates request body is required
- `@query_param` - Documents query parameters

### Regenerating Documentation

After making changes to controllers or routes, regenerate the OpenAPI spec:

```bash
php artisan scramble:docs
```

Then clear config cache:

```bash
php artisan config:clear
```

---

## Related Documentation

- [README.md](../README.md) - Project overview and setup
- [Backend Conventions](../memories/repo/backend-conventions.md) - Code style guidelines
- [Order Status Flow](../memories/repo/order-status-flow.md) - Order lifecycle documentation
