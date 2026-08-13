import type { PaginationMeta, Product, RejectProductRequest } from '../types';
import apiClient from './apiClient';

export const adminProductService = {
  async listPending(filters?: {
    shop_id?: number;
    category_id?: number;
    search?: string;
    sort?: 'newest' | 'oldest';
    per_page?: number;
  }): Promise<{ data: Product[]; meta: PaginationMeta }> {
    const response = await apiClient.get('/products/pending', { params: filters });
    return { data: response.data.data, meta: response.data.meta };
  },

  async approve(productId: number): Promise<Product> {
    const response = await apiClient.patch(`/products/${productId}/approve`);
    return response.data.data;
  },

  async reject(productId: number, data: RejectProductRequest): Promise<Product> {
    const response = await apiClient.patch(`/products/${productId}/reject`, data);
    return response.data.data;
  },
};
