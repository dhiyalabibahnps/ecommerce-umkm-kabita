import type { StoreLocationRequest, UpdateLocationRequest } from '@/types/schemas';
import type { ApiResponse, CodLocation, MessageResponse, PaginatedResponse } from '../types';
import apiClient from './apiClient';

export const locationService = {
  async list(): Promise<PaginatedResponse<CodLocation>> {
    const response = await apiClient.get('/locations');
    return response.data;
  },

  async create(data: StoreLocationRequest): Promise<ApiResponse<CodLocation>> {
    const response = await apiClient.post('/locations', data);
    return response.data;
  },

  async update(locationId: number, data: UpdateLocationRequest): Promise<ApiResponse<CodLocation>> {
    const response = await apiClient.put(`/locations/${locationId}`, data);
    return response.data;
  },

  async delete(locationId: number): Promise<MessageResponse> {
    const response = await apiClient.delete(`/locations/${locationId}`);
    return response.data;
  },

  async setDefault(locationId: number): Promise<ApiResponse<CodLocation>> {
    const response = await apiClient.patch(`/locations/${locationId}/default`);
    return response.data;
  },
};
