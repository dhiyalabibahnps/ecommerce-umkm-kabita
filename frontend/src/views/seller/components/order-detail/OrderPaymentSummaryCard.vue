<script setup lang="ts">
defineProps<{
  subtotal: string;
  shippingCost: string;
  totalAmount: string;
  paymentMethod: string;
}>();

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val;
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(num || 0);
};
</script>

<template>
  <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs mb-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
      <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Pembayaran</h3>
      <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-semibold">
        {{ paymentMethod === 'transfer' ? 'Transfer Bank' : 'COD' }}
      </span>
    </div>

    <div class="space-y-2 text-xs text-slate-600">
      <div class="flex justify-between">
        <span>Subtotal Produk</span>
        <span class="font-medium text-slate-800">{{ formatRupiah(subtotal) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Ongkos Kirim</span>
        <span class="font-medium text-slate-800">{{ formatRupiah(shippingCost) }}</span>
      </div>
      <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-xs text-slate-900">
        <span>Total Pembayaran</span>
        <span class="text-sm text-blue-600 font-extrabold">{{ formatRupiah(totalAmount) }}</span>
      </div>
    </div>
  </div>
</template>
