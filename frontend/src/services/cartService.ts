import type { AddToCartRequest, Cart, MessageResponse, PaginatedResponse, UpdateCartItemRequest } from '../types';
import apiClient from './apiClient';

export const cartService = {
  async get(): Promise<Cart> {
    const response = await apiClient.get('/cart');
    return response.data.data;
  },

  async addItem(data: AddToCartRequest): Promise<PaginatedResponse<Cart>> {
    const response = await apiClient.post<PaginatedResponse<Cart>>('/cart/items', data);
    return response.data;
  },

  async updateItem(cartItemId: number, data: UpdateCartItemRequest): Promise<MessageResponse> {
    const response = await apiClient.put(`/cart/items/${cartItemId}`, data);
    return response.data;
  },

  async removeItem(cartItemId: number): Promise<MessageResponse> {
    const response = await apiClient.delete(`/cart/items/${cartItemId}`);
    return response.data;
  },

  async clear(): Promise<MessageResponse> {
    const response = await apiClient.delete('/cart/clear');
    return response.data;
  },
};
