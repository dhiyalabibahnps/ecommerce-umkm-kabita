<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';

import OrderStatusBadge from '@/components/ui/OrderStatusBadge.vue';
import { FLAT_SHIPPING_OPTIONS, formatCourierDisplay, getCourierSelectOptions, resolveCourierOptionValue } from '@/constants/courier';
import { getApiErrorMessage } from '@/services/apiError';
import { sellerOrderService } from '@/services/sellerOrderService';
import { useChatStore } from '@/stores/chat';
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
import OrderInvoiceModal from '@/components/invoice/OrderInvoiceModal.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const chatStore = useChatStore();

const isLoading = ref(true);
const isActionLoading = ref(false);
const order = ref<Order | null>(null);
const errorMessage = ref('');
const shippingModalVisible = ref(false);
const invoiceModalVisible = ref(false);
const trackingNumberInput = ref('');
const courierInput = ref('JNE REG');

const fetchOrderDetail = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    order.value = await sellerOrderService.getDetail(Number(route.params.id));
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Detail pesanan gagal dimuat.');
  } finally {
    isLoading.value = false;
  }
};

const handleOpenChat = () => {
  if (order.value) {
    chatStore.openOrderChat(
      order.value.id,
      order.value.order_number,
      order.value.buyer?.name,
      'seller'
    );
  }
};

const openShipModal = () => {
  if (!order.value) return;
  if (order.value.shipping_method === 'cod') {
    handleShipOrderDirect();
    return;
  }
  trackingNumberInput.value = order.value.tracking_number || '';
  courierInput.value = resolveCourierOptionValue(order.value.courier);
  shippingModalVisible.value = true;
};

const handleShipOrderDirect = async () => {
  if (!order.value) return;
  isActionLoading.value = true;
  try {
    order.value = await sellerOrderService.ship(order.value.id, {
      tracking_number: order.value.tracking_number || undefined,
      courier: order.value.courier || undefined,
    });
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan berhasil dikirim.', life: 3000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(error, 'Status pesanan gagal diperbarui.'), life: 4000 });
  } finally {
    isActionLoading.value = false;
  }
};

const handleConfirmShipModal = async () => {
  if (!order.value) return;

  if (order.value.shipping_method !== 'cod' && !trackingNumberInput.value.trim()) {
    toast.add({ severity: 'warn', summary: 'Wajib Diisi', detail: 'Nomor resi pengiriman wajib diisi.', life: 3000 });
    return;
  }

  isActionLoading.value = true;
  try {
    order.value = await sellerOrderService.ship(order.value.id, {
      tracking_number: trackingNumberInput.value.trim(),
      courier: courierInput.value.trim() || undefined,
    });
    shippingModalVisible.value = false;
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Nomor resi disimpan dan pesanan berhasil dikirim.', life: 3000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(error, 'Pengiriman pesanan gagal.'), life: 4000 });
  } finally {
    isActionLoading.value = false;
  }
};

const handlePackOrder = async () => {
  if (!order.value) return;
  isActionLoading.value = true;
  try {
    order.value = await sellerOrderService.pack(order.value.id);
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan berhasil dikemas.', life: 3000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(error, 'Status pesanan gagal diperbarui.'), life: 4000 });
  } finally {
    isActionLoading.value = false;
  }
};

const handleCodComplete = async () => {
  if (!order.value) return;
  isActionLoading.value = true;
  try {
    order.value = await sellerOrderService.codComplete(order.value.id);
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan COD berhasil diselesaikan.', life: 3000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(error, 'Status pesanan gagal diperbarui.'), life: 4000 });
  } finally {
    isActionLoading.value = false;
  }
};

const formatDate = (value?: string) =>
  value
    ? new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      })
    : '—';

const courierOptionsForSelect = computed(() => {
  return getCourierSelectOptions(order.value?.courier);
});

onMounted(fetchOrderDetail);
</script>

