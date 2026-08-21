import type { AppNotification } from '@/types';
import apiClient from './apiClient';

export interface NotificationResponse {
  data: AppNotification[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    unread_count: number;
  };
}

export const notificationService = {
  async getNotifications(params?: { type?: string; unread_only?: boolean; limit?: number }): Promise<NotificationResponse> {
    const response = await apiClient.get('/notifications', { params });
    return {
      data: response.data.data,
      meta: response.data.meta,
    };
  },

  async list(params?: { type?: string; unread_only?: boolean; limit?: number }): Promise<NotificationResponse> {
    return this.getNotifications(params);
  },

  async markAsRead(notificationId: number): Promise<{ success: boolean; unread_count: number }> {
    const response = await apiClient.put(`/notifications/${notificationId}/read`);
    return response.data;
  },

  async markAllAsRead(): Promise<{ success: boolean; unread_count: number }> {
    const response = await apiClient.put('/notifications/read-all');
    return response.data;
  },
};
