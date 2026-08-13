<script setup lang="ts">
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref } from 'vue'

import type { Order } from '@/types/entities'; // Disesuaikan dengan folder types Anda
import type { OrderStatus } from '@/types/enums'

const toast = useToast()

// State Loading Fullscreen 1 Halaman (GET Data)
const isLoadingGet = ref(true)

// State Filter
const searchQuery = ref('')
const selectedStatus = ref<string>('all')
const selectedCourier = ref<string>('all')
const dateRange = ref('Okt 1 - Okt 31, 2024')

// State Multi-Select Checkbox
const selectedOrders = ref<Order[]>([])

// Dropdown Filter Options
const statusFilterOptions = [
  { label: 'Semua Status', value: 'all' },
  { label: 'Menunggu Pembayaran', value: 'pending' },
  { label: 'Menunggu Pengiriman', value: 'processing' },
  { label: 'Dikirim', value: 'shipped' },
  { label: 'Selesai', value: 'completed' },
  { label: 'Dibatalkan', value: 'cancelled' }
]

const courierFilterOptions = [
  { label: 'Semua Kurir', value: 'all' },
  { label: 'JNE Reguler', value: 'jne' },
  { label: 'J&T Express', value: 'jnt' },
  { label: 'SiCepat', value: 'sicepat' },
  { label: 'COD (Ketemuan)', value: 'cod' }
]

// Mock Summary Stats Overview
const orderStats = ref({
  pendingPayment: 12,
  pendingShipment: 28,
  shipped: 45,
  completedThisMonth: 156,
  completedGrowth: '+12%'
})

// Mock Data List Orders
const orders = ref<Order[]>([])

// ----------------------------------------------------------------
// 1. SIMULASI GET DATA ORDERS (Full Screen Circular Loader)
// ----------------------------------------------------------------
const fetchOrdersData = () => {
  isLoadingGet.value = true
  setTimeout(() => {
    orders.value = [
      {
        id: 1,
        order_number: 'INV/20241024/001',
        buyer_id: 101,
        shop_id: 1,
        subtotal: '150000',
        shipping_cost: '15000',
        total_amount: '150000',
        shipping_method: 'JNE Reguler',
        payment_method: 'Transfer Bank',
        status: 'processing',
        shipping_address: 'Bandung, Jawa Barat',
        tracking_number: null,
        notes: null,
        created_at: '2024-10-24 14:30:00',
        updated_at: '2024-10-24 14:30:00',
        buyer: {
          id: 101,
          name: 'Budi Santoso',
          email: 'budi@gmail.com',
          role: 'buyer',
          status: 'active',
          phone: '08123456789',
          address: 'Bandung, Jawa Barat',
          email_verified_at: null,
          proof_image: null,
          verified_by: null,
          verified_at: null,
          created_at: '',
          updated_at: ''
        },
        items: [
          {
            id: 1,
            order_id: 1,
            product_id: 10,
            quantity: 2,
            price_snapshot: '75000',
            cost_snapshot: '50000',
            created_at: '',
            updated_at: '',
            product: {
              id: 10,
              shop_id: 1,
              category_id: 1,
              name: 'Kopi Susu Gula Aren Literan',
              slug: 'kopi-susu-gula-aren',
              description: '',
              price: '75000',
              cost_price: '50000',
              stock: 20,
              weight: 1000,
              status: 'approved',
              verified_at: null,
              rejection_reason: null,
              created_at: ''
            }
          }
        ]
      },
      {
        id: 2,
        order_number: 'INV/20241024/045',
        buyer_id: 102,
        shop_id: 1,
        subtotal: '85500',
        shipping_cost: '10000',
        total_amount: '85500',
        shipping_method: 'J&T Express',
        payment_method: 'Transfer Bank',
        status: 'pending',
        shipping_address: 'Surabaya, Jawa Timur',
        tracking_number: null,
        notes: null,
        created_at: '2024-10-24 09:15:00',
        updated_at: '2024-10-24 09:15:00',
        buyer: {
          id: 102,
          name: 'Siti Aminah',
          email: 'siti@gmail.com',
          role: 'buyer',
          status: 'active',
          phone: '08987654321',
          address: 'Surabaya, Jawa Timur',
          email_verified_at: null,
          proof_image: null,
          verified_by: null,
          verified_at: null,
          created_at: '',
          updated_at: ''
        },
        items: [
          {
            id: 2,
            order_id: 2,
            product_id: 11,
            quantity: 5,
            price_snapshot: '17100',
            cost_snapshot: '12000',
            created_at: '',
            updated_at: '',
            product: {
              id: 11,
              shop_id: 1,
              category_id: 1,
              name: 'Keripik Tempe Renyah Rasa Pedas',
              slug: 'keripik-tempe-renyah',
              description: '',
              price: '17100',
              cost_price: '12000',
              stock: 50,
              weight: 200,
              status: 'approved',
              verified_at: null,
              rejection_reason: null,
              created_at: ''
            }
          }
        ]
      }
    ]
    isLoadingGet.value = false
  }, 1000)
}

