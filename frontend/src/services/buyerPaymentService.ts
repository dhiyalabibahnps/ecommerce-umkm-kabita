import type { Payment, UploadPaymentRequest } from '../types';
import apiClient from './apiClient';

export interface PaymentSettingData {
  id: number;
  bank_name: string;
  account_number: string;
  account_holder_name: string;
  is_active: boolean;
}

export const buyerPaymentService = {
  async getPaymentSettings(): Promise<PaymentSettingData | null> {
    const response = await apiClient.get('/buyer/payment-settings');
    return response.data.data;
  },

  async uploadProof(paymentId: number, data: UploadPaymentRequest): Promise<Payment> {
    const formData = new FormData();
    formData.append('proof_image', data.proof_image);

    const response = await apiClient.post(`/payments/${paymentId}/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    return response.data.data;
  },
};
