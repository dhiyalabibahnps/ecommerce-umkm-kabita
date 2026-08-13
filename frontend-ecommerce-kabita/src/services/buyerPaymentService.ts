import type { Payment, UploadPaymentRequest } from '../types';
import apiClient from './apiClient';

export const buyerPaymentService = {
  async uploadProof(paymentId: number, data: UploadPaymentRequest): Promise<Payment> {
    const formData = new FormData();
    formData.append('proof_image', data.proof_image);

    const response = await apiClient.post(`/payments/${paymentId}/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    return response.data.data;
  },
};
