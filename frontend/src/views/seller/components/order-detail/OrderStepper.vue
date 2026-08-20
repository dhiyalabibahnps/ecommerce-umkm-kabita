<script setup lang="ts">
import type { OrderStatus } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
  status: OrderStatus;
  shippingMethod: string;
  isVerified?: boolean;
}>();

interface StepItem {
  key: string;
  label: string;
  icon: string;
}

const steps = computed<StepItem[]>(() => {
  if (props.status === 'cancelled') {
    return [
      { key: 'dipesan', label: 'Dipesan', icon: 'pi pi-shopping-cart' },
      { key: 'dibayar', label: 'Dibayar', icon: 'pi pi-credit-card' },
      { key: 'dibatalkan', label: 'Dibatalkan', icon: 'pi pi-times-circle' },
    ];
  }

  if (props.shippingMethod === 'cod') {
    return [
      { key: 'dipesan', label: 'Dipesan', icon: 'pi pi-shopping-cart' },
      { key: 'dikonfirmasi', label: 'Dikonfirmasi', icon: 'pi pi-check-circle' },
      { key: 'dikemas', label: 'Dikemas', icon: 'pi pi-box' },
      { key: 'dikirim', label: 'Menuju Titik Temu', icon: 'pi pi-truck' },
      { key: 'ketemuan', label: 'Ketemuan', icon: 'pi pi-map-marker' },
      { key: 'selesai', label: 'Selesai', icon: 'pi pi-verified' },
    ];
  }

  return [
    { key: 'dipesan', label: 'Dipesan', icon: 'pi pi-shopping-cart' },
    { key: 'verifikasi', label: props.isVerified ? 'Dikonfirmasi' : 'Verifikasi', icon: 'pi pi-check-circle' },
    { key: 'dikemas', label: 'Dikemas', icon: 'pi pi-box' },
    { key: 'dikirim', label: 'Dikirim', icon: 'pi pi-truck' },
    { key: 'selesai', label: 'Selesai', icon: 'pi pi-verified' },
  ];
});

const currentStepIndex = computed(() => {
  if (props.status === 'cancelled') return 2;
  switch (props.status) {
    case 'awaiting_verification': return props.isVerified ? 1 : 0;
    case 'processing': return 1;
    case 'packed': return 2;
    case 'shipped': return 3;
    case 'cod_meeting': return props.shippingMethod === 'cod' ? 4 : 3;
    case 'completed': return props.shippingMethod === 'cod' ? 5 : 4;
    default: return 0;
  }
});
</script>

<template>
  <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm mb-6">
    <div class="relative flex items-center justify-between max-w-2xl mx-auto">
      <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 z-0" />

      <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 transition-all duration-500 z-0"
        :class="status === 'cancelled' ? 'bg-red-500' : 'bg-blue-600'"
        :style="{ width: `${(currentStepIndex / (steps.length - 1)) * 100}%` }" />

      <div v-for="(step, idx) in steps" :key="step.key" class="relative z-10 flex flex-col items-center group">
        <div
          class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border-2 font-bold text-sm"
          :class="[
            idx < currentStepIndex
              ? 'bg-blue-600 border-blue-600 text-white'
              : idx === currentStepIndex
                ? (status === 'cancelled' ? 'bg-red-600 border-red-600 text-white' : 'bg-blue-600 border-blue-600 text-white ring-4 ring-blue-100')
                : 'bg-gray-100 border-gray-300 text-gray-400'
          ]">
          <i :class="step.icon" class="text-base"></i>
        </div>
        <span class="mt-2 text-xs font-semibold"
          :class="idx <= currentStepIndex ? (status === 'cancelled' && idx === currentStepIndex ? 'text-red-600' : 'text-blue-600') : 'text-gray-400'">
          {{ step.label }}
        </span>
      </div>
    </div>
  </div>
</template>