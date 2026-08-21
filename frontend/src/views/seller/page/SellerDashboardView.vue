<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'

import OrderStatusBadge from '@/components/ui/OrderStatusBadge.vue'
import { formatCourierDisplay } from '@/constants/courier'
import { getApiErrorMessage } from '@/services/apiError'
import { sellerAnalyticsService } from '@/services/sellerAnalyticsService'
import { sellerOrderService } from '@/services/sellerOrderService'
import type { AnalyticsSalesRow, Order, SellerOverview } from '@/types'
import { formatRupiah } from '@/utils/format'

const router = useRouter()
const toast = useToast()

const isLoading = ref<boolean>(true)
const isSalesLoading = ref<boolean>(false)
const actionLoadingId = ref<number | null>(null)

// Stats Overview
const sellerOverview = ref<SellerOverview>({
  total_products: 0,
  total_orders: 0,
  total_revenue: '0',
  pending_orders_count: 0,
})

// Sales Chart Data
const salesAnalytics = ref<AnalyticsSalesRow[]>([])

// Period Filter for Sales Performance
type PeriodKey = '7days' | '30days' | 'month' | 'today'

interface PeriodOption {
  label: string
  value: PeriodKey
  title: string
}

const periodOptions: PeriodOption[] = [
  { label: '7 Hari Terakhir', value: '7days', title: 'Performa Penjualan (7 Hari)' },
  { label: '30 Hari Terakhir', value: '30days', title: 'Performa Penjualan (30 Hari)' },
  { label: 'Bulan Ini', value: 'month', title: 'Performa Penjualan (Bulan Ini)' },
  { label: 'Hari Ini', value: 'today', title: 'Performa Penjualan (Hari Ini)' },
]

const selectedPeriod = ref<PeriodKey>('7days')

// Actionable Orders (processing / awaiting verification)
const actionableOrders = ref<Order[]>([])

// Recent Orders
const recentOrders = ref<Order[]>([])

const formatYmd = (d: Date): string => {
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const getDateFilterForPeriod = (period: PeriodKey) => {
  const now = new Date()
  const endDate = formatYmd(now)

  if (period === 'today') {
    return { period: 'daily' as const, start_date: endDate, end_date: endDate }
  }
  if (period === '7days') {
    const start = new Date(now)
    start.setDate(start.getDate() - 6)
    return { period: 'daily' as const, start_date: formatYmd(start), end_date: endDate }
  }
  if (period === '30days') {
    const start = new Date(now)
    start.setDate(start.getDate() - 29)
    return { period: 'daily' as const, start_date: formatYmd(start), end_date: endDate }
  }
  // 'month'
  const start = new Date(now.getFullYear(), now.getMonth(), 1)
  return { period: 'daily' as const, start_date: formatYmd(start), end_date: endDate }
}

const fetchSalesData = async () => {
  isSalesLoading.value = true
  try {
    const filters = getDateFilterForPeriod(selectedPeriod.value)
    const salesData = await sellerAnalyticsService.getSales(filters)
    salesAnalytics.value = Array.isArray(salesData) ? salesData : []
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal Memuat Penjualan',
      detail: getApiErrorMessage(error, 'Gagal memuat data grafik performa penjualan.'),
      life: 3000,
    })
  } finally {
    isSalesLoading.value = false
  }
}

const fetchDashboardData = async () => {
  isLoading.value = true
  try {
    const [overviewData, recentRes, actionableRes] = await Promise.allSettled([
      sellerAnalyticsService.getOverview(),
      sellerOrderService.list({ per_page: 5 }),
      sellerOrderService.list({ status: 'processing', per_page: 5 }),
    ])

    if (overviewData.status === 'fulfilled') {
      sellerOverview.value = overviewData.value
    }

    if (recentRes.status === 'fulfilled') {
      recentOrders.value = recentRes.value.data || []
    }

    if (actionableRes.status === 'fulfilled') {
      let orders = actionableRes.value.data || []
      // If processing orders is empty, check awaiting_verification
      if (orders.length === 0) {
        try {
          const awaitingRes = await sellerOrderService.list({ status: 'awaiting_verification', per_page: 5 })
          orders = awaitingRes.data || []
        } catch {
          // silent fallback
        }
      }
      actionableOrders.value = orders
    }

    await fetchSalesData()
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal Memuat Dashboard',
      detail: getApiErrorMessage(error, 'Terjadi kesalahan saat memuat data dashboard.'),
      life: 3500,
    })
  } finally {
    isLoading.value = false
  }
}

