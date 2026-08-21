// =====================================================
// KABITA E-COMMERCE - TYPESCRIPT TYPE BARREL EXPORTS
// All types are sourced from PHP Models, Enums & API Resources
// =====================================================

// ─────────────────────────────────────────────────────
// ENUM TYPES (from backend/app/Enums/)
// ─────────────────────────────────────────────────────
export type {
    OrderStatus,
    PaymentStatus, ProductStatus, ShopStatus, UserRole,
    UserStatus
} from './enums';

// ─────────────────────────────────────────────────────
// REQUEST / RESPONSE SCHEMAS (from backend/api.json)
// ─────────────────────────────────────────────────────
export type {
    // Cart
    AddToCartRequest, CheckoutRequest,
    // Category
    CreateCategoryRequest,
    // Shop
    CreateShopRequest,
    // Auth
    LoginRequest,
    // Generic Responses
    MessageResponse, RegisterRequest, RejectPaymentRequest,
    // Product
    RejectProductRequest, RejectShopRequest,
    // Order
    ShipOrderRequest,
    // Shipping
    ShippingCalculateRequest, ShippingCalculation, ShippingOption,
    // COD Location
    StoreLocationRequest as StoreCodLocationRequest, StoreProductBulkRequest,
    StoreProductInput,
    // Admin
    SuspendUserRequest, UpdateCartItemRequest, UpdateCategoryRequest, UpdateLocationRequest, UpdateProductRequest, UpdateProfileRequest, UpdateShopRequest,
    // Payment
    UploadPaymentRequest, VerifyEmailRequest
} from './schemas';

// ─────────────────────────────────────────────────────
// DOMAIN ENTITIES (from backend/app/Models/ + Resources)
// ─────────────────────────────────────────────────────
export type {
    // Analytics
    AnalyticsSalesRow, Cart, CartGroupByShop, CartItem, CartStockStatus, Category, CodLocation,
    DailyProductSales,
    EmailVerificationCode, LowStockProduct, Order,
    OrderItem,
    Payment, PlatformStats, Product,
    ProductImage, SalesRow, SellerOverview, SellerTopProduct, Shop, TopProduct, TopSeller,
    ChatMessage, Conversation, AppNotification,
    // Core entities
    User
} from './entities';

// ─────────────────────────────────────────────────────
// UTILITY TYPES
// ─────────────────────────────────────────────────────
export type {
    ApiError, ApiResponse, AuthResponse, PaginatedResponse,
    PaginationMeta, SingleResponse
} from './utils';