<template>
  <div class="mx-auto max-w-6xl pb-12">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 text-slate-400">
      <ProgressSpinner style="width: 40px; height: 40px" />
      <p class="mt-3 text-xs">Memuat detail pesanan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage || !order" class="rounded-2xl border border-rose-100 bg-white p-10 text-center shadow-2xs">
      <i class="pi pi-exclamation-triangle text-3xl text-rose-500"></i>
      <h3 class="mt-3 font-bold text-slate-800">Gagal Memuat Pesanan</h3>
      <p class="mt-1 text-xs text-rose-600">{{ errorMessage || 'Pesanan tidak ditemukan.' }}</p>
      <div class="mt-5 flex justify-center gap-2">
        <Button label="Kembali ke Pesanan" severity="secondary" outlined size="small" class="rounded-lg! text-xs!" @click="router.push('/seller/pesanan')" />
        <Button label="Coba Lagi" icon="pi pi-refresh" size="small" class="rounded-lg! text-xs!" @click="fetchOrderDetail" />
      </div>
    </div>

    <!-- Order Detail View -->
    <div v-else class="space-y-4">
      <!-- Back button & Top bar -->
      <div class="flex items-center justify-between">
        <button
          type="button"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 transition cursor-pointer"
          @click="router.push('/seller/pesanan')"
        >
          <i class="pi pi-arrow-left text-[11px]"></i>
          <span>Kembali ke Daftar Pesanan</span>
        </button>
      </div>

      <!-- Modern Compact Order Header -->
      <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2.5">
              <h1 class="font-mono text-base font-black tracking-tight text-slate-900 sm:text-lg">
                {{ order.order_number }}
              </h1>
              <OrderStatusBadge :status="order.status" size="normal" role="seller" />
            </div>
            <p class="mt-1 text-xs text-slate-500">
              Pembeli: <strong class="text-slate-800">{{ order.buyer?.name || 'Pembeli' }}</strong> • {{ formatDate(order.created_at) }}
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex flex-wrap items-center gap-2">
            <Button
              label="Cetak Invoice"
              icon="pi pi-print"
              severity="secondary"
              outlined
              size="small"
              class="text-xs! rounded-lg! text-slate-700! border-slate-300! hover:bg-slate-50!"
              @click="invoiceModalVisible = true"
            />
            <Button
              label="Chat Pembeli"
              icon="pi pi-comments"
              severity="secondary"
              outlined
              size="small"
              class="text-xs! rounded-lg! text-blue-600! border-blue-200! hover:bg-blue-50!"
              @click="handleOpenChat"
            />

            <!-- Contextual Status Actions -->
            <template v-if="order.status === 'processing'">
              <Button
                label="Proses Pesanan"
                icon="pi pi-box"
                size="small"
                class="bg-blue-600! border-blue-600! text-xs! rounded-lg! font-bold!"
                :loading="isActionLoading"
                @click="handlePackOrder"
              />
            </template>

            <template v-else-if="order.status === 'packed'">
              <Button
                label="Konfirmasi Pengiriman"
                icon="pi pi-send"
                size="small"
                class="bg-blue-600! border-blue-600! text-xs! rounded-lg! font-bold!"
                :loading="isActionLoading"
                @click="openShipModal"
              />
            </template>

            <template v-else-if="order.status === 'cod_meeting'">
              <Button
                label="Selesaikan Pesanan COD"
                icon="pi pi-check"
                severity="success"
                size="small"
                class="text-xs! rounded-lg! font-bold!"
                :loading="isActionLoading"
                @click="handleCodComplete"
              />
            </template>
          </div>
        </div>
      </div>

      <!-- Stepper Component -->
      <OrderStepper
        :status="order.status"
        :shippingMethod="order.shipping_method"
        :isVerified="order.payment?.status === 'verified'"
      />

      <!-- Content Grid: Left Main + Right Sidebar -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Main Content -->
        <div class="lg:col-span-8 space-y-4">
          <OrderStatusAlert :status="order.status" :notes="order.notes" :shippingMethod="order.shipping_method" />
          <OrderTransferProof :proofImage="order.payment?.proof_image ?? undefined" />
          <OrderProductList :items="order.items" :notes="order.notes" />
          <OrderTimeline
            :status="order.status"
            :shippingMethod="order.shipping_method"
            :paymentMethod="order.payment_method"
            :isVerified="order.payment?.status === 'verified'"
            :hasProofImage="Boolean(order.payment?.proof_image)"
            :courier="order.courier"
            :trackingNumber="order.tracking_number"
            :createdAt="order.created_at"
            :updatedAt="order.updated_at"
          />
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-4 space-y-4">
          <OrderBuyerCard :buyer="order.buyer" :shippingAddress="order.shipping_address" @chat="handleOpenChat" />
          <OrderShippingCard
            :shippingMethod="order.shipping_method"
            :courier="order.courier"
            :trackingNumber="order.tracking_number"
            :shippingAddress="order.shipping_address"
            :status="order.status"
          />
          <OrderPaymentSummaryCard
            :subtotal="order.subtotal"
            :shippingCost="order.shipping_cost"
            :totalAmount="order.total_amount"
            :paymentMethod="order.payment_method"
          />
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Pengiriman & Input Resi -->
    <Dialog
      v-model:visible="shippingModalVisible"
      modal
      header="Konfirmasi Pengiriman Pesanan"
      :style="{ width: 'min(460px, 92vw)' }"
      class="rounded-2xl!"
    >
      <div class="space-y-4 pt-1">
        <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100 text-xs space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Nomor Order:</span>
            <strong class="text-slate-800 font-mono">{{ order?.order_number }}</strong>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Pilihan Kurir Pembeli:</span>
            <span class="font-bold text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded text-[11px]">
              {{ formatCourierDisplay(order?.courier) }}
            </span>
          </div>
          <div class="flex items-start justify-between gap-2 pt-0.5 border-t border-slate-200/50">
            <span class="text-slate-500 shrink-0">Alamat Tujuan:</span>
            <span class="text-slate-700 text-right line-clamp-2">{{ order?.shipping_address }}</span>
          </div>
        </div>

        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold text-slate-700">Layanan Kurir <span class="text-rose-500">*</span></label>
            <span class="text-[10px] text-slate-400 font-medium">Otomatis terpilih 1-1 sesuai buyer</span>
          </div>
          <select
            v-model="courierInput"
            class="w-full h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-800 focus:border-blue-500 focus:outline-none"
          >
            <option v-for="item in courierOptionsForSelect" :key="item.value" :value="item.value">
              {{ item.label }}
            </option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-700">Nomor Resi Pengiriman <span class="text-rose-500">*</span></label>
          <InputText
            v-model="trackingNumberInput"
            placeholder="Contoh: JNE123456789 / SPXID0987654321"
            class="w-full text-xs! py-2.5! rounded-lg!"
          />
          <p class="text-[11px] text-slate-400">Nomor resi wajib diisi agar pembeli dapat melacak paket.</p>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
          <Button
            label="Batal"
            severity="secondary"
            outlined
            size="small"
            class="rounded-lg! text-xs!"
            @click="shippingModalVisible = false"
          />
          <Button
            label="Kirim Pesanan"
            icon="pi pi-send"
            size="small"
            class="bg-blue-600! border-blue-600! rounded-lg! text-xs! font-bold!"
            :loading="isActionLoading"
            @click="handleConfirmShipModal"
          />
        </div>
      </div>
    </Dialog>

    <!-- Modal Cetak Invoice -->
    <OrderInvoiceModal
      v-model:visible="invoiceModalVisible"
      :order="order"
    />
  </div>
</template>