// Watch period changes
watch(selectedPeriod, () => {
  fetchSalesData()
})

const handlePackOrder = async (order: Order) => {
  actionLoadingId.value = order.id
  try {
    await sellerOrderService.pack(order.id)
    toast.add({
      severity: 'success',
      summary: 'Pesanan Dikemas',
      detail: `Pesanan ${order.order_number} berhasil dikemas dan siap dikirim.`,
      life: 3000,
    })
    // Refresh actionable orders & overview
    const [actRes, ovRes] = await Promise.allSettled([
      sellerOrderService.list({ status: 'processing', per_page: 5 }),
      sellerAnalyticsService.getOverview(),
    ])
    if (actRes.status === 'fulfilled') actionableOrders.value = actRes.value.data || []
    if (ovRes.status === 'fulfilled') sellerOverview.value = ovRes.value
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal mengubah status pesanan menjadi dikemas.'),
      life: 4000,
    })
  } finally {
    actionLoadingId.value = null
  }
}

// Chart calculations
const currentPeriodTitle = computed(() => {
  const opt = periodOptions.find((o) => o.value === selectedPeriod.value)
  return opt ? opt.title : 'Performa Penjualan'
})

const totalPeriodRevenue = computed(() => {
  return salesAnalytics.value.reduce((sum, item) => sum + (parseFloat(item.total_revenue || '0') || 0), 0)
})

const totalPeriodOrders = computed(() => {
  return salesAnalytics.value.reduce((sum, item) => sum + (item.orders_count || 0), 0)
})

const maxChartOrders = computed(() => {
  if (!salesAnalytics.value.length) return 1
  const max = Math.max(...salesAnalytics.value.map((i) => i.orders_count || 0))
  return max > 0 ? max : 1
})

const formatChartDateLabel = (dateStr?: string): string => {
  if (!dateStr) return '-'
  try {
    const parts = dateStr.split('-')
    if (parts.length === 3 && parts[1] && parts[2]) {
      const day = parts[2]
      const monthIdx = parseInt(parts[1], 10) - 1
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
      return `${day} ${months[monthIdx] || ''}`
    }
    return dateStr
  } catch {
    return dateStr
  }
}

const formatDate = (val?: string): string => {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

onMounted(fetchDashboardData)
</script>

<template>
  <div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Dashboard Toko</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau aktivitas toko dan pesanan masuk secara real-time.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <Button
          label="Tambah Produk"
          icon="pi pi-plus"
          size="small"
          class="bg-blue-600! border-blue-600! text-xs! font-bold! rounded-lg!"
          @click="router.push('/seller/produk/tambah')"
        />
        <Button
          label="Kelola Pesanan"
          icon="pi pi-box"
          size="small"
          severity="secondary"
          outlined
          class="text-xs! font-semibold! rounded-lg!"
          @click="router.push('/seller/pesanan')"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 text-slate-400">
      <ProgressSpinner style="width: 40px; height: 40px" />
      <p class="mt-3 text-xs">Memuat data dashboard...</p>
    </div>

    <template v-else>
      <!-- Stats Overview 4 Cards -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Card 1: Total Produk -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500">Total Produk</span>
            <div class="h-9 w-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <i class="pi pi-box text-sm"></i>
            </div>
          </div>
          <div class="mt-2.5">
            <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
              {{ sellerOverview.total_products }}
            </span>
            <span class="text-[11px] text-slate-400 block mt-0.5">Produk di etalase toko</span>
          </div>
        </div>

        <!-- Card 2: Total Pesanan Bulan Ini -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500">Total Pesanan</span>
            <div class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <i class="pi pi-shopping-bag text-sm"></i>
            </div>
          </div>
          <div class="mt-2.5">
            <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
              {{ sellerOverview.total_orders }}
            </span>
            <span class="text-[11px] text-slate-400 block mt-0.5">Pesanan bulan ini</span>
          </div>
        </div>

        <!-- Card 3: Pendapatan Bulan Ini -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs hover:border-slate-300 transition">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500">Pendapatan</span>
            <div class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <i class="pi pi-wallet text-sm"></i>
            </div>
          </div>
          <div class="mt-2.5">
            <span class="text-lg sm:text-xl font-black text-slate-900 tracking-tight block truncate">
              {{ formatRupiah(sellerOverview.total_revenue) }}
            </span>
            <span class="text-[11px] text-slate-400 block mt-0.5">Estimasi bulan ini</span>
          </div>
        </div>

        <!-- Card 4: Menunggu Verifikasi / Tindakan -->
        <div
          class="rounded-2xl border p-4 sm:p-5 shadow-2xs transition cursor-pointer"
          :class="sellerOverview.pending_orders_count > 0 ? 'border-amber-200 bg-amber-50/50 hover:bg-amber-50' : 'border-slate-200/80 bg-white hover:border-slate-300'"
          @click="router.push('/seller/pesanan')"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold" :class="sellerOverview.pending_orders_count > 0 ? 'text-amber-800' : 'text-slate-500'">
              Perlu Verifikasi
            </span>
            <div
              class="h-9 w-9 rounded-xl flex items-center justify-center"
              :class="sellerOverview.pending_orders_count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'"
            >
              <i class="pi pi-clock text-sm"></i>
            </div>
          </div>
          <div class="mt-2.5">
            <span class="text-xl sm:text-2xl font-black tracking-tight" :class="sellerOverview.pending_orders_count > 0 ? 'text-amber-900' : 'text-slate-900'">
              {{ sellerOverview.pending_orders_count }}
            </span>
            <span class="text-[11px] block mt-0.5" :class="sellerOverview.pending_orders_count > 0 ? 'text-amber-700 font-semibold' : 'text-slate-400'">
              {{ sellerOverview.pending_orders_count > 0 ? 'Pesanan menunggu respon' : 'Semua sudah beres' }}
            </span>
          </div>
        </div>
      </section>

      <!-- Main Layout: 2 Columns -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Sales Chart & Actionable Orders -->
        <div class="lg:col-span-8 space-y-6">
          <!-- Performa Penjualan Card -->
          <section class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
              <div>
                <h2 class="text-sm font-bold text-slate-800">{{ currentPeriodTitle }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Grafik pergerakan jumlah pesanan dan nominal penjualan.</p>
              </div>

              <!-- Filter Periode Dropdown -->
              <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-slate-500 hidden sm:inline">Periode:</span>
                <select
                  v-model="selectedPeriod"
                  class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none cursor-pointer"
                >
                  <option v-for="opt in periodOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Mini Summary for Current Period -->
            <div class="grid grid-cols-2 gap-3 pt-4 pb-2">
              <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Total Pendapatan</span>
                <p class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                  {{ formatRupiah(totalPeriodRevenue) }}
                </p>
              </div>
              <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Total Pesanan</span>
                <p class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                  {{ totalPeriodOrders }} Pesanan
                </p>
              </div>
            </div>

            <!-- Chart Container -->
            <div class="pt-4">
              <div v-if="isSalesLoading" class="flex flex-col items-center justify-center py-16 text-slate-400">
                <ProgressSpinner style="width: 32px; height: 32px" />
                <p class="mt-2 text-xs">Memperbarui grafik penjualan...</p>
              </div>

              <div v-else-if="salesAnalytics.length === 0" class="flex flex-col items-center justify-center py-16 text-slate-400 text-center">
                <i class="pi pi-chart-bar text-3xl text-slate-300"></i>
                <p class="mt-2 text-xs font-medium text-slate-500">Belum ada transaksi pada periode ini.</p>
                <p class="text-[11px] text-slate-400">Transaksi baru akan otomatis muncul di grafik ini.</p>
              </div>

              <div v-else class="space-y-4">
                <!-- Visual Bar Chart -->
                <div class="flex items-end gap-2 sm:gap-3 h-48 sm:h-56 pt-6 pb-2 px-1 sm:px-2 border-b border-slate-100 overflow-x-auto">
                  <div
                    v-for="(row, idx) in salesAnalytics"
                    :key="idx"
                    class="group relative flex-1 min-w-[36px] max-w-[64px] flex flex-col items-center h-full justify-end"
                  >
                    <!-- Tooltip Hover -->
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none absolute -top-12 z-20 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-[10px] text-white shadow-lg">
                      <div class="font-bold">{{ formatRupiah(row.total_revenue || '0') }}</div>
                      <div class="text-slate-300">{{ row.orders_count }} Pesanan ({{ formatChartDateLabel(row.date) }})</div>
                    </div>

                    <!-- Bar -->
                    <div
                      class="w-full rounded-t-lg bg-blue-500 group-hover:bg-blue-600 transition-all flex items-center justify-center relative cursor-pointer"
                      :style="{
                        height: `${Math.max(Math.round(((row.orders_count || 0) / maxChartOrders) * 100), row.orders_count > 0 ? 15 : 6)}%`,
                        backgroundColor: row.orders_count > 0 ? undefined : '#e2e8f0',
                      }"
                    >
                      <span v-if="row.orders_count > 0" class="text-[10px] font-bold text-white mb-1">
                        {{ row.orders_count }}
                      </span>
                    </div>

                    <!-- X-Axis Label -->
                    <span class="mt-2 text-[10px] font-semibold text-slate-400 group-hover:text-slate-700 truncate max-w-full text-center">
                      {{ formatChartDateLabel(row.date) }}
                    </span>
                  </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                  <span>* Menampilkan ringkasan pesanan & total pendapatan per tanggal</span>
                  <button
                    type="button"
                    class="font-semibold text-blue-600 hover:text-blue-700 cursor-pointer inline-flex items-center gap-1"
                    @click="router.push('/seller/analisis')"
                  >
                    <span>Analisis Detail</span>
                    <i class="pi pi-arrow-right text-[9px]"></i>
                  </button>
                </div>
              </div>
            </div>
          </section>

          <!-- Pesanan Perlu Tindakan Card -->
          <section class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
              <div>
                <h2 class="text-sm font-bold text-slate-800">Pesanan Perlu Tindakan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pesanan yang butuh diproses, dikemas, atau dikonfirmasi.</p>
              </div>
              <Button
                label="Lihat Semua"
                severity="secondary"
                outlined
                size="small"
                class="text-xs! rounded-lg!"
                @click="router.push('/seller/pesanan')"
              />
            </div>

            <div class="pt-4">
              <div v-if="actionableOrders.length === 0" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                <i class="pi pi-check-circle text-3xl text-emerald-500"></i>
                <p class="mt-2 text-xs font-bold text-slate-700">Semua pesanan sudah ditangani!</p>
                <p class="text-[11px] text-slate-400">Tidak ada pesanan tertunda yang memerlukan pengemasan saat ini.</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="order in actionableOrders"
                  :key="order.id"
                  class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 hover:border-slate-200 transition"
                >
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="font-mono text-xs font-bold text-slate-800">{{ order.order_number }}</span>
                      <OrderStatusBadge :status="order.status" size="small" role="seller" />
                    </div>
                    <p class="text-xs text-slate-600">
                      Pembeli: <strong class="text-slate-800">{{ order.buyer?.name || 'Pembeli' }}</strong> •
                      Kurir: <span class="text-blue-700 font-semibold">{{ order.shipping_method === 'cod' ? 'COD' : formatCourierDisplay(order.courier) }}</span>
                    </p>
                    <p class="text-[11px] text-slate-400">
                      Total: <span class="font-bold text-slate-800">{{ formatRupiah(order.total_amount) }}</span> • {{ formatDate(order.created_at) }}
                    </p>
                  </div>

                  <div class="flex items-center gap-2 shrink-0">
                    <Button
                      v-if="order.status === 'processing'"
                      label="Kemas Pesanan"
                      icon="pi pi-box"
                      size="small"
                      class="bg-blue-600! border-blue-600! text-xs! font-bold! rounded-lg!"
                      :loading="actionLoadingId === order.id"
                      @click="handlePackOrder(order)"
                    />
                    <Button
                      label="Detail"
                      icon="pi pi-arrow-right"
                      iconPos="right"
                      size="small"
                      severity="secondary"
                      outlined
                      class="text-xs! rounded-lg!"
                      @click="router.push(`/seller/pesanan/${order.id}`)"
                    />
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Right Column: Recent Activity & Quick Shortcuts -->
        <div class="lg:col-span-4 space-y-6">
          <!-- Pesanan Terbaru -->
          <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
              <h2 class="text-sm font-bold text-slate-800">Pesanan Masuk Terbaru</h2>
              <button
                type="button"
                class="text-xs font-semibold text-blue-600 hover:text-blue-700 cursor-pointer"
                @click="router.push('/seller/pesanan')"
              >
                Lihat Semua
              </button>
            </div>

            <div class="pt-3">
              <div v-if="recentOrders.length === 0" class="py-8 text-center text-slate-400 text-xs">
                Belum ada pesanan terbaru.
              </div>

              <div v-else class="divide-y divide-slate-100">
                <div
                  v-for="order in recentOrders"
                  :key="order.id"
                  class="py-3 flex items-start justify-between gap-2 hover:bg-slate-50/80 p-2 rounded-lg transition cursor-pointer"
                  @click="router.push(`/seller/pesanan/${order.id}`)"
                >
                  <div class="min-w-0 space-y-0.5">
                    <p class="font-mono text-xs font-bold text-slate-800 truncate">{{ order.order_number }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ order.buyer?.name || 'Pembeli' }}</p>
                    <p class="text-[11px] font-bold text-slate-800">{{ formatRupiah(order.total_amount) }}</p>
                  </div>
                  <div class="shrink-0 text-right space-y-1">
                    <OrderStatusBadge :status="order.status" size="small" role="seller" />
                    <span class="text-[10px] text-slate-400 block">{{ formatDate(order.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Pintasan Cepat Menu Seller -->
          <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs">
            <h2 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Pintasan Cepat</h2>
            <div class="pt-3 space-y-2">
              <button
                type="button"
                class="w-full flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50/50 hover:border-blue-200 transition text-left cursor-pointer group"
                @click="router.push('/seller/produk')"
              >
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="pi pi-tags text-xs"></i>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition block">Daftar Produk Toko</span>
                    <span class="text-[10px] text-slate-400">Atur stok, harga, dan varian</span>
                  </div>
                </div>
                <i class="pi pi-chevron-right text-xs text-slate-400 group-hover:text-blue-600 transition"></i>
              </button>

              <button
                type="button"
                class="w-full flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50/50 hover:border-blue-200 transition text-left cursor-pointer group"
                @click="router.push('/seller/produk/terlaris')"
              >
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="pi pi-star text-xs"></i>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition block">Produk Terlaris</span>
                    <span class="text-[10px] text-slate-400">Lihat produk dengan penjualan tertinggi</span>
                  </div>
                </div>
                <i class="pi pi-chevron-right text-xs text-slate-400 group-hover:text-blue-600 transition"></i>
              </button>

              <button
                type="button"
                class="w-full flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50/50 hover:border-blue-200 transition text-left cursor-pointer group"
                @click="router.push('/seller/analisis')"
              >
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                    <i class="pi pi-chart-line text-xs"></i>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition block">Laporan Analisis</span>
                    <span class="text-[10px] text-slate-400">Analisis penjualan & laba kotor</span>
                  </div>
                </div>
                <i class="pi pi-chevron-right text-xs text-slate-400 group-hover:text-blue-600 transition"></i>
              </button>

              <button
                type="button"
                class="w-full flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50/50 hover:border-blue-200 transition text-left cursor-pointer group"
                @click="router.push('/seller/pengaturan')"
              >
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-lg bg-slate-200 text-slate-600 flex items-center justify-center">
                    <i class="pi pi-cog text-xs"></i>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition block">Pengaturan Toko</span>
                    <span class="text-[10px] text-slate-400">Informasi toko dan rekening transfer</span>
                  </div>
                </div>
                <i class="pi pi-chevron-right text-xs text-slate-400 group-hover:text-blue-600 transition"></i>
              </button>
            </div>
          </section>
        </div>
      </div>
    </template>
  </div>
</template>
