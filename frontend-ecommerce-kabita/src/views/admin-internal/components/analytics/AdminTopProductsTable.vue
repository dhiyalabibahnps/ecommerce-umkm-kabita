<script setup lang="ts">
export interface TopProductItem {
  rank: number;
  productName: string;
  imageUrl?: string;
  shopName: string;
  qtySold: number;
  revenue: string;
  profit: string;
}

defineProps<{
  products: TopProductItem[];
}>();

const getRankBadgeClass = (rank: number) => {
  switch (rank) {
    case 1:
      return 'bg-amber-400 text-white font-bold'; // Gold
    case 2:
      return 'bg-slate-300 text-slate-700 font-bold'; // Silver
    case 3:
      return 'bg-amber-600 text-white font-bold'; // Bronze
    default:
      return 'bg-slate-100 text-slate-500 font-medium';
  }
};
</script>

<template>
  <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100">
      <h3 class="text-base font-bold text-slate-800">Top 10 Produk Terlaris</h3>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/70 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100">
            <th class="py-3 px-5 font-semibold">RANK</th>
            <th class="py-3 px-5 font-semibold">PRODUK</th>
            <th class="py-3 px-5 font-semibold">TOKO</th>
            <th class="py-3 px-5 font-semibold text-center">QTY SOLD</th>
            <th class="py-3 px-5 font-semibold text-right">REVENUE</th>
            <th class="py-3 px-5 font-semibold text-right">PROFIT</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          <tr v-for="item in products" :key="item.rank" class="hover:bg-slate-50/50 transition">
            <td class="py-3.5 px-5">
              <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs"
                :class="getRankBadgeClass(item.rank)">
                {{ item.rank }}
              </span>
            </td>

            <td class="py-3.5 px-5">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                  <img v-if="item.imageUrl" :src="item.imageUrl" :alt="item.productName"
                    class="w-full h-full object-cover" />
                  <i v-else class="pi pi-box text-slate-400"></i>
                </div>
                <span class="font-semibold text-slate-800">{{ item.productName }}</span>
              </div>
            </td>

            <td class="py-3.5 px-5 text-slate-600">
              {{ item.shopName }}
            </td>

            <td class="py-3.5 px-5 text-center font-medium text-slate-700">
              {{ item.qtySold }}
            </td>

            <td class="py-3.5 px-5 text-right font-medium text-slate-800">
              {{ item.revenue }}
            </td>

            <td class="py-3.5 px-5 text-right font-semibold text-emerald-600">
              {{ item.profit }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>