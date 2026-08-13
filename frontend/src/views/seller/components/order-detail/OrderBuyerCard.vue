<script setup lang="ts">
import type { User } from '@/types';

defineProps<{
  buyer?: User;
  shippingAddress: string;
}>();
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
    <h3 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Informasi Pembeli</h3>

    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <img :src="buyer?.proof_image || 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100'"
          class="w-10 h-10 rounded-full object-cover border border-gray-200" />
        <div>
          <h4 class="text-xs font-bold text-gray-800">{{ buyer?.name || 'Budi Santoso' }}</h4>
          <p class="text-[11px] text-gray-400">{{ buyer?.email || 'budi@example.com' }}</p>
        </div>
      </div>
      <i class="pi pi-comments text-blue-600 text-lg cursor-pointer hover:opacity-80"></i>
    </div>

    <div class="space-y-1.5 text-xs text-gray-600">
      <span class="font-semibold text-gray-400 text-[11px]">Alamat Pengiriman</span>
      <p class="leading-relaxed text-gray-700">{{ shippingAddress }}</p>
      <p class="font-medium text-gray-800 pt-1">{{ buyer?.phone || '0812-3456-7890' }}</p>
    </div>
  </div>
</template>