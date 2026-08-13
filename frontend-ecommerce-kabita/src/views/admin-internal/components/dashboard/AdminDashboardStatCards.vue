<script setup lang="ts">
import type { PlatformStats } from '@/types/entities';

interface Props {
  stats: PlatformStats
}

defineProps<Props>()

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(num || 0)
}
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total User</span>
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <i class="pi pi-users text-lg"></i>
        </div>
      </div>
      <div class="flex items-baseline justify-between">
        <h3 class="text-2xl font-bold text-slate-800">{{ stats.total_users.toLocaleString('id-ID') }}</h3>
        <span
          class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1">
          <i class="pi font-bold text-[10px] pi-arrow-up-right"></i> +12%
        </span>
      </div>
      <p class="text-xs text-slate-400 mt-2">
        Buyer: <span class="font-medium text-slate-600">{{ stats.users_by_role?.buyer || 0 }}</span> |
        Seller: <span class="font-medium text-slate-600">{{ stats.users_by_role?.seller || 0 }}</span>
      </p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Toko</span>
        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
          <i class="pi pi-shop text-lg"></i>
        </div>
      </div>
      <div class="flex items-baseline justify-between">
        <h3 class="text-2xl font-bold text-slate-800">{{ stats.total_shops.toLocaleString('id-ID') }}</h3>
        <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
          {{ stats.pending_shops }} Pending
        </span>
      </div>
      <p class="text-xs text-slate-400 mt-2">
        Terverifikasi: <span class="font-medium text-emerald-600">{{ stats.verified_shops }} Toko</span>
      </p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Produk</span>
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
          <i class="pi pi-box text-lg"></i>
        </div>
      </div>
      <div class="flex items-baseline justify-between">
        <h3 class="text-2xl font-bold text-slate-800">{{ stats.total_products.toLocaleString('id-ID') }}</h3>
        <span
          class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1">
          <i class="pi font-bold text-[10px] pi-arrow-up-right"></i> +8.4%
        </span>
      </div>
      <p class="text-xs text-slate-400 mt-2">Aktif dalam katalog platform</p>
    </div>

    <div class="bg-linear-to-br from-blue-600 to-indigo-700 p-5 rounded-2xl shadow-lg shadow-blue-500/20 text-white">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-blue-100 uppercase tracking-wider">Pendapatan Platform</span>
        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xs text-white flex items-center justify-center">
          <i class="pi pi-wallet text-lg"></i>
        </div>
      </div>
      <h3 class="text-2xl font-bold tracking-tight mb-1">
        {{ formatRupiah(stats.platform_revenue) }}
      </h3>
      <div class="flex items-center gap-1.5 text-xs text-blue-100 mt-3">
        <span class="px-1.5 py-0.5 rounded-md bg-white/20 font-semibold text-[11px]">+18.5%</span>
        <span>dibanding bulan lalu</span>
      </div>
    </div>
  </div>
</template>