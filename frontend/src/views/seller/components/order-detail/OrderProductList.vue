<script setup lang="ts">
import type { OrderItem } from '@/types';

defineProps<{
  items?: OrderItem[];
  notes?: string | null;
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
  <div class="space-y-4 mb-4">
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs">
      <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Produk</h3>
        <span class="text-[11px] text-slate-500 font-medium">{{ items?.length || 0 }} Produk</span>
      </div>

      <div class="divide-y divide-slate-100">
        <div
          v-for="item in items"
          :key="item.id"
          class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div
              v-if="item.product?.images?.[0]?.url"
              class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-slate-100 bg-slate-50"
            >
              <img
                :src="item.product.images[0].url"
                :alt="item.product.name"
                class="h-full w-full object-cover"
              />
            </div>
            <div
              v-else
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-300"
            >
              <i class="pi pi-box text-base"></i>
            </div>

            <div class="min-w-0 flex-1">
              <h4 class="text-xs font-bold text-slate-800 line-clamp-1">
                {{ item.product?.name || 'Produk' }}
              </h4>
              <p class="text-[11px] text-slate-500 mt-0.5">
                {{ item.quantity }} × {{ formatRupiah(item.price_snapshot) }}
              </p>
            </div>
          </div>

          <div class="text-right shrink-0">
            <span class="text-xs font-bold text-slate-900">
              {{ formatRupiah(parseFloat(item.price_snapshot) * item.quantity) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Catatan Pembeli -->
    <div
      v-if="notes"
      class="rounded-xl bg-amber-50/70 border border-amber-200/80 p-3.5 flex items-start gap-3 shadow-2xs"
    >
      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 mt-0.5">
        <i class="pi pi-comment text-xs"></i>
      </div>
      <div class="min-w-0 flex-1">
        <h5 class="text-xs font-bold text-amber-900">Catatan Pembeli</h5>
        <p class="text-xs text-amber-800/90 italic mt-0.5 leading-relaxed">
          "{{ notes }}"
        </p>
      </div>
    </div>
  </div>
</template>
