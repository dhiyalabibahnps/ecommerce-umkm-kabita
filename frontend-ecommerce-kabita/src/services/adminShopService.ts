import type { PaginationMeta, RejectShopRequest, Shop } from '../types';
import apiClient from './apiClient';

export const adminShopService = {
  async listPending(filters?: {
    search?: string;
    sort?: 'newest' | 'oldest';
    per_page?: number;
  }): Promise<{ data: Shop[]; meta: PaginationMeta }> {
    const response = await apiClient.get('/shops/pending', { params: filters });
    return { data: response.data.data, meta: response.data.meta };
  },

  async verify(shopId: number): Promise<Shop> {
    const response = await apiClient.patch(`/shops/${shopId}/verify`);
    return response.data.data;
  },

  async reject(shopId: number, data: RejectShopRequest): Promise<Shop> {
    const response = await apiClient.patch(`/shops/${shopId}/reject`, data);
    return response.data.data;
  },
};
