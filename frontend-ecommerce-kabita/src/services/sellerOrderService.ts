import type { Order, PaginationMeta, ShipOrderRequest } from '../types';
import apiClient from './apiClient';

export const sellerOrderService = {
  async list(filters?: {
    status?: string;
    start_date?: string;
    end_date?: string;
    sort?: 'newest' | 'oldest' | 'total_asc' | 'total_desc';
    per_page?: number;
  }): Promise<{ data: Order[]; meta: PaginationMeta }> {
    const response = await apiClient.get('/seller/orders', { params: filters });
    return { data: response.data.data, meta: response.data.meta };
  },

  async getDetail(orderId: number): Promise<Order> {
    const response = await apiClient.get(`/seller/orders/${orderId}`);
    return response.data.data;
  },

  async process(orderId: number): Promise<Order> {
    const response = await apiClient.patch(`/seller/orders/${orderId}/process`);
    return response.data.data;
  },

  async ship(orderId: number, data: ShipOrderRequest): Promise<Order> {
    const response = await apiClient.patch(`/seller/orders/${orderId}/ship`, data);
    return response.data.data;
  },
};
