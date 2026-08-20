<script setup lang="ts">
import type { OrderStatus } from '@/types';

defineProps<{
  status: OrderStatus;
  createdAt: string;
  updatedAt: string;
}>();
</script>

const statusLabel = computed(() => {
  const map: Record<string, string> = {
    awaiting_verification: 'Pembayaran Sedang Diverifikasi',
    processing: 'Dikonfirmasi',
    packed: 'Dikemas',
    shipped: 'Dikirim',
    cod_meeting: 'Ketemuan',
    completed: 'Pesanan Selesai',
    cancelled: 'Dibatalkan',
  };
  return map[props.status] || props.status;
});

const timelineItems = computed(() => {
  const items = [
    { title: 'Pesanan Dibuat', desc: 'Pesanan berhasil dibuat oleh pembeli.', time: createdAt, done: true },
    { title: statusLabel.value, desc: 'Status pesanan diperbarui.', time: updatedAt, done: true },
  ];

  if (props.status === 'cancelled') {
    items.push({ title: 'Dibatalkan', desc: 'Pesanan dibatalkan.', time: updatedAt, done: true });
  }

  return items;
});
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
    <h3 class="text-sm font-bold text-gray-800 mb-4">Aktivitas Pesanan</h3>

    <div
      class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
      <div v-for="(item, idx) in timelineItems" :key="idx" class="relative">
        <span
          class="absolute -left-6 top-0 w-4 h-4 rounded-full flex items-center justify-center"
          :class="item.done ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-gray-200'">
          <i v-if="item.done" class="pi pi-check text-[8px] text-white"></i>
        </span>
        <h4 class="text-xs font-bold text-gray-800">{{ item.title }}</h4>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ item.desc }}</p>
        <p class="text-[11px] text-gray-400">{{ item.time }}</p>
      </div>
    </div>
  </div>
</template>