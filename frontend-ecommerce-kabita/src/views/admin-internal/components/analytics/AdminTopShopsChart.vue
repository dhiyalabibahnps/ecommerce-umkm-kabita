<script setup lang="ts">
import Chart from 'primevue/chart';
import { onMounted, ref } from 'vue';

const chartData = ref();
const chartOptions = ref();

onMounted(() => {
  chartData.value = {
    labels: ['Toko A', 'Toko B', 'Toko C', 'Toko D', 'Toko E', 'Toko F', 'Toko G', 'Toko H'],
    datasets: [
      {
        label: 'Revenue',
        data: [92, 85, 72, 62, 58, 48, 35, 25],
        backgroundColor: '#3b82f6',
        borderRadius: 6,
        barThickness: 12
      }
    ]
  };

  chartOptions.value = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      x: {
        display: false,
        max: 100
      },
      y: {
        grid: { display: false },
        ticks: { font: { size: 12 }, color: '#475569' }
      }
    }
  };
});
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col">
    <h3 class="text-base font-bold text-slate-800 mb-4">Top 10 Toko (Revenue)</h3>
    <div class="h-72 w-full relative">
      <Chart type="bar" :data="chartData" :options="chartOptions" class="h-full w-full" />
    </div>
  </div>
</template>