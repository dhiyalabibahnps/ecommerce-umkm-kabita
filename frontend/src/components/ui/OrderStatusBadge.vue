<script setup lang="ts">
import type { OrderStatus } from '@/types/enums';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    status: OrderStatus | string;
    size?: 'small' | 'normal' | 'large';
    role?: 'buyer' | 'seller' | 'admin';
  }>(),
  {
    size: 'normal',
    role: 'buyer',
  }
);

const config = computed(() => {
  switch (props.status) {
    case 'awaiting_verification':
      return {
        label: props.role === 'seller' ? 'Menunggu Verifikasi' : 'Pembayaran Sedang Diverifikasi',
        classes: 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-500/10',
        dot: 'bg-amber-500',
        icon: 'pi pi-clock',
      };
    case 'processing':
      return {
        label: 'Dikonfirmasi',
        classes: 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-500/10',
        dot: 'bg-blue-500',
        icon: 'pi pi-check-circle',
      };
    case 'packed':
      return {
        label: 'Dikemas',
        classes: 'bg-purple-50 text-purple-700 border-purple-200 ring-purple-500/10',
        dot: 'bg-purple-500',
        icon: 'pi pi-box',
      };
    case 'shipped':
      return {
        label: 'Dikirim',
        classes: 'bg-cyan-50 text-cyan-800 border-cyan-200 ring-cyan-500/10',
        dot: 'bg-cyan-500',
        icon: 'pi pi-truck',
      };
    case 'cod_meeting':
      return {
        label: 'Ketemuan (COD)',
        classes: 'bg-orange-50 text-orange-700 border-orange-200 ring-orange-500/10',
        dot: 'bg-orange-500',
        icon: 'pi pi-map-marker',
      };
    case 'completed':
      return {
        label: 'Selesai',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-500/10',
        dot: 'bg-emerald-500',
        icon: 'pi pi-verified',
      };
    case 'cancelled':
      return {
        label: 'Dibatalkan',
        classes: 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/10',
        dot: 'bg-rose-500',
        icon: 'pi pi-times-circle',
      };
    default:
      return {
        label: String(props.status || 'Memproses'),
        classes: 'bg-slate-50 text-slate-700 border-slate-200 ring-slate-500/10',
        dot: 'bg-slate-400',
        icon: 'pi pi-info-circle',
      };
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'small':
      return 'text-[11px] px-2 py-0.5 gap-1.5';
    case 'large':
      return 'text-xs px-3 py-1.5 gap-2 font-bold';
    default:
      return 'text-xs px-2.5 py-1 gap-1.5 font-medium';
  }
});
</script>

<template>
  <span
    class="inline-flex items-center rounded-full border shadow-2xs transition-colors shrink-0"
    :class="[config.classes, sizeClasses]"
  >
    <span class="h-1.5 w-1.5 rounded-full" :class="config.dot"></span>
    <span>{{ config.label }}</span>
  </span>
</template>
