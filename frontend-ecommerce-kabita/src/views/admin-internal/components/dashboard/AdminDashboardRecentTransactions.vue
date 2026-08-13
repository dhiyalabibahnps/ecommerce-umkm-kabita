<script setup lang="ts">
import type { Order } from '@/types/entities';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

interface Props {
  orders: Partial<Order>[]
}

defineProps<Props>()

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

const getPaymentBadgeClass = (method?: string) => {
  switch (method?.toLowerCase()) {
    case 'transfer':
      return 'bg-blue-50 text-blue-600 border-blue-200'
    case 'cod':
      return 'bg-amber-50 text-amber-600 border-amber-200'
    default:
      return 'bg-slate-50 text-slate-600 border-slate-200'
  }
}
</script>

<template>
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h4 class="text-base font-bold text-slate-800">Pesanan Terbaru</h4>
        <p class="text-xs text-slate-500">Aktivitas pesanan antar Toko dan Pembeli di platform</p>
      </div>
      <Button label="Lihat Semua" outlined size="small"
        class="text-xs! font-semibold! text-blue-600! border-blue-300! hover:bg-blue-50!" />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr
            class="border-b border-slate-200/80 text-slate-400 text-[11px] font-bold uppercase tracking-wider bg-slate-50/50">
            <th class="py-3 px-4">ORDER NUMBER</th>
            <th class="py-3 px-4">PEMBELI</th>
            <th class="py-3 px-4">TOKO</th>
            <th class="py-3 px-4">TOTAL</th>
            <th class="py-3 px-4">PAYMENT</th>
            <th class="py-3 px-4">STATUS</th>
            <th class="py-3 px-4">TANGGAL</th>
            <th class="py-3 px-4 text-center">ACTION</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="order in orders" :key="order.id" class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs">
              {{ order.order_number || `#ORD-2026-00${order.id}` }}
            </td>

            <td class="py-3.5 px-4 text-slate-700 font-medium text-xs">
              {{ order.buyer?.name || 'Pembeli Kabita' }}
            </td>

            <td class="py-3.5 px-4 text-slate-600 text-xs">
              {{ order.shop?.name || 'Toko Kabita' }}
            </td>

            <td class="py-3.5 px-4 font-bold text-slate-800 text-xs">
              {{ formatRupiah(order.total_amount || 0) }}
            </td>

            <td class="py-3.5 px-4">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                :class="getPaymentBadgeClass(order.payment_method)">
                {{ order.payment_method || 'Transfer' }}
              </span>
            </td>

            <td class="py-3.5 px-4">
              <Tag :value="order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Pending'"
                :severity="getStatusSeverity(order.status)" class="text-[10px]! px-2.5! py-0.5!" />
            </td>

            <td class="py-3.5 px-4 text-xs text-slate-500">
              {{ order.created_at ? new Date(order.created_at).toLocaleDateString('id-ID', {
                day: '2-digit', month:
                  'short', year: 'numeric'
              }) : '12 Okt 2026' }}
            </td>

            <td class="py-3.5 px-4 text-center">
              <Button icon="pi pi-ellipsis-v" text rounded severity="secondary"
                class="w-7! h-7! p-0! text-slate-400! hover:text-slate-700!" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>