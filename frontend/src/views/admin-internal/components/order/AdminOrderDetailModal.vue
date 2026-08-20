<script setup lang="ts">
import type { Order } from '@/types/entities';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';

defineProps<{
  visible: boolean
  order: Partial<Order> | null
}>()

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void
}>()

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num || 0)
}

const getStatusSeverity = (status?: string) => {
  switch (status) {
    case 'completed':
    case 'delivered':
      return 'success'
    case 'processing':
    case 'shipped':
      return 'info'
    case 'pending':
      return 'warn'
    case 'cancelled':
      return 'danger'
    default:
      return 'secondary'
  }
}
</script>

<template>
  <Dialog :visible="visible" modal :showHeader="false" :style="{ width: '52rem' }"
    @update:visible="emit('update:visible', $event)">
    <div v-if="order" class="bg-white rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="pi pi-receipt text-lg"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-slate-800 text-base">{{ order.order_number || `#ORD-2026-00${order.id}` }}</h3>
              <Tag :value="order.status?.toUpperCase()" :severity="getStatusSeverity(order.status)"
                class="text-![10px] px-2!" />
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Dibuat pada: {{ order.created_at ? new
              Date(order.created_at).toLocaleString('id-ID') : '-' }}</p>
          </div>
        </div>

        <button @click="emit('update:visible', false)"
          class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 transition-colors">
          <i class="pi pi-times text-sm"></i>
        </button>
      </div>

      <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto bg-slate-50/50">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded-2xl border border-slate-200/80 space-y-2">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Informasi Pembeli</p>
            <p class="text-sm font-bold text-slate-800">{{ order.buyer?.name || 'Nama Pembeli' }}</p>
            <p class="text-xs text-slate-500 flex items-center gap-1.5"><i class="pi pi-envelope text-blue-500"></i> {{
              order.buyer?.email }}</p>
            <p class="text-xs text-slate-500 flex items-center gap-1.5"><i class="pi pi-phone text-blue-500"></i> {{
              order.buyer?.phone || '-' }}</p>
            <div class="pt-2 border-t border-slate-100 text-xs text-slate-600">
              <span class="text-slate-400 block text-[11px] mb-0.5">Alamat Pengiriman:</span>
              <p class="leading-relaxed">{{ order.shipping_address || 'Jl. Jend. Sudirman No. 12, Jakarta' }}</p>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl border border-slate-200/80 space-y-2">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Informasi Toko</p>
            <div class="flex items-center gap-3">
              <img :src="order.shop?.logo || 'https://via.placeholder.com/100'"
                class="w-10 h-10 rounded-lg object-cover border border-slate-200" />
              <div>
                <p class="text-sm font-bold text-slate-800">{{ order.shop?.name || 'Nama Toko' }}</p>
                <p class="text-xs text-slate-500">Pemilik: {{ order.shop?.seller?.name || 'Seller' }}</p>
              </div>
            </div>
            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
              <div>
                <span class="text-slate-400 block text-[11px]">Metode Bayar:</span>
                <span class="font-bold text-slate-800 uppercase">{{ order.payment_method || 'Transfer' }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Metode Kirim:</span>
                <span class="font-bold text-slate-800 uppercase">{{ order.shipping_method === 'cod' ? 'COD' : 'Kurir'
                }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4">
          <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">Item Pesanan</p>
          <div class="space-y-3">
            <div v-for="item in order.items" :key="item.id"
              class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
              <div class="flex items-center gap-3">
                <img :src="item.product?.images?.[0]?.url || 'https://via.placeholder.com/100'"
                  class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0" />
                <div>
                  <p class="font-bold text-slate-800 line-clamp-1">{{ item.product?.name || 'Nama Produk' }}</p>
                  <p class="text-slate-400 mt-0.5">{{ item.quantity }} x {{ formatRupiah(Number(item.product?.price)) }}
                  </p>
                </div>
              </div>
              <span class="font-extrabold text-slate-800">{{ formatRupiah(Number(item.product?.price) * item.quantity)
                }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 space-y-2 text-xs">
          <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Rincian Pembayaran</p>
          <div class="flex items-center justify-between text-slate-600">
            <span>Subtotal Produk:</span>
            <span>{{ formatRupiah(parseFloat(order.total_amount || '0') - parseFloat(order.shipping_cost || '0'))
            }}</span>
          </div>
          <div class="flex items-center justify-between text-slate-600">
            <span>Biaya Pengiriman:</span>
            <span>{{ formatRupiah(order.shipping_cost || 0) }}</span>
          </div>
          <div
            class="flex items-center justify-between pt-2 border-t border-slate-100 text-sm font-extrabold text-slate-800">
            <span>Total Transaksi:</span>
            <span class="text-blue-600 text-base">{{ formatRupiah(order.total_amount || 0) }}</span>
          </div>
        </div>
      </div>

      <div class="p-4 border-t border-slate-100 flex items-center justify-end bg-white">
        <Button label="Tutup" outlined class="border-slate-300! text-slate-600! px-5!"
          @click="emit('update:visible', false)" />
      </div>
    </div>
  </Dialog>
</template>