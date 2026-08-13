import type { Order, PaginationMeta } from '../types';
import apiClient from './apiClient';

export const buyerOrderService = {
  async list(filters?: {
    status?: string;
    start_date?: string;
    end_date?: string;
    sort?: 'newest' | 'oldest' | 'total_asc' | 'total_desc';
    per_page?: number;
  }): Promise<{ data: Order[]; meta: PaginationMeta }> {
    const response = await apiClient.get('/orders', { params: filters });
    return { data: response.data.data, meta: response.data.meta };
  },

  async getDetail(orderId: number): Promise<Order> {
    const response = await apiClient.get(`/orders/${orderId}`);
    return response.data.data;
  },

  async confirmReceived(orderId: number): Promise<Order> {
    const response = await apiClient.patch(`/orders/${orderId}/confirm`);
    return response.data.data;
  },

  async cancel(orderId: number): Promise<Order> {
    const response = await apiClient.patch(`/orders/${orderId}/cancel`);
    return response.data.data;
  },

  async confirmCodPayment(orderId: number): Promise<Order> {
    const response = await apiClient.post(`/orders/${orderId}/cod-confirm`);
    return response.data.data;
  },
};
