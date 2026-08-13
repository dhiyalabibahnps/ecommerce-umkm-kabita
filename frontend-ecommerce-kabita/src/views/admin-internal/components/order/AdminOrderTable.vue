<script setup lang="ts">
import type { Order } from '@/types/entities';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

defineProps<{
  orders: Partial<Order>[]
}>()

const emit = defineEmits<{
  (e: 'viewDetail', order: Partial<Order>): void
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

const getPaymentBadge = (method?: string) => {
  if (method?.toLowerCase() === 'cod') {
    return 'bg-amber-50 text-amber-700 border-amber-200'
  }
  return 'bg-blue-50 text-blue-700 border-blue-200'
}
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="border-b border-slate-100 text-slate-400 text-[11px] font-bold uppercase tracking-wider bg-slate-50/50">
            <th class="py-4 px-5">NO. PESANAN</th>
            <th class="py-4 px-4">PEMBELI</th>
            <th class="py-4 px-4">TOKO</th>
            <th class="py-4 px-4">TOTAL</th>
            <th class="py-4 px-4">PEMBAYARAN</th>
            <th class="py-4 px-4">PENGIRIMAN</th>
            <th class="py-4 px-4">STATUS</th>
            <th class="py-4 px-4">TANGGAL</th>
            <th class="py-4 px-5 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          <tr v-if="orders.length === 0">
            <td colspan="9" class="py-12 text-center text-slate-400 font-medium">
              <i class="pi pi-receipt text-3xl text-slate-300 mb-2 block"></i>
              Tidak ada data pesanan ditemukan.
            </td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-5 font-bold text-slate-800 text-xs">
              {{ order.order_number || `#ORD-2026-00${order.id}` }}
            </td>

            <td class="py-3.5 px-4">
              <div class="flex flex-col">
                <span class="font-bold text-slate-800 text-xs">{{ order.buyer?.name || 'Pembeli Kabita' }}</span>
                <span class="text-[11px] text-slate-400 truncate max-w-35">{{ order.buyer?.email }}</span>
              </div>
            </td>

            <td class="py-3.5 px-4 font-semibold text-slate-700 text-xs">
              {{ order.shop?.name || 'Toko Vendor' }}
            </td>

            <td class="py-3.5 px-4 font-extrabold text-slate-800 text-xs">
              {{ formatRupiah(order.total_amount || 0) }}
            </td>

            <td class="py-3.5 px-4">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border capitalize"
                :class="getPaymentBadge(order.payment_method)">
                {{ order.payment_method?.toUpperCase() || 'TRANSFER' }}
              </span>
            </td>

            <td class="py-3.5 px-4 text-xs text-slate-600 font-medium capitalize">
              <span class="inline-flex items-center gap-1">
                <i
                  :class="order.shipping_method === 'cod' ? 'pi pi-map-marker text-amber-500' : 'pi pi-truck text-blue-500'"></i>
                {{ order.shipping_method === 'cod' ? 'COD (Lokal)' : 'Kurir Reguler' }}
              </span>
            </td>

            <td class="py-3.5 px-4">
              <Tag :value="order.status ? order.status.toUpperCase() : 'PENDING'"
                :severity="getStatusSeverity(order.status)" class="text-![10px] px-2!.5 py-0!.5" />
            </td>

            <td class="py-3.5 px-4 text-xs text-slate-500">
              {{ order.created_at ? new Date(order.created_at).toLocaleDateString('id-ID', {
                day: '2-digit', month:
                  'short', year: 'numeric' }) : '-' }}
            </td>

            <td class="py-3.5 px-5 text-right">
              <Button label="Detail" outlined size="small"
                class="px-3! py-1! text-xs! font-bold! border-blue-200! text-blue-600! hover:bg-blue-50!"
                @click="emit('viewDetail', order)" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between p-4 border-t border-slate-100 text-xs text-slate-500 bg-slate-50/30">
      <span>Menampilkan 1-{{ orders.length }} dari {{ orders.length }} transaksi</span>
      <div class="flex items-center gap-1">
        <Button label="< Prev" text size="small" class="text-slate-500! font-semibold! px-2!.5" />
        <Button label="1"
          class="bg-blue-600! border-blue-600! text-white! w-7! h-7! p-0! rounded-full! font-bold! text-xs!" />
        <Button label="Next >" text size="small" class="text-slate-500! font-semibold! px-2!.5" />
      </div>
    </div>
  </div>
</template>