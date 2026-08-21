<script setup lang="ts">
import type { TopProduct, TopSeller } from '@/types/entities';

interface Props {
  topSellers: TopSeller[]
  topProducts: TopProduct[]
}

defineProps<Props>()

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num || 0)
}
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-base font-bold text-slate-800">Toko Teratas (Top Seller)</h4>
        <span class="text-xs text-slate-400">Berdasarkan Penjualan</span>
      </div>

      <div class="space-y-3">
        <div v-for="(seller, idx) in topSellers" :key="seller.id"
          class="flex items-center justify-between p-3 rounded-xl bg-slate-50/60 border border-slate-100">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
              :class="idx === 0 ? 'bg-amber-100 text-amber-700' : idx === 1 ? 'bg-slate-200 text-slate-700' : 'bg-orange-100 text-orange-700'">
              {{ idx + 1 }}
            </span>
            <div>
              <p class="text-sm font-bold text-slate-800">{{ seller.shop?.name || seller.seller?.name || '-' }}</p>
              <p class="text-xs text-slate-500">{{ seller.total_orders }} Pesanan Selesai</p>
            </div>
          </div>
          <span class="text-sm font-bold text-blue-600">{{ formatRupiah(seller.total_revenue) }}</span>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-base font-bold text-slate-800">Produk Terlaris</h4>
        <span class="text-xs text-slate-400">Volume Terjual</span>
      </div>

      <div class="space-y-3">
        <div v-for="(prod, idx) in topProducts" :key="prod.id"
          class="flex items-center justify-between p-3 rounded-xl bg-slate-50/60 border border-slate-100">
          <div class="flex items-center gap-3">
            <span
              class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs bg-slate-200 text-slate-700">
              {{ idx + 1 }}
            </span>
            <div>
              <p class="text-sm font-bold text-slate-800 line-clamp-1">{{ prod.name }}</p>
              <p class="text-xs text-slate-500">{{ prod.total_sold }} Terjual</p>
            </div>
          </div>
          <span class="text-sm font-bold text-emerald-600">{{ formatRupiah(prod.price) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>