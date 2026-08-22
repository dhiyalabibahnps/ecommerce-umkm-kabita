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
      { key: 'dipesan', label: 'Dipesan', icon: 'pi pi-shopping-bag' },
      { key: 'dibatalkan', label: 'Dibatalkan', icon: 'pi pi-times-circle' },
    ];
  }

  if (props.shippingMethod === 'cod') {
    return [
      { key: 'dipesan', label: 'Dipesan', icon: 'pi pi-shopping-bag' },
      { key: 'dikonfirmasi', label: 'Dikonfirmasi', icon: 'pi pi-check' },
      { key: 'dikemas', label: 'Dikemas', icon: 'pi pi-box' },
      { key: 'ketemuan', label: 'Ketemuan', icon: 'pi pi-map-marker' },
      { key: 'selesai', label: 'Selesai', icon: 'pi pi-verified' },
    ];
  }

  return [
    { key: 'dipesan', label: 'Dipesan', icon: 'pi pi-shopping-bag' },
    { key: 'dikonfirmasi', label: 'Dikonfirmasi', icon: 'pi pi-check' },
    { key: 'dikemas', label: 'Dikemas', icon: 'pi pi-box' },
    { key: 'dikirim', label: 'Dikirim', icon: 'pi pi-truck' },
    { key: 'selesai', label: 'Selesai', icon: 'pi pi-verified' },
  ];
});

const currentStepIndex = computed(() => {
  if (props.status === 'cancelled') return 1;
  switch (props.status) {
    case 'awaiting_verification':
      return 0;
    case 'processing':
      return 1;
    case 'packed':
      return 2;
    case 'shipped':
      return 3;
    case 'cod_meeting':
      return props.shippingMethod === 'cod' ? 3 : 3;
    case 'completed':
      return props.shippingMethod === 'cod' ? 4 : 4;
    default:
      return 0;
  }
});
</script>

<template>
  <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs mb-5">
    <div class="relative flex items-center justify-between mx-auto px-2">
      <!-- Background Line -->
      <div class="absolute left-6 right-6 top-4 h-0.5 bg-slate-200 z-0"></div>

      <!-- Active Progress Line -->
      <div class="absolute left-6 top-4 h-0.5 transition-all duration-500 z-0"
        :class="status === 'cancelled' ? 'bg-rose-500' : 'bg-blue-600'" :style="{
          width: steps.length > 1 ? `calc(${((currentStepIndex) / (steps.length - 1)) * 100}% - 3rem)` : '0%'
        }"></div>

      <!-- Step Items -->
      <div v-for="(step, idx) in steps" :key="step.key" class="relative z-10 flex flex-col items-center group min-w-16">
        <div
          class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all duration-300"
          :class="[
            idx < currentStepIndex
              ? 'border-blue-600 bg-blue-600 text-white shadow-2xs'
              : idx === currentStepIndex
                ? (status === 'cancelled'
                  ? 'border-rose-600 bg-rose-600 text-white ring-4 ring-rose-100'
                  : 'border-blue-600 bg-blue-600 text-white ring-4 ring-blue-100 shadow-xs')
                : 'border-slate-200 bg-white text-slate-400'
          ]">
          <i v-if="idx < currentStepIndex" class="pi pi-check text-[10px]"></i>
          <i v-else-if="idx === currentStepIndex" :class="[step.icon, 'text-[11px]']"></i>
          <span v-else class="text-[10px]">{{ idx + 1 }}</span>
        </div>
        <span class="mt-1.5 text-[11px] font-semibold transition text-center" :class="[
          idx === currentStepIndex
            ? (status === 'cancelled' ? 'text-rose-600 font-bold' : 'text-blue-600 font-bold')
            : idx < currentStepIndex
              ? 'text-slate-800'
              : 'text-slate-400'
        ]">
          {{ step.label }}
        </span>
      </div>
    </div>
  </div>
</template>
