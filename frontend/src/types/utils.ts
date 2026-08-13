// =====================================================
// KABITA E-COMMERCE - UTILITY & WRAPPER TYPES
// Converted from: backend/api.json (PaginatedResponse, SingleResponse, MessageResponse patterns)
// =====================================================

// ─────────────────────────────────────────────────────
// GENERIC API RESPONSE WRAPPERS
// ─────────────────────────────────────────────────────

/** Standard paginated response matching api.json PaginatedResponse schema */
export interface PaginatedResponse<T> {
  success: boolean;
  message?: string;
  data: T[];
  meta: PaginationMeta;
}

/** Pagination metadata returned by list endpoints */
export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

/** Single-item response matching api.json SingleResponse schema */
export interface SingleResponse<T> {
  success: boolean;
  data: T;
}

/** Flexible response wrapper used throughout the app */
export interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
  meta?: PaginationMeta;
}

/** Auth login/register response */
export interface AuthResponse {
  user: import('./entities').User;
  token: string;
}

/** Validation error response */
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
