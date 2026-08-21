// =====================================================
// KABITA E-COMMERCE - PHP ENUM → TYPESCRIPT TYPE ALIASES
// Converted from: backend/app/Enums/
// =====================================================

/** From App\Enums\UserRole */
export type UserRole = 'admin' | 'seller' | 'buyer';

/** From App\Enums\UserStatus */
export type UserStatus = 'active' | 'inactive' | 'suspended';

/** From App\Enums\ProductStatus */
export type ProductStatus = 'pending' | 'approved' | 'rejected' | 'active';

/** From App\Enums\OrderStatus */
export type OrderStatus = 'awaiting_verification' | 'processing' | 'packed' | 'shipped' | 'cod_meeting' | 'completed' | 'cancelled';

/** From App\Enums\PaymentStatus */
export type PaymentStatus = 'pending' | 'verified' | 'rejected';

/** From App\Enums\ShopStatus */
export type ShopStatus = 'pending' | 'verified' | 'rejected';

/** From App\Enums\Courier */
export type CourierCode = 'JNE' | 'JNT' | 'SICEPAT' | 'ANTERAJA' | 'POS' | 'NINJA';