// Filtered Orders Computation
const filteredOrders = computed(() => {
  return orders.value.filter((order) => {
    const matchSearch =
      order.order_number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      order.buyer?.name.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchStatus = selectedStatus.value === 'all' || order.status === selectedStatus.value

    const matchCourier =
      selectedCourier.value === 'all' ||
      order.shipping_method.toLowerCase().includes(selectedCourier.value.toLowerCase())

    return matchSearch && matchStatus && matchCourier
  })
})

// Formatting Helpers
const formatRupiah = (val?: string | number) => {
  const num = Number(val || 0)
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num)
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
    date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false })
}

const getStatusLabel = (status: OrderStatus) => {
  switch (status) {
    case 'pending':
      return 'Menunggu Pembayaran'
    case 'processing':
      return 'Menunggu Pengiriman'
    case 'shipped':
      return 'Dikirim'
    case 'completed':
      return 'Selesai'
    case 'cancelled':
      return 'Dibatalkan'
    default:
      return status
  }
}

const getStatusClass = (status: OrderStatus) => {
  switch (status) {
    case 'pending':
      return 'bg-amber-50 text-amber-600 border border-amber-200'
    case 'processing':
      return 'bg-blue-50 text-blue-600 border border-blue-200'
    case 'shipped':
      return 'bg-purple-50 text-purple-600 border border-purple-200'
    case 'completed':
      return 'bg-emerald-50 text-emerald-600 border border-emerald-200'
    case 'cancelled':
      return 'bg-red-50 text-red-600 border border-red-200'
    default:
      return 'bg-slate-100 text-slate-600'
  }
}

// ----------------------------------------------------------------
// 2. HANDLER ACTIONS & EXPORT
// ----------------------------------------------------------------
const handleExportCSV = () => {
  toast.add({
    severity: 'success',
    summary: 'Export CSV',
    detail: 'Daftar pesanan berhasil diunduh dalam format CSV.',
    life: 3000
  })
}

const handleBulkPrintLabel = () => {
  if (selectedOrders.value.length === 0) return
  toast.add({
    severity: 'info',
    summary: 'Cetak Label Masal',
    detail: `Mencetak label pengiriman untuk ${selectedOrders.value.length} pesanan.`,
    life: 3000
  })
}

const handleBulkUpdateStatus = () => {
  if (selectedOrders.value.length === 0) return
  toast.add({
    severity: 'info',
    summary: 'Ubah Status Masal',
    detail: `Memproses pembaruan status untuk ${selectedOrders.value.length} pesanan.`,
    life: 3000
  })
}

onMounted(() => {
  fetchOrdersData()
})
</script>

