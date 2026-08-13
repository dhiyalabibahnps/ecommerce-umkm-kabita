import type { PaginationMeta, Payment, RejectPaymentRequest } from '../types';
import apiClient from './apiClient';

export const adminPaymentService = {
  async listPending(filters?: {
    shop_id?: number;
    buyer_id?: number;
    search?: string;
    sort?: 'newest' | 'oldest';
    per_page?: number;
  }): Promise<{ data: Payment[]; meta: PaginationMeta }> {
    const response = await apiClient.get('/payments/pending', { params: filters });
    return { data: response.data.data, meta: response.data.meta };
  },

  async getDetail(paymentId: number): Promise<Payment> {
    const response = await apiClient.get(`/payments/${paymentId}`);
    return response.data.data;
  },

  async verify(paymentId: number): Promise<Payment> {
    const response = await apiClient.patch(`/payments/${paymentId}/verify`);
    return response.data.data;
  },

  async reject(paymentId: number, data: RejectPaymentRequest): Promise<Payment> {
    const response = await apiClient.patch(`/payments/${paymentId}/reject`, data);
    return response.data.data;
  },
};
