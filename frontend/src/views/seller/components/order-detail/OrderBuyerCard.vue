<script setup lang="ts">
import type { User } from '@/types';
import Button from 'primevue/button';

defineProps<{
  buyer?: User;
  shippingAddress: string;
}>();

const emit = defineEmits<{
  (e: 'chat'): void;
}>();
</script>

<template>
  <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs mb-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
      <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Pembeli</h3>
      <span class="text-[10px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-semibold">Pelanggan</span>
    </div>

    <div class="space-y-3">
      <div class="flex items-start gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 border border-blue-100 text-xs font-bold text-blue-600">
          {{ (buyer?.name || '?').slice(0, 1).toUpperCase() }}
        </div>
        <div class="min-w-0 flex-1">
          <h4 class="text-xs font-bold text-slate-800">{{ buyer?.name || 'Pembeli' }}</h4>
          <p class="text-[11px] text-slate-500 truncate">{{ buyer?.email || '-' }}</p>
          <p v-if="buyer?.phone" class="text-[11px] font-medium text-slate-700 mt-0.5">{{ buyer.phone }}</p>
        </div>
      </div>

      <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-100 text-xs space-y-1">
        <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Alamat Pengiriman</span>
        <p class="text-xs text-slate-700 leading-relaxed">{{ shippingAddress || 'Alamat tidak tersedia' }}</p>
      </div>

      <Button
        label="Chat Pembeli"
        icon="pi pi-comments"
        severity="secondary"
        outlined
        size="small"
        class="w-full text-xs! rounded-lg! text-blue-600! border-blue-200! hover:bg-blue-50!"
        @click="emit('chat')"
      />
    </div>
  </div>
</template>
