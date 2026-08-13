<script setup lang="ts">
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import type { Order } from '@/types';

// Sub Components
import OrderBuyerCard from '../components/order-detail/OrderBuyerCard.vue';
import OrderPaymentSummaryCard from '../components/order-detail/OrderPaymentSummaryCard.vue';
import OrderProductList from '../components/order-detail/OrderProductList.vue';
import OrderShippingCard from '../components/order-detail/OrderShippingCard.vue';
import OrderStatusAlert from '../components/order-detail/OrderStatusAlert.vue';
import OrderStepper from '../components/order-detail/OrderStepper.vue';
import OrderTimeline from '../components/order-detail/OrderTimeline.vue';
import OrderTransferProof from '../components/order-detail/OrderTransferProof.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const isActionLoading = ref(false);
const order = ref<Order | null>(null);

// MOCK DATA GETTER
const fetchOrderDetail = () => {
  isLoading.value = true;
  setTimeout(() => {
    order.value = {
      id: 101,
      order_number: (route.params.id as string) || 'INV/20231028/MPL/3521944',
      buyer_id: 1,
      shop_id: 2,
      subtotal: '295000',
      shipping_cost: '15000',
      total_amount: '310000',
      shipping_method: 'JNE Reguler',
      payment_method: 'Transfer Bank BCA',
      status: 'processing',
      shipping_address: 'Jl. Sudirman No. 45, Kebayoran Baru, Jakarta Selatan, 12190',
      tracking_number: 'JNT8899223311',
      notes: 'Tolong dipacking kayu ya mas, agar aman sampai tujuan. Terima kasih!',
      created_at: '28 Okt 2023, 14:30 WIB',
      updated_at: '28 Okt 2023, 15:00 WIB',
      buyer: {
        id: 1,
        name: 'Budi Santoso',
        email: 'budi.s@example.com',
        phone: '0812-3456-7890',
        proof_image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100',
        role: 'buyer',
        status: 'active',
        address: null,
        email_verified_at: null,
        verified_by: null,
        verified_at: null,
        created_at: '',
        updated_at: ''
      },
      items: [
        {
          id: 1,
          order_id: 101,
          product_id: 201,
          quantity: 2,
          price_snapshot: '85000',
          cost_snapshot: '70000',
          created_at: '',
          updated_at: '',
          product: {
            id: 201,
            shop_id: 2,
            category_id: 1,
            name: 'Kopi Arabica Gayo Premium - 250g',
            slug: 'kopi-arabica-gayo',
            description: '',
            price: '85000',
            cost_price: null,
            stock: 20,
            weight: 250,
            status: 'active',
            verified_at: null,
            rejection_reason: null,
            created_at: '',
            images: [{ id: 1, url: 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=200' }]
          }
        },
        {
          id: 2,
          order_id: 101,
          product_id: 202,
          quantity: 1,
          price_snapshot: '125000',
          cost_snapshot: '100000',
          created_at: '',
          updated_at: '',
          product: {
            id: 202,
            shop_id: 2,
            category_id: 1,
            name: 'Ceramic V60 Dripper Size 02',
            slug: 'v60-dripper',
            description: '',
            price: '125000',
            cost_price: null,
            stock: 15,
            weight: 300,
            status: 'active',
            verified_at: null,
            rejection_reason: null,
            created_at: '',
            images: [{ id: 2, url: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=200' }]
          }
        }
      ],
      payment: {
        id: 1,
        order_id: 101,
        amount: '310000',
        status: 'verified',
        proof_image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=400',
        created_at: '',
        updated_at: ''
      }
    };
    isLoading.value = false;
  }, 700);
};

const handleShipOrder = () => {
  isActionLoading.value = true;
  setTimeout(() => {
    if (order.value) order.value.status = 'shipped';
    isActionLoading.value = false;
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan dikonfirmasi & status diubah menjadi Dikirim!', life: 3000 });
  }, 1000);
};

onMounted(() => {
  fetchOrderDetail();
});
</script>

<template>
  <div class="p-6 relative min-h-screen bg-slate-50">

    <Transition name="fade">
      <div v-if="isLoading"
        class="fixed inset-0 z-50 bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center gap-3">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" animationDuration=".8s" />
        <span class="text-sm font-semibold text-gray-600">Memuat Detail Pesanan...</span>
      </div>
    </Transition>

    <div v-if="order" class="max-w-6xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.back()" />
          <div>
            <h1 class="text-xl font-bold text-gray-800">Detail {{ order.order_number }}</h1>
            <p class="text-xs text-gray-400 mt-0.5">{{ order.created_at }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Button icon="pi pi-print" label="Cetak Invoice" outlined severity="secondary" size="small" />
          <Button v-if="order.status === 'processing'" label="Konfirmasi Pengiriman" icon="pi pi-send" size="small"
            :loading="isActionLoading" @click="handleShipOrder" />
        </div>
      </div>

      <OrderStepper :status="order.status" :shippingMethod="order.shipping_method"
        :isVerified="order.payment?.status === 'verified'" />

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8">
          <OrderStatusAlert :status="order.status" :notes="order.notes" :shippingMethod="order.shipping_method" />
          <OrderTransferProof :proofImage="order.payment?.proof_image || null" />
          <OrderProductList :items="order.items" :notes="order.notes" />
          <OrderTimeline :status="order.status" :createdAt="order.created_at" :updatedAt="order.updated_at" />
        </div>

        <div class="lg:col-span-4">
          <OrderBuyerCard :buyer="order.buyer" :shippingAddress="order.shipping_address" />
          <OrderShippingCard :shippingMethod="order.shipping_method" :trackingNumber="order.tracking_number"
            :shippingAddress="order.shipping_address" />
          <OrderPaymentSummaryCard :subtotal="order.subtotal" :shippingCost="order.shipping_cost"
            :totalAmount="order.total_amount" :paymentMethod="order.payment_method" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>