<script setup lang="ts">
import { computed } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { formatCourierDisplay } from '@/constants/courier';
import type { Order } from '@/types';

const props = defineProps<{
  visible: boolean;
  order: Order | null;
}>();

const emit = defineEmits<{
  (e: 'update:visible', val: boolean): void;
}>();

const formatCurrency = (amount?: number | string) => {
  const num = Number(amount || 0);
  return 'Rp ' + num.toLocaleString('id-ID');
};

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const statusLabel = computed(() => {
  switch (props.order?.status) {
    case 'awaiting_verification':
      return 'Menunggu Verifikasi Pembayaran';
    case 'processing':
      return 'Dikonfirmasi (Sedang Diproses)';
    case 'packed':
      return 'Dikemas';
    case 'shipped':
      return 'Dalam Pengiriman';
    case 'cod_meeting':
      return 'COD (Menunggu Pertemuan)';
    case 'completed':
      return 'Selesai';
    case 'cancelled':
      return 'Dibatalkan';
    default:
      return props.order?.status || '-';
  }
});

const paymentStatusLabel = computed(() => {
  if (props.order?.payment?.status === 'verified') return 'Lunas (Diverifikasi Admin)';
  if (props.order?.payment_method === 'cod') return 'COD (Bayar di Tempat)';
  if (props.order?.payment?.status === 'rejected') return 'Ditolak';
  return 'Menunggu Verifikasi';
});

const isCod = computed(() => {
  return props.order?.shipping_method === 'cod' || props.order?.payment_method === 'cod';
});

const handlePrint = () => {
  window.print();
};
</script>

