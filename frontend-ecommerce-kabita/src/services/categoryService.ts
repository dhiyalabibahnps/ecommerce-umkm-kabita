import type { Category, CreateCategoryRequest, PaginatedResponse, UpdateCategoryRequest } from '../types';
import apiClient from './apiClient';

export const categoryService = {
  async list(): Promise<PaginatedResponse<Category>> {
    const response = await apiClient.get('/categories');
    return response.data.data;
  },

  async getBySlug(slug: string): Promise<PaginatedResponse<Category>> {
    const response = await apiClient.get<PaginatedResponse<Category>>(`/categories/${slug}`);
    return response.data;
  },

  async create(data: CreateCategoryRequest): Promise<PaginatedResponse<Category>> {
    const response = await apiClient.post<PaginatedResponse<Category>>('/categories', data);
    return response.data;
  },

  async update(slug: string, data: UpdateCategoryRequest): Promise<PaginatedResponse<Category>> {
    const response = await apiClient.put<PaginatedResponse<Category>>(`/categories/${slug}`, data);
    return response.data;
  },

  async delete(slug: string): Promise<void> {
    await apiClient.delete(`/categories/${slug}`);
  },
};
