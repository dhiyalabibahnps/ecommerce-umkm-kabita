<script setup lang="ts">
import { getApiErrorMessage } from '@/services/apiError'
import { sellerOrderService } from '@/services/sellerOrderService'
import type { Order, OrderStatus } from '@/types'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const orders = ref<Order[]>([])
const search = ref('')
const status = ref('')
const loading = ref(true)
const error = ref('')
const updatingId = ref<number | null>(null)

const filteredOrders = computed(() => orders.value.filter((order) => {
  const query = search.value.trim().toLowerCase()
  const matchesSearch = !query || [order.order_number, order.buyer?.name, order.shipping_address]
    .some((value) => String(value ?? '').toLowerCase().includes(query))
  return matchesSearch && (!status.value || order.status === status.value)
}))

const loadOrders = async () => {
  loading.value = true
  error.value = ''
  try {
    orders.value = (await sellerOrderService.list({ sort: 'newest', per_page: 100 })).data
  } catch (exception) {
    error.value = getApiErrorMessage(exception, 'Pesanan seller gagal dimuat.')
  } finally {
    loading.value = false
  }
}

const updateOrder = async (order: Order, action: 'process' | 'pack' | 'ship') => {
  updatingId.value = order.id
  try {
    const updated = action === 'process'
      ? await sellerOrderService.process(order.id)
      : action === 'pack'
        ? await sellerOrderService.pack(order.id)
        : await sellerOrderService.ship(order.id, { tracking_number: order.tracking_number || '' })
    orders.value = orders.value.map((item) => item.id === updated.id ? updated : item)
  } catch (exception) {
    error.value = getApiErrorMessage(exception, 'Status pesanan gagal diperbarui.')
  } finally {
    updatingId.value = null
  }
}

const formatCurrency = (value: string | number) => new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
}).format(Number(value) || 0)
const formatDate = (value: string) => new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
const statusLabel = (value: OrderStatus) => ({ awaiting_verification: 'Menunggu Verifikasi', processing: 'Dikonfirmasi', packed: 'Dikemas', shipped: 'Dikirim', cod_meeting: 'Ketemuan', completed: 'Selesai', cancelled: 'Dibatalkan' }[value] || value)
const statusSeverity = (value: OrderStatus) => ({ awaiting_verification: 'warn', processing: 'info', packed: 'info', shipped: 'info', cod_meeting: 'warn', completed: 'success', cancelled: 'danger' }[value] || 'secondary')

onMounted(loadOrders)
</script>

<template>
  <section class="mx-auto max-w-2xl lg:max-w-5xl xl:max-w-7xl space-y-5 pb-12">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-semibold text-blue-600">Operasional toko</p>
        <h1 class="text-2xl font-bold text-slate-900">Pesanan Masuk</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola pesanan pelanggan sampai siap dikirim.</p>
      </div>
      <Button label="Refresh" icon="pi pi-refresh" outlined class="rounded-xl!" :loading="loading"
        @click="loadOrders" />
    </header>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
      <div
        v-for="item in [{ label: 'Semua', value: '' }, { label: 'Menunggu Verifikasi', value: 'awaiting_verification' }, { label: 'Dikonfirmasi', value: 'processing' }, { label: 'Dikemas', value: 'packed' }]"
        :key="item.label" class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <p class="text-xs text-slate-500">{{ item.label }}</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">{{orders.filter((order) => !item.value || order.status ===
          item.value).length}}</p>
      </div>
    </div>
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:flex-row">
      <InputText v-model="search" placeholder="Cari nomor pesanan atau pembeli..."
        class="w-full rounded-xl! sm:flex-1" />
      <select v-model="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm sm:w-52">
        <option value="">Semua status</option>
        <option value="awaiting_verification">Menunggu Verifikasi</option>
        <option value="processing">Dikonfirmasi</option>
        <option value="packed">Dikemas</option>
        <option value="shipped">Dikirim</option>
        <option value="cod_meeting">Ketemuan</option>
        <option value="completed">Selesai</option>
      </select>
    </div>
    <div v-if="loading" class="flex justify-center rounded-2xl bg-white py-20">
      <ProgressSpinner />
    </div>
    <div v-else-if="error" class="rounded-2xl border border-rose-100 bg-white p-10 text-center">
      <p class="text-sm text-rose-600">{{ error }}</p><Button label="Coba lagi" outlined class="mt-4 rounded-xl!"
        @click="loadOrders" />
    </div>
    <div v-else-if="filteredOrders.length === 0" class="rounded-2xl border border-slate-100 bg-white p-16 text-center">
      <i class="pi pi-inbox text-4xl text-slate-300"></i>
      <p class="mt-4 font-semibold text-slate-700">Belum ada pesanan yang sesuai</p>
    </div>
    <div v-else class="space-y-3">
      <article v-for="order in filteredOrders" :key="order.id"
        class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="font-bold text-slate-900">{{ order.order_number }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ order.buyer?.name || 'Pembeli' }} • {{
              formatDate(order.created_at) }}</p>
          </div>
          <Tag :value="statusLabel(order.status)" :severity="statusSeverity(order.status)" />
        </div>
        <div
          class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-800">
              {{ order.items?.[0]?.product?.name || 'Detail Produk' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ order.items?.length || 0 }} jenis produk • {{
              order.shipping_address }}</p>
          </div>
          <p class="shrink-0 font-bold text-slate-900">{{ formatCurrency(order.total_amount) }}</p>
        </div>
        <div class="mt-4 flex flex-wrap justify-end gap-2"><Button label="Detail" text class="rounded-xl!"
            @click="router.push(`/seller/pesanan/${order.id}`)" /><Button
            v-if="order.status === 'awaiting_verification'" label="Proses pesanan" icon="pi pi-check"
            class="rounded-xl!" :loading="updatingId === order.id" @click="updateOrder(order, 'process')" /><Button
            v-if="order.status === 'processing'" label="Kemas pesanan" icon="pi pi-box" class="rounded-xl!"
            :loading="updatingId === order.id" @click="updateOrder(order, 'pack')" /><Button
            v-if="order.status === 'packed'" label="Konfirmasi pengiriman" icon="pi pi-send" class="rounded-xl!"
            :loading="updatingId === order.id" @click="updateOrder(order, 'ship')" /></div>
      </article>
    </div>
  </section>
</template>
