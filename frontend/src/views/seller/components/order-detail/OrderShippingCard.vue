<script setup lang="ts">
import Tag from 'primevue/tag';

defineProps<{
  shippingMethod: string;
  trackingNumber: string | null;
  shippingAddress: string;
}>();
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
    <h3 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">
      {{ shippingMethod === 'cod' ? 'Informasi Ketemuan (COD)' : 'Info Pengiriman' }}
    </h3>

    <div class="space-y-3 text-xs">
      <div>
        <span class="text-gray-400 text-[11px]">Metode / Kurir</span>
        <p class="font-bold text-gray-800 uppercase">{{ shippingMethod === 'cod' ? 'Cash on Delivery (COD)' :
          shippingMethod }}</p>
      </div>

      <div>
        <span class="text-gray-400 text-[11px]">Nomor Resi</span>
        <div class="mt-1">
          <Tag v-if="trackingNumber" :value="trackingNumber" severity="info" class="font-mono! text-xs!"></Tag>
          <span v-else class="text-gray-400 italic">Menunggu resi...</span>
        </div>
      </div>

      <div v-if="shippingMethod === 'cod'" class="mt-3">
        <span class="text-gray-400 text-[11px] block mb-1">Informasi Ketemuan</span>
        <div
          class="w-full h-28 bg-slate-100 rounded-lg border border-slate-200 flex flex-col items-center justify-center text-slate-400 gap-1">
          <i class="pi pi-map-marker text-red-500 text-xl"></i>
          <span class="text-[10px] text-gray-500 font-medium px-2 text-center">{{ shippingAddress }}</span>
        </div>
      </div>
    </div>
  </div>
</template>