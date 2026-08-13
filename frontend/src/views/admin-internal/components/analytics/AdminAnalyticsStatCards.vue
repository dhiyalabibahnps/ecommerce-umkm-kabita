<script setup lang="ts">
export interface StatMetric {
  title: string;
  value: string;
  percentage: string;
  isPositive: boolean;
  icon: string;
}

defineProps<{
  stats: StatMetric[];
}>();
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div v-for="(card, index) in stats" :key="index"
      class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <span class="text-xs font-semibold text-slate-500 tracking-wide uppercase">{{ card.title }}</span>
        <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600">
          <i :class="card.icon" class="text-lg"></i>
        </div>
      </div>

      <div class="mt-3">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">{{ card.value }}</h2>
        <div class="flex items-center gap-1.5 mt-2">
          <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full"
            :class="card.isPositive ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
            <i :class="card.isPositive ? 'pi pi-arrow-up' : 'pi pi-arrow-down'" class="text-[10px] mr-1"></i>
            {{ card.percentage }}
          </span>
          <span class="text-xs text-slate-400">vs bln lalu</span>
        </div>
      </div>
    </div>
  </div>
</template>