import type { ShippingCalculateRequest, ShippingCalculation, ShippingOption } from '../types';
import apiClient from './apiClient';

export const shippingService = {
  async listOptions(): Promise<ShippingOption[]> {
    const response = await apiClient.get('/shipping/options');
    return response.data.data;
  },

  async calculate(data: ShippingCalculateRequest): Promise<ShippingCalculation> {
    const response = await apiClient.post('/shipping/calculate', data);
    return response.data.data;
  },
};
