import type { Conversation, ChatMessage } from '@/types';
import apiClient from './apiClient';

export const chatService = {
  async getConversationById(conversationId: number): Promise<Conversation> {
    const response = await apiClient.get(`/chat/conversations/${conversationId}`);
    return response.data.data;
  },

  async sendMessageByConversationId(conversationId: number, message: string): Promise<ChatMessage> {
    const response = await apiClient.post(`/chat/conversations/${conversationId}/messages`, {
      message,
    });
    return response.data.data;
  },

  async getOrderConversation(orderId: number): Promise<Conversation> {
    const response = await apiClient.get(`/chat/orders/${orderId}`);
    return response.data.data;
  },

  async sendOrderMessage(orderId: number, message: string): Promise<ChatMessage> {
    const response = await apiClient.post(`/chat/orders/${orderId}/messages`, {
      message,
    });
    return response.data.data;
  },

  async getShopConversation(shopId: number): Promise<Conversation> {
    const response = await apiClient.get(`/chat/shops/${shopId}`);
    return response.data.data;
  },

  async sendShopMessage(shopId: number, message: string, productName?: string): Promise<ChatMessage> {
    const response = await apiClient.post(`/chat/shops/${shopId}/messages`, {
      message,
      product_name: productName,
    });
    return response.data.data;
  },
};