<template>
  <div class="relative min-h-[80vh]">
    <Transition name="fade">
      <div v-if="isLoadingGet"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
        <ProgressSpinner style="width: 56px; height: 56px" strokeWidth="4" fill="transparent" animationDuration=".8s"
          aria-label="Loading Orders" />
        <p class="mt-4 font-medium text-slate-600 text-sm animate-pulse">
          Memuat Daftar Pesanan Toko...
        </p>
      </div>
    </Transition>

    <div v-if="!isLoadingGet" class="max-w-7xl mx-auto space-y-6 pb-12">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu Pembayaran</span>
            <h3 class="text-2xl font-extrabold text-slate-800 mt-1">{{ orderStats.pendingPayment }}</h3>
          </div>
          <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
            <i class="pi pi-clock text-base"></i>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu Pengiriman</span>
            <h3 class="text-2xl font-extrabold text-slate-800 mt-1">{{ orderStats.pendingShipment }}</h3>
          </div>
          <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
            <i class="pi pi-box text-base"></i>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dikirim</span>
            <h3 class="text-2xl font-extrabold text-slate-800 mt-1">{{ orderStats.shipped }}</h3>
          </div>
          <div class="w-9 h-9 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center">
            <i class="pi pi-send text-base"></i>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Selesai Bulan Ini</span>
            <div class="flex items-baseline gap-2 mt-1">
              <h3 class="text-2xl font-extrabold text-slate-800">{{ orderStats.completedThisMonth }}</h3>
              <span class="text-[11px] font-bold text-emerald-600">↑ {{ orderStats.completedGrowth }}</span>
            </div>
          </div>
          <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
            <i class="pi pi-check-circle text-base"></i>
          </div>
        </div>

      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">

          <div class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative min-w-60 flex-1 sm:flex-initial">
              <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
              <InputText v-model="searchQuery" placeholder="Cari No. Pesanan / Pembeli..."
                class="w-full! pl-9! pr-4! py-2! bg-slate-50/80! border-slate-200! rounded-xl! text-xs!" />
            </div>

            <Dropdown v-model="selectedStatus" :options="statusFilterOptions" optionLabel="label" optionValue="value"
              placeholder="Semua Status" class="bg-slate-50/80! border-slate-200! rounded-xl! text-xs! min-w-37.5!" />

            <Dropdown v-model="selectedCourier" :options="courierFilterOptions" optionLabel="label" optionValue="value"
              placeholder="Semua Kurir" class="bg-slate-50/80! border-slate-200! rounded-xl! text-xs! min-w-35!" />

            <div class="relative flex items-center">
              <i class="pi pi-calendar absolute left-3 text-slate-400 text-xs z-10"></i>
              <InputText v-model="dateRange" readonly
                class="pl-9! pr-4! py-2! bg-slate-50/80! border-slate-200! rounded-xl! text-xs! w-44! cursor-pointer! text-slate-600" />
            </div>
          </div>

          <div class="shrink-0">
            <Button label="Export CSV" icon="pi pi-download" outlined
              class="border-blue-600! text-blue-600! hover:bg-blue-50! rounded-xl! text-xs! font-semibold! px-4! py-2!"
              @click="handleExportCSV" />
          </div>

        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <DataTable v-model:selection="selectedOrders" :value="filteredOrders" dataKey="id" class="p-datatable-sm">
          <template #empty>
            <div class="p-12 text-center text-slate-400 text-xs">
              Tidak ada pesanan yang sesuai dengan filter pencarian.
            </div>
          </template>

          <Column selectionMode="multiple" headerStyle="width: 3rem" class="text-center"></Column>

          <Column header="NO. PESANAN / TGL">
            <template #body="{ data }">
              <div class="py-1">
                <span class="block text-xs font-bold text-blue-600 cursor-pointer hover:underline">
                  {{ data.order_number }}
                </span>
                <span class="block text-[11px] text-slate-400 mt-0.5">
                  {{ formatDate(data.created_at) }}
                </span>
              </div>
            </template>
          </Column>

          <Column header="PEMBELI / TUJUAN">
            <template #body="{ data }">
              <div class="py-1">
                <span class="block text-xs font-bold text-slate-800">
                  {{ data.buyer?.name || 'Pembeli' }}
                </span>
                <span class="flex items-center gap-1 text-[11px] text-slate-400 mt-0.5">
                  <i class="pi pi-map-marker text-[10px]"></i>
                  {{ data.shipping_address }}
                </span>
              </div>
            </template>
          </Column>

          <Column header="PRODUK">
            <template #body="{ data }">
              <div class="flex items-center gap-2.5 py-1">
                <div
                  class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200/60 flex items-center justify-center shrink-0">
                  <i class="pi pi-image text-slate-400 text-sm"></i>
                </div>
                <div class="overflow-hidden">
                  <span class="block text-xs font-semibold text-slate-800 truncate max-w-45">
                    {{ data.items?.[0]?.product?.name || 'Nama Produk' }}
                  </span>
                  <span class="block text-[11px] text-slate-400">
                    {{ data.items?.[0]?.quantity || 1 }} Barang
                  </span>
                </div>
              </div>
            </template>
          </Column>

          <Column header="TOTAL (RP)">
            <template #body="{ data }">
              <span class="text-xs font-extrabold text-slate-800">
                {{ formatRupiah(data.total_amount) }}
              </span>
            </template>
          </Column>

          <Column header="KURIR">
            <template #body="{ data }">
              <span
                class="inline-block px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200/50">
                {{ data.shipping_method }}
              </span>
            </template>
          </Column>

          <Column header="STATUS">
            <template #body="{ data }">
              <span :class="[
                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold',
                getStatusClass(data.status)
              ]">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ getStatusLabel(data.status) }}
              </span>
            </template>
          </Column>

          <Column header="AKSI" class="text-center">
            <template #body="{ data }">
              <Button icon="pi pi-ellipsis-v" severity="secondary" text rounded
                class="w-8! h-8! text-slate-400! hover:text-slate-700!" />
            </template>
          </Column>
        </DataTable>

        <div
          class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
          <div class="flex items-center gap-3">
            <span class="text-slate-600 font-medium">
              {{ selectedOrders.length }} dipilih
            </span>
            <Button label="Cetak Label Masal" severity="secondary"
              class="bg-slate-100! hover:bg-slate-200! text-slate-700! border-none! rounded-lg! text-xs! py-1.5! px-3!"
              :disabled="selectedOrders.length === 0" @click="handleBulkPrintLabel" />
            <Button label="Ubah Status" severity="secondary"
              class="bg-slate-100! hover:bg-slate-200! text-slate-700! border-none! rounded-lg! text-xs! py-1.5! px-3!"
              :disabled="selectedOrders.length === 0" @click="handleBulkUpdateStatus" />
          </div>

          <div class="flex items-center gap-4">
            <span>Menampilkan 1-{{ filteredOrders.length }} dari {{ orders.length }}</span>
            <Paginator :rows="10" :totalRecords="filteredOrders.length" />
          </div>
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