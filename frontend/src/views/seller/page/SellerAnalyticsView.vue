<script setup lang="ts">
import Button from 'primevue/button'
import Chart from 'primevue/chart'
import Dropdown from 'primevue/dropdown'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { onMounted, ref, watch } from 'vue'

import type { SellerTopProduct } from '@/types/entities'
import { useRouter } from 'vue-router'

const router = useRouter()
const toast = useToast()

const isLoadingGet = ref(true)
const selectedPeriod = ref('month')

const periodOptions = [
  { label: 'Periode: Hari Ini', value: 'today' },
  { label: 'Periode: 7 Hari Terakhir', value: 'week' },
  { label: 'Periode: Bulan Ini', value: 'month' },
  { label: 'Periode: Tahun Ini', value: 'year' }
]

// Mock Metrics Overview
const overviewMetrics = ref({
  totalRevenue: 'Rp 15.450.000',
  revenueGrowth: '+12% dari bulan lalu',
  totalOrders: '128 Order',
  ordersGrowth: '+5% dari bulan lalu',
  productsSold: '340 Unit',
  conversionRate: '3.2%'
})

// Mock Top 5 Products
const topProducts = ref<SellerTopProduct[]>([])

// PrimeVue Chart Configs
const barChartData = ref()
const barChartOptions = ref()
const donutChartData = ref()
const donutChartOptions = ref()

// ----------------------------------------------------------------
// 1. SETUP DATA & PRIMEVUE V4 CHART CONFIG
// ----------------------------------------------------------------
const initCharts = () => {
  // Bar Chart Data (Grafik Penjualan 30 Hari Terakhir)
  barChartData.value = {
    labels: ['1', '4', '7', '10', '13', '16', '19', '22', '25', '28', '30'],
    datasets: [
      {
        label: 'Pendapatan (Rp)',
        backgroundColor: '#3b82f6',
        borderRadius: 6,
        data: [12, 19, 8, 22, 15, 28, 14, 31, 24, 38, 42]
      },
      {
        label: 'Profit (Rp)',
        backgroundColor: '#10b981',
        borderRadius: 6,
        data: [7, 11, 4, 14, 9, 18, 8, 20, 15, 25, 29]
      }
    ]
  }

  barChartOptions.value = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { color: '#94a3b8' }
      },
      y: {
        grid: { color: '#f1f5f9' },
        ticks: { display: false }
      }
    }
  }

  // Donut Chart Data (Kategori Terlaris)
  donutChartData.value = {
    labels: ['Makanan & Minuman', 'Fashion', 'Elektronik', 'Lainnya'],
    datasets: [
      {
        data: [40, 30, 20, 10],
        backgroundColor: ['#2563eb', '#f59e0b', '#10b981', '#cbd5e1'],
        borderWidth: 0
      }
    ]
  }

  donutChartOptions.value = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '75%',
    plugins: {
      legend: { display: false }
    }
  }
}

// ----------------------------------------------------------------
// 2. SIMULASI GET DATA ANALYTICS (Full Screen Circular Loader)
// ----------------------------------------------------------------
const fetchAnalyticsData = () => {
  isLoadingGet.value = true
  setTimeout(() => {
    topProducts.value = [
      { id: 1, name: 'Kopi Luwak Premium 200g', slug: 'kopi-luwak', total_qty_sold: 145, revenue: 'Rp 4.350.000', profit: 'Rp 1.450.000' },
      { id: 2, name: 'Batik Tulis Pekalongan', slug: 'batik-tulis', total_qty_sold: 82, revenue: 'Rp 3.690.000', profit: 'Rp 1.100.000' },
      { id: 3, name: 'Sambal Roa Manado Asli', slug: 'sambal-roa', total_qty_sold: 76, revenue: 'Rp 1.140.000', profit: 'Rp 380.000' }
    ]

    initCharts()
    isLoadingGet.value = false
  }, 1000)
}

// ----------------------------------------------------------------
// 3. EXPORT LAPORAN & NAVIGASI
// ----------------------------------------------------------------
const handleExportReport = () => {
  toast.add({
    severity: 'success',
    summary: 'Export Laporan',
    detail: `Laporan analitik (${selectedPeriod.value}) berhasil diunduh (CSV).`,
    life: 3000
  })
}

const goToTopProductsPage = () => {
  router.push('/seller/analitik/top-products')
}

watch(selectedPeriod, () => {
  fetchAnalyticsData()
})

onMounted(() => {
  fetchAnalyticsData()
})
</script>

