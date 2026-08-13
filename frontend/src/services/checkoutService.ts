import type { CheckoutRequest, Order } from '../types';
import apiClient from './apiClient';

export const checkoutService = {
  async checkout(data: CheckoutRequest): Promise<Order> {
    const response = await apiClient.post('/checkout', data);
    return response.data.order;
  },
};
