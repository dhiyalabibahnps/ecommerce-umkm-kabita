<script setup lang="ts">
import Chart from 'primevue/chart';
import { onMounted, ref } from 'vue';

const chartData = ref();
const chartOptions = ref();

onMounted(() => {
  chartData.value = {
    labels: ['Fashion', 'Makanan', 'Elektronik', 'Kecantikan', 'Kesehatan', 'Lainnya'],
    datasets: [
      {
        label: 'Revenue (Juta Rp)',
        data: [11.5, 9.8, 7.5, 5.8, 5.0, 2.1],
        backgroundColor: [
          '#2563eb', // Fashion (Blue)
          '#10b981', // Makanan (Green)
          '#c2410c', // Elektronik (Orange)
          '#a855f7', // Kecantikan (Purple)
          '#ec4899', // Kesehatan (Pink)
          '#eab308'  // Lainnya (Yellow)
        ],
        borderRadius: 6,
        barThickness: 28
      }
    ]
  };

  chartOptions.value = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (context: any) => ` Rp ${context.raw} Juta`
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { size: 11 }, color: '#64748b' }
      },
      y: {
        beginAtZero: true,
        max: 15,
        grid: { color: '#f1f5f9' },
        ticks: {
          stepSize: 5,
          font: { size: 11 },
          color: '#64748b',
          callback: (value: number) => `${value}jt`
        }
      }
    }
  };
});
</script>

<template>
  <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex flex-col">
    <h3 class="text-base font-bold text-slate-800 mb-4">Revenue per Kategori</h3>
    <div class="h-72 w-full relative">
      <Chart type="bar" :data="chartData" :options="chartOptions" class="h-full w-full" />
    </div>
  </div>
</template>