<template>
  <div class="relative min-h-[80vh]">
    <Transition name="fade">
      <div v-if="isLoadingGet"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
        <ProgressSpinner style="width: 56px; height: 56px" strokeWidth="4" fill="transparent" animationDuration=".8s" />
        <p class="mt-4 font-medium text-slate-600 text-sm animate-pulse">
          Memuat Laporan & Analitik Penjualan...
        </p>
      </div>
    </Transition>

    <div v-if="!isLoadingGet" class="max-w-6xl mx-auto space-y-6 pb-12">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Ringkasan Performa Toko</h1>
          <p class="text-xs text-slate-500 mt-1">
            Pantau metrik penjualan dan perilaku pembeli secara real-time.
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <Dropdown v-model="selectedPeriod" :options="periodOptions" optionLabel="label" optionValue="value"
            class="bg-white! border-slate-200! rounded-xl! text-xs!" />
          <Button label="Export Laporan" icon="pi pi-download" outlined
            class="border-blue-600! text-blue-600! hover:bg-blue-50! rounded-xl! text-xs! font-semibold! py-2.5!"
            @click="handleExportReport" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pendapatan</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="pi pi-wallet text-sm"></i>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ overviewMetrics.totalRevenue }}</h3>
            <span
              class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md mt-2">
              <i class="pi pi-arrow-up-right text-[10px]"></i> {{ overviewMetrics.revenueGrowth }}
            </span>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pesanan</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <i class="pi pi-shopping-bag text-sm"></i>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ overviewMetrics.totalOrders }}</h3>
            <span
              class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md mt-2">
              <i class="pi pi-arrow-up-right text-[10px]"></i> {{ overviewMetrics.ordersGrowth }}
            </span>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Produk Terjual</span>
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
              <i class="pi pi-box text-sm"></i>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ overviewMetrics.productsSold }}</h3>
            <p class="text-[11px] text-slate-400 mt-2">Total unit produk sukses terkirim</p>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat Konversi</span>
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
              <i class="pi pi-chart-line text-sm"></i>
            </div>
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ overviewMetrics.conversionRate }}</h3>
            <p class="text-[11px] text-slate-400 mt-2">Pengunjung yang melakukan transaksi</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div
          class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-900">Grafik Penjualan 30 Hari Terakhir</h2>
            <div class="flex items-center gap-4 text-xs font-medium">
              <span class="flex items-center gap-1.5 text-slate-600">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Pendapatan
              </span>
              <span class="flex items-center gap-1.5 text-slate-600">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Profit
              </span>
            </div>
          </div>

          <div class="h-64 relative">
            <Chart type="bar" :data="barChartData" :options="barChartOptions" class="h-full w-full" />
          </div>
        </div>

        <div
          class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
          <div>
            <h2 class="text-base font-bold text-slate-900 mb-2">Kategori Terlaris</h2>
            <div class="h-44 relative my-2 flex items-center justify-center">
              <Chart type="doughnut" :data="donutChartData" :options="donutChartOptions" class="h-full" />
              <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-xl font-bold text-slate-900">40%</span>
                <span class="text-[10px] text-slate-400 font-medium">Makanan</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-3 border-t border-slate-100">
            <div class="flex items-center gap-1.5 text-slate-600">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Makanan (40%)
            </div>
            <div class="flex items-center gap-1.5 text-slate-600">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Fashion (30%)
            </div>
            <div class="flex items-center gap-1.5 text-slate-600">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Elektronik (20%)
            </div>
            <div class="flex items-center gap-1.5 text-slate-600">
              <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Lainnya (10%)
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-slate-100">
          <h2 class="text-base font-bold text-slate-900">Top 5 Produk Terlaris</h2>
          <button @click="goToTopProductsPage"
            class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1">
            <span>Lihat Semua</span>
            <i class="pi pi-chevron-right text-[10px]"></i>
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead
              class="bg-slate-50/70 text-slate-400 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-100">
              <tr>
                <th class="py-3.5 px-6">Produk</th>
                <th class="py-3.5 px-4">Kategori</th>
                <th class="py-3.5 px-4 text-center">Terjual</th>
                <th class="py-3.5 px-4">Pendapatan</th>
                <th class="py-3.5 px-6 text-center">Stok</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in topProducts" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="py-4 px-6 font-semibold text-slate-800">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                      <i class="pi pi-box text-slate-400"></i>
                    </div>
                    <div>
                      <p class="text-xs font-bold text-slate-800">{{ item.name }}</p>
                      <p class="text-[10px] text-slate-400">SKU: KLP-001</p>
                    </div>
                  </div>
                </td>
                <td class="py-4 px-4 text-slate-500">Makanan & Minuman</td>
                <td class="py-4 px-4 text-center font-bold text-slate-800">{{ item.total_qty_sold }}</td>
                <td class="py-4 px-4 font-bold text-blue-600">{{ item.revenue }}</td>
                <td class="py-4 px-6 text-center">
                  <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>