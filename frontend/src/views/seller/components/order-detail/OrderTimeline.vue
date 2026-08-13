<script setup lang="ts">
import type { OrderStatus } from '@/types';

defineProps<{
  status: OrderStatus;
  createdAt: string;
  updatedAt: string;
}>();
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
    <h3 class="text-sm font-bold text-gray-800 mb-4">Aktivitas Pesanan</h3>

    <div
      class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
      <div class="relative">
        <span
          class="absolute -left-6 top-0 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-100 flex items-center justify-center">
          <i class="pi pi-check text-[8px] text-white"></i>
        </span>
        <h4 class="text-xs font-bold text-gray-800 capitalize">Status saat ini: {{ status }}</h4>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ updatedAt }}</p>
      </div>

      <div class="relative">
        <span class="absolute -left-6 top-0 w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center">
          <i class="pi pi-check text-[8px] text-white"></i>
        </span>
        <h4 class="text-xs font-bold text-gray-800">Pesanan Dibuat</h4>
        <p class="text-[11px] text-gray-400 mt-0.5">Pesanan berhasil dibuat oleh pembeli.</p>
        <p class="text-[11px] text-gray-400">{{ createdAt }}</p>
      </div>
    </div>
  </div>
</template>