<script setup lang="ts">
defineProps<{
  subtotal: string;
  shippingCost: string;
  totalAmount: string;
  paymentMethod: string;
}>();

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val;
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num || 0);
};
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
    <h3 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Rincian Pembayaran</h3>

    <div class="space-y-2.5 text-xs text-gray-600">
      <div class="flex justify-between">
        <span>Subtotal Produk</span>
        <span class="font-medium text-gray-800">{{ formatRupiah(subtotal) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Ongkos Kirim</span>
        <span class="font-medium text-gray-800">{{ formatRupiah(shippingCost) }}</span>
      </div>
      <div class="flex justify-between border-t border-gray-100 pt-2 font-bold text-sm text-gray-900">
        <span>Total Belanja</span>
        <span class="text-blue-600">{{ formatRupiah(totalAmount) }}</span>
      </div>
    </div>

    <div class="mt-4 p-2.5 bg-gray-50 rounded-lg text-center text-xs font-semibold text-gray-600 uppercase">
      Metode: {{ paymentMethod }}
    </div>
  </div>
</template>