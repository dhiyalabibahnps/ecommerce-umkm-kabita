<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'

import OrderStatusBadge from '@/components/ui/OrderStatusBadge.vue'
import { getApiErrorMessage } from '@/services/apiError'
import { buyerOrderService } from '@/services/buyerOrderService'
import { useChatStore } from '@/stores/chat'
import type { Order, OrderStatus } from '@/types'

const router = useRouter()
const chatStore = useChatStore()
const orders = ref<Order[]>([])
const searchQuery = ref('')
const selectedStatus = ref<string>('')
const isLoading = ref(true)
const errorMessage = ref('')

const statusFilters = [
  { label: 'Semua', value: '' },
  { label: 'Menunggu Verifikasi', value: 'awaiting_verification' },
  { label: 'Dikonfirmasi', value: 'processing' },
  { label: 'Dikemas', value: 'packed' },
  { label: 'Dikirim', value: 'shipped' },
  { label: 'Selesai', value: 'completed' },
  { label: 'Dibatalkan', value: 'cancelled' },
]

const filteredOrders = computed(() => {
  return orders.value.filter((order) => {
    const query = searchQuery.value.trim().toLowerCase()
    const matchesSearch =
      !query ||
      [
        order.order_number,
        order.shop?.name,
        order.courier,
        order.items?.[0]?.product?.name,
      ].some((value) => String(value ?? '').toLowerCase().includes(query))

    const matchesStatus =
      !selectedStatus.value || order.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})

const loadOrders = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await buyerOrderService.list({ sort: 'newest', per_page: 50 })
    orders.value = response.data
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Pesanan belum dapat dimuat.')
  } finally {
    isLoading.value = false
  }
}

const openChat = (order: Order) => {
  chatStore.openOrderChat(order.id, order.order_number, order.shop?.name, 'buyer')
}

const formatCurrency = (amount: string | number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(amount) || 0)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })

onMounted(loadOrders)
</script>

<template>
  <section class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs sm:p-6 lg:p-7">
    <!-- Header -->
    <header class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Pesanan Saya</h1>
        <p class="mt-0.5 text-xs text-slate-500">Pantau status transaksi, bukti pembayaran, dan riwayat belanja Anda.</p>
      </div>

      <div class="relative w-full sm:w-72">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
        <InputText
          v-model="searchQuery"
          placeholder="Cari order, produk, atau toko..."
          class="w-full rounded-xl! py-2! pl-8! text-xs!"
        />
      </div>
    </header>

    <!-- Status Tabs Filter -->
    <div class="mt-4 flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
      <button
        v-for="tab in statusFilters"
        :key="tab.value"
        type="button"
        class="shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition cursor-pointer"
        :class="[
          selectedStatus === tab.value
            ? 'bg-blue-600 text-white shadow-2xs'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'
        ]"
        @click="selectedStatus = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center gap-3 py-20 text-slate-400">
      <ProgressSpinner style="width: 38px; height: 38px" />
      <span class="text-xs">Memuat daftar pesanan...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="py-16 text-center">
      <i class="pi pi-exclamation-triangle text-2xl text-rose-500"></i>
      <p class="mt-2 text-xs font-semibold text-rose-600">{{ errorMessage }}</p>
      <Button label="Coba lagi" icon="pi pi-refresh" outlined size="small" class="mt-4 rounded-xl! text-xs!" @click="loadOrders" />
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredOrders.length === 0" class="py-16 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 text-slate-300">
        <i class="pi pi-receipt text-2xl"></i>
      </div>
      <h2 class="mt-4 font-bold text-slate-800 text-sm">Pesanan tidak ditemukan</h2>
      <p class="mt-1 text-xs text-slate-500">Belum ada pesanan yang sesuai dengan filter atau pencarian Anda.</p>
      <Button
        label="Mulai Belanja"
        icon="pi pi-shopping-bag"
        size="small"
        class="mt-5 rounded-xl! text-xs! bg-blue-600! border-blue-600!"
        @click="router.push('/produk')"
      />
    </div>

    <!-- Orders Card List -->
    <div v-else class="mt-5 space-y-3.5">
      <article
        v-for="order in filteredOrders"
        :key="order.id"
        class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs transition hover:border-blue-200 hover:shadow-xs"
      >
        <!-- Card Top Bar: Shop Name + Status Badge -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3 text-xs">
          <div class="flex items-center gap-2">
            <i class="pi pi-shop text-blue-600 text-sm"></i>
            <strong class="text-slate-900 font-bold text-xs">{{ order.shop?.name || 'Toko UMKM' }}</strong>
            <span class="text-slate-300">•</span>
            <span class="font-mono text-slate-500 text-[11px]">{{ order.order_number }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-slate-400 text-[11px]">{{ formatDate(order.created_at) }}</span>
          </div>

          <OrderStatusBadge :status="order.status" size="small" role="buyer" />
        </div>

        <!-- Product Summary Items -->
        <div class="divide-y divide-slate-50 py-1">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="flex items-center gap-3 py-2.5"
          >
            <div
              v-if="item.product?.images?.[0]?.url"
              class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-slate-100 bg-slate-50"
            >
              <img
                :src="item.product.images[0].url"
                :alt="item.product.name"
                class="h-full w-full object-cover"
              />
            </div>
            <div
              v-else
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-300"
            >
              <i class="pi pi-box text-base"></i>
            </div>

            <div class="min-w-0 flex-1">
              <h3 class="truncate text-xs font-bold text-slate-800">
                {{ item.product?.name || 'Produk' }}
              </h3>
              <p class="mt-0.5 text-[11px] text-slate-500">
                {{ item.quantity }} × {{ formatCurrency(item.price_snapshot) }}
              </p>
            </div>

            <span class="shrink-0 text-xs font-bold text-slate-800">
              {{ formatCurrency(Number(item.price_snapshot) * item.quantity) }}
            </span>
          </div>
        </div>

        <!-- Middle Info: Shipping & Buyer Note -->
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-slate-50/70 p-2.5 border border-slate-100 text-xs">
          <div class="flex items-center gap-2">
            <i class="pi pi-truck text-slate-500 text-xs"></i>
            <span class="text-slate-600 font-medium">
              {{ order.shipping_method === 'cod' ? 'COD (Ketemuan Langsung)' : (order.courier || 'Kurir Ekspedisi') }}
            </span>
            <span v-if="order.tracking_number" class="font-mono text-[11px] text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200/60">
              Resi: {{ order.tracking_number }}
            </span>
          </div>

          <p v-if="order.notes" class="text-[11px] text-slate-500 italic truncate max-w-xs">
            Catatan: "{{ order.notes }}"
          </p>
        </div>

        <!-- Card Footer: Total Amount & Action Buttons -->
        <footer class="mt-3 flex flex-col gap-2.5 border-t border-slate-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="text-xs">
            <span class="text-slate-500">Total Belanja: </span>
            <strong class="text-sm font-extrabold text-blue-600">
              {{ formatCurrency(order.total_amount) }}
            </strong>
            <span class="text-[10px] text-slate-400 ml-1.5 capitalize">({{ order.payment_method === 'transfer' ? 'Transfer' : 'COD' }})</span>
          </div>

          <div class="flex items-center gap-2 justify-end">
            <Button
              label="Hubungi Penjual"
              icon="pi pi-comments"
              severity="secondary"
              outlined
              size="small"
              class="text-xs! rounded-lg! text-blue-600! border-blue-200! hover:bg-blue-50!"
              @click="openChat(order)"
            />

            <Button
              label="Lihat Detail"
              icon="pi pi-arrow-right"
              iconPos="right"
              size="small"
              class="text-xs! rounded-lg! bg-blue-600! border-blue-600! font-bold!"
              @click="router.push(`/order-detail?id=${order.id}`)"
            />
          </div>
        </footer>
      </article>
    </div>
  </section>
</template>
