<script setup lang="ts">
import Chart from 'primevue/chart';
import { ref, watch } from 'vue';

const props = defineProps<{
  rows: Array<{ date: string; revenue: string }>;
  period: 'daily' | 'weekly' | 'monthly';
}>();

const emit = defineEmits<{ (e: 'update:period', v: 'daily' | 'weekly' | 'monthly'): void }>();

const periodMeta: Record<'daily' | 'weekly' | 'monthly', string> = {
  daily: 'Harian',
  weekly: 'Mingguan',
  monthly: 'Bulanan',
};

const salesPeriod = ref<typeof props.period>(props.period);

watch(() => props.period, (v) => {
  salesPeriod.value = v;
});

const selectPeriod = (p: typeof props.period) => {
  emit('update:period', p);
};

const chartData = ref();
const chartOptions = ref();

watch(() => props.rows, () => {
  chartData.value = {
    labels: props.rows.map((row) => row.date),
    datasets: [
      {
        label: 'Revenue',
        data: props.rows.map((row) => Number(row.revenue)),
        borderColor: '#2563eb',
        borderWidth: 4,
        tension: 0.45,
        pointRadius: 0,
        fill: false
      }
    ]
  };

  chartOptions.value = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { size: 11 }, color: '#64748b' }
      },
      y: {
        beginAtZero: true,
        grid: { color: '#f1f5f9' },
        ticks: {
          font: { size: 11 },
          color: '#64748b',
          callback: (value: number) => {
            if (value === 0) return '0';
            if (value >= 1000000) return `${value / 1000000}jt`;
            if (value >= 1000) return `${value / 1000}k`;
            return value;
          }
        }
      }
    }
  };
}, { immediate: true });
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-bold text-slate-800">Tren Penjualan {{ periodMeta[salesPeriod] }}</h3>

      <div class="flex items-center gap-1 p-1 rounded-lg bg-slate-100">
        <button
          v-for="(label, key) in periodMeta"
          :key="key"
          type="button"
          class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors"
          :class="salesPeriod === key ? 'bg-blue-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
          @click="selectPeriod(key)"
        >
          {{ label }}
        </button>
      </div>
    </div>

    <div class="h-80 w-full relative">
      <Chart type="line" :data="chartData" :options="chartOptions" class="h-full w-full" />
    </div>

    <div class="flex items-center justify-center gap-6 mt-4 pt-3 border-t border-slate-50 text-xs">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-blue-600"></span>
        <span class="text-slate-600 font-medium">Revenue</span>
      </div>
    </div>
  </div>
</template>