<template>
  <Dialog
    :visible="visible"
    @update:visible="(val) => emit('update:visible', val)"
    modal
    :dismissableMask="true"
    :style="{ width: 'min(820px, 96vw)' }"
    class="invoice-dialog-modal"
    :pt="{
      root: { class: 'rounded-2xl overflow-hidden' },
      content: { class: 'p-0 overflow-y-auto max-h-[88vh]' },
    }"
  >
    <!-- Actions Bar (Screen Only) -->
    <div class="no-print bg-slate-900 text-white px-6 py-3.5 flex items-center justify-between sticky top-0 z-20 shadow-md">
      <div class="flex items-center gap-2">
        <i class="pi pi-file text-blue-400 text-base"></i>
        <span class="font-bold text-sm">Pratinjau Cetak Invoice</span>
      </div>
      <div class="flex items-center gap-2">
        <Button
          label="Cetak Invoice"
          icon="pi pi-print"
          size="small"
          class="bg-blue-600! border-blue-600! text-xs! px-3! py-1.5! rounded-lg! font-bold! hover:bg-blue-700!"
          @click="handlePrint"
        />
        <Button
          icon="pi pi-times"
          text
          rounded
          size="small"
          class="text-white! hover:bg-white/10!"
          aria-label="Tutup"
          @click="emit('update:visible', false)"
        />
      </div>
    </div>

    <!-- Printable Invoice Body -->
    <div v-if="order" class="invoice-container bg-white p-6 sm:p-10 font-sans text-slate-800 text-xs sm:text-sm">
      <!-- Invoice Header -->
      <div class="border-b-2 border-slate-900 pb-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-2xl font-black tracking-tight text-blue-700">KABITA</span>
              <span class="text-[10px] uppercase font-bold tracking-widest bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                Marketplace UMKM
              </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Platform Belanja & Pemberdayaan Produk UMKM Lokal</p>
          </div>
          <div class="sm:text-right">
            <h1 class="text-lg sm:text-xl font-black text-slate-900 uppercase tracking-tight">Invoice / Bukti Transaksi</h1>
            <p class="font-mono text-xs font-bold text-blue-700 mt-0.5">{{ order.order_number }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Tanggal: {{ formatDate(order.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Transaction Info Grid: Seller, Buyer, Shipping -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-200">
        <!-- Seller Info -->
        <div class="space-y-1.5">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Penjual / Toko</span>
          <p class="font-bold text-slate-900 text-sm">{{ order.shop?.name || 'Toko UMKM Kabita' }}</p>
          <p v-if="order.shop?.address" class="text-xs text-slate-600 leading-relaxed">{{ order.shop.address }}</p>
          <p v-if="order.shop?.seller?.phone || order.shop?.phone" class="text-xs text-slate-500">
            Telp: {{ order.shop?.seller?.phone || order.shop?.phone }}
          </p>
        </div>

        <!-- Buyer Info -->
        <div class="space-y-1.5">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Tujuan Pengiriman</span>
          <p class="font-bold text-slate-900 text-sm">{{ order.buyer?.name || 'Pembeli' }}</p>
          <p v-if="order.buyer?.phone" class="text-xs text-slate-500">Telp: {{ order.buyer.phone }}</p>
          <p class="text-xs text-slate-600 leading-relaxed">{{ order.shipping_address || 'Alamat sesuai profil' }}</p>
        </div>
      </div>

      <!-- Shipping & Payment Details -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 py-4 border-b border-slate-200 text-xs bg-slate-50/60 -mx-6 sm:-mx-10 px-6 sm:px-10">
        <div>
          <span class="text-[10px] text-slate-400 block font-semibold uppercase">Metode Pembayaran</span>
          <span class="font-bold text-slate-800 capitalize">{{ order.payment_method === 'transfer' ? 'Transfer Bank' : 'COD (Ketemuan)' }}</span>
        </div>
        <div>
          <span class="text-[10px] text-slate-400 block font-semibold uppercase">Status Pembayaran</span>
          <span class="font-bold text-slate-800">{{ paymentStatusLabel }}</span>
        </div>
        <div>
          <span class="text-[10px] text-slate-400 block font-semibold uppercase">Kurir & Layanan</span>
          <span class="font-bold text-slate-800">{{ isCod ? 'COD (Ketemuan Langsung)' : formatCourierDisplay(order.courier) }}</span>
        </div>
        <div>
          <span class="text-[10px] text-slate-400 block font-semibold uppercase">Nomor Resi</span>
          <span class="font-mono font-bold text-blue-700">{{ order.tracking_number || '-' }}</span>
        </div>
      </div>

      <!-- Products Table -->
      <div class="mt-6">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Rincian Produk</h2>
        <div class="overflow-x-auto border border-slate-200 rounded-lg">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                <th class="py-2.5 px-3 w-10 text-center">No</th>
                <th class="py-2.5 px-3">Nama Produk</th>
                <th class="py-2.5 px-3 text-center w-20">Qty</th>
                <th class="py-2.5 px-3 text-right w-28">Harga Satuan</th>
                <th class="py-2.5 px-3 text-right w-32">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="(item, index) in order.items" :key="item.id" class="hover:bg-slate-50/50">
                <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ index + 1 }}</td>
                <td class="py-2.5 px-3">
                  <p class="font-bold text-slate-900">{{ item.product?.name || 'Produk' }}</p>
                  <p v-if="item.product?.category?.name" class="text-[10px] text-slate-400">{{ item.product.category.name }}</p>
                </td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-800">{{ item.quantity }}</td>
                <td class="py-2.5 px-3 text-right text-slate-600">{{ formatCurrency(item.price_snapshot) }}</td>
                <td class="py-2.5 px-3 text-right font-bold text-slate-900">
                  {{ formatCurrency(Number(item.price_snapshot) * item.quantity) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes & Financial Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 mt-6 pt-4 items-start">
        <!-- Notes Section -->
        <div class="sm:col-span-6 space-y-2">
          <div v-if="order.notes" class="bg-amber-50/70 border border-amber-200/80 rounded-lg p-3 text-xs">
            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wide block mb-1">Catatan Pembeli:</span>
            <p class="text-amber-900 italic">"{{ order.notes }}"</p>
          </div>
          <div class="text-[11px] text-slate-400 space-y-0.5">
            <p>• Bukti transaksi ini sah dan diterbitkan secara digital oleh platform Kabita.</p>
            <p>• Simpan invoice ini sebagai referensi pelacakan atau klaim pesanan Anda.</p>
          </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="sm:col-span-6 bg-slate-50/80 border border-slate-200 rounded-lg p-4 space-y-2 text-xs">
          <div class="flex justify-between text-slate-600">
            <span>Subtotal Produk</span>
            <span class="font-semibold text-slate-800">{{ formatCurrency(order.subtotal) }}</span>
          </div>
          <div class="flex justify-between text-slate-600">
            <span>Biaya Pengiriman (Ongkir)</span>
            <span class="font-semibold text-slate-800">{{ isCod ? 'Gratis (COD)' : formatCurrency(order.shipping_cost) }}</span>
          </div>
          <div class="border-t-2 border-slate-300 pt-2.5 mt-2 flex justify-between items-center">
            <span class="text-xs sm:text-sm font-extrabold text-slate-900 uppercase">Grand Total</span>
            <span class="text-base sm:text-lg font-black text-blue-700">{{ formatCurrency(order.total_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Footer Signoff -->
      <div class="mt-8 pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-400 gap-2">
        <span>Status Transaksi: <strong class="text-slate-700 font-semibold">{{ statusLabel }}</strong></span>
        <span>Dicetak melalui Kabita E-Commerce UMKM</span>
      </div>
    </div>
  </Dialog>
</template>

<style>
@media print {
  /* Hide all non-printable UI elements */
  body * {
    visibility: hidden !important;
  }
  .no-print {
    display: none !important;
  }
  
  /* Show only the invoice container */
  .invoice-dialog-modal,
  .invoice-dialog-modal *,
  .invoice-container,
  .invoice-container * {
    visibility: visible !important;
  }

  .invoice-dialog-modal {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    border: none !important;
    background: transparent !important;
  }

  .invoice-container {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 11pt !important;
    color: #000000 !important;
  }

  @page {
    size: A4 portrait;
    margin: 15mm;
  }
}
</style>
