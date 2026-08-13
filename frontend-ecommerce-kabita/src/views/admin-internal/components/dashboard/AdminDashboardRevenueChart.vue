<script setup lang="ts">
import Chart from 'primevue/chart'
import { computed } from 'vue'

interface Props {
  monthlyTransactions: {
    month: number
    transactions: number
    revenue: string
  }[]
}

const props = defineProps<Props>()

const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

// Format Data untuk PrimeVue Chart Component
const chartData = computed(() => {
  const labels = props.monthlyTransactions.map((item) => monthNames[item.month - 1] || `Bln ${item.month}`)
  const revenues = props.monthlyTransactions.map((item) => parseFloat(item.revenue) / 1000000) // dalam Jutaan Rupiah
  const transactions = props.monthlyTransactions.map((item) => item.transactions)

  return {
    labels,
    datasets: [
      {
        type: 'bar',
        label: 'Pendapatan (Juta Rp)',
        backgroundColor: '#2563eb',
        hoverBackgroundColor: '#1d4ed8',
        borderRadius: 8,
        data: revenues,
        yAxisID: 'y'
      },
      {
        type: 'line',
        label: 'Jumlah Transaksi',
        borderColor: '#f59e0b',
        borderWidth: 3,
        fill: false,
        tension: 0.4,
        pointBackgroundColor: '#f59e0b',
        pointRadius: 4,
        data: transactions,
        yAxisID: 'y1'
      }
    ]
  }
})

// Konfigurasi Opsi Chart PrimeVue
const chartOptions = computed(() => {
  return {
    maintainAspectRatio: false,
    aspectRatio: 0.7,
    plugins: {
      legend: {
        labels: {
          color: '#475569',
          font: { weight: '600', size: 12 }
        },
        position: 'top'
      },
      tooltip: {
        mode: 'index',
        intersect: false
      }
    },
    scales: {
      x: {
        ticks: { color: '#64748b' },
        grid: { display: false }
      },
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        ticks: { color: '#64748b' },
        grid: { color: '#f1f5f9' },
        title: { display: true, text: 'Omset (Juta Rp)', color: '#64748b' }
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        ticks: { color: '#64748b' },
        grid: { drawOnChartArea: false },
        title: { display: true, text: 'Transaksi', color: '#64748b' }
      }
    }
  }
})
</script>

<template>
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h4 class="text-base font-bold text-slate-800">Performa Transaksi & Pendapatan</h4>
        <p class="text-xs text-slate-500">Statistik transaksi seluruh platform tahun berjalan</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700">
          Tahun 2026
        </span>
      </div>
    </div>

    <div class="h-80 w-full">
      <Chart type="bar" :data="chartData" :options="chartOptions" class="h-full w-full" />
    </div>
  </div>
</template>