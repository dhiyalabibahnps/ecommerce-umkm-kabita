import type {
  LowStockProduct,
  SalesRow,
  SellerOverview,
  SellerTopProduct,
} from '../types';
import apiClient from './apiClient';

export const sellerAnalyticsService = {
  async getOverview(): Promise<SellerOverview> {
    const response = await apiClient.get('/seller/analytics/overview');
    return response.data.data;
  },

  async getSales(filters?: {
    period?: 'daily' | 'weekly' | 'monthly';
    start_date?: string;
    end_date?: string;
  }): Promise<SalesRow[]> {
    const response = await apiClient.get('/seller/analytics/sales', { params: filters });
    return response.data.data;
  },

  async getTopProducts(): Promise<SellerTopProduct[]> {
    const response = await apiClient.get('/seller/analytics/products/top');
    return response.data.data;
  },

  async getLowStockProducts(): Promise<LowStockProduct[]> {
    const response = await apiClient.get('/seller/analytics/products/low-stock');
    return response.data.data;
  },
};
