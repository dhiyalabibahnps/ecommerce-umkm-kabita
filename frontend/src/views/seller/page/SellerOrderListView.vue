<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import Select from 'primevue/select'
import { useToast } from 'primevue/usetoast'

import OrderStatusBadge from '@/components/ui/OrderStatusBadge.vue'
import { COURIER_MASTER_LIST, FLAT_SHIPPING_OPTIONS, formatCourierDisplay, getCourierSelectOptions, resolveCourierOptionValue } from '@/constants/courier'
import { getApiErrorMessage } from '@/services/apiError'
import { sellerOrderService } from '@/services/sellerOrderService'
import { useChatStore } from '@/stores/chat'
import type { Order, OrderStatus } from '@/types'

const router = useRouter()
const toast = useToast()
const chatStore = useChatStore()

const orders = ref<Order[]>([])
const search = ref('')
const selectedStatus = ref<string>('')
const loading = ref(true)
const error = ref('')
const actionLoadingId = ref<number | null>(null)

// Modal konfirmasi pengiriman
const shipModalVisible = ref(false)
const selectedOrderToShip = ref<Order | null>(null)
const selectedCourier = ref<string>('JNE REG')
const trackingNumberInput = ref<string>('')
const isShipSubmitting = ref(false)

// Chat dialog
const chatVisible = ref(false)
const activeChatOrder = ref<Order | null>(null)

const summaryTabs = [
  { label: 'Semua', value: '', icon: 'pi pi-list' },
  { label: 'Menunggu Verifikasi', value: 'awaiting_verification', icon: 'pi pi-clock' },
  { label: 'Dikonfirmasi', value: 'processing', icon: 'pi pi-check-circle' },
  { label: 'Dikemas', value: 'packed', icon: 'pi pi-box' },
  { label: 'Dikirim', value: 'shipped', icon: 'pi pi-truck' },
  { label: 'Selesai', value: 'completed', icon: 'pi pi-verified' },
]

const statusCounts = computed(() => {
  const map: Record<string, number> = {
    '': orders.value.length,
    awaiting_verification: 0,
    processing: 0,
    packed: 0,
    shipped: 0,
    completed: 0,
  }
  for (const ord of orders.value) {
    if (ord.status && map[ord.status] !== undefined) {
      map[ord.status] = (map[ord.status] ?? 0) + 1
    }
  }
  return map
})

const filteredOrders = computed(() => {
  return orders.value.filter((order) => {
    const query = search.value.trim().toLowerCase()
    const matchesSearch =
      !query ||
      [
        order.order_number,
        order.buyer?.name,
        order.courier,
        order.items?.[0]?.product?.name,
      ].some((val) => String(val ?? '').toLowerCase().includes(query))

    const matchesStatus =
      !selectedStatus.value || order.status === selectedStatus.value

    return matchesSearch && matchesStatus
  })
})

const loadOrders = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await sellerOrderService.list({ sort: 'newest', per_page: 100 })
    orders.value = response.data
  } catch (exception) {
    error.value = getApiErrorMessage(exception, 'Pesanan seller gagal dimuat.')
  } finally {
    loading.value = false
  }
}

const handleProcessOrder = async (order: Order) => {
  actionLoadingId.value = order.id
  try {
    const updated = await sellerOrderService.process(order.id)
    orders.value = orders.value.map((item) => (item.id === updated.id ? updated : item))
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan dikonfirmasi dan siap dikemas.', life: 3000 })
  } catch (exception) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(exception, 'Gagal memproses pesanan.'), life: 4000 })
  } finally {
    actionLoadingId.value = null
  }
}

const handlePackOrder = async (order: Order) => {
  actionLoadingId.value = order.id
  try {
    const updated = await sellerOrderService.pack(order.id)
    orders.value = orders.value.map((item) => (item.id === updated.id ? updated : item))
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan telah masuk status dikemas.', life: 3000 })
  } catch (exception) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(exception, 'Gagal mengemas pesanan.'), life: 4000 })
  } finally {
    actionLoadingId.value = null
  }
}

const openShipModal = (order: Order) => {
  selectedOrderToShip.value = order
  selectedCourier.value = resolveCourierOptionValue(order.courier)
  trackingNumberInput.value = order.tracking_number || ''
  shipModalVisible.value = true
}

const handleConfirmShip = async () => {
  if (!selectedOrderToShip.value) return

  const isCourierShipping = selectedOrderToShip.value.shipping_method !== 'cod'
  if (isCourierShipping && !trackingNumberInput.value.trim()) {
    toast.add({ severity: 'warn', summary: 'Wajib Diisi', detail: 'Nomor resi pengiriman wajib diisi.', life: 3000 })
    return
  }

  isShipSubmitting.value = true
  try {
    const updated = await sellerOrderService.ship(selectedOrderToShip.value.id, {
      courier: selectedCourier.value.trim() || undefined,
      tracking_number: trackingNumberInput.value.trim() || undefined,
    })
    orders.value = orders.value.map((item) => (item.id === updated.id ? updated : item))
    shipModalVisible.value = false
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan berhasil dikirim dengan nomor resi.', life: 3000 })
  } catch (exception) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(exception, 'Gagal mengirim pesanan.'), life: 4000 })
  } finally {
    isShipSubmitting.value = false
  }
}

const handleCodComplete = async (order: Order) => {
  actionLoadingId.value = order.id
  try {
    const updated = await sellerOrderService.codComplete(order.id)
    orders.value = orders.value.map((item) => (item.id === updated.id ? updated : item))
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pesanan COD berhasil diselesaikan.', life: 3000 })
  } catch (exception) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(exception, 'Gagal menyelesaikan pesanan COD.'), life: 4000 })
  } finally {
    actionLoadingId.value = null
  }
}

const openChat = (order: Order) => {
  chatStore.openOrderChat(order.id, order.order_number, order.buyer?.name, 'seller')
}

const formatCurrency = (value: string | number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value) || 0)

const formatDate = (value: string) =>
  new Date(value).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })

const courierOptionsForSelect = computed(() => {
  return getCourierSelectOptions(selectedOrderToShip.value?.courier)
})

onMounted(loadOrders)
</script>

<template>
  <section class="mx-auto max-w-6xl space-y-5 pb-12">
    <!-- Header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex h-6 items-center rounded-md bg-blue-50 px-2 text-[11px] font-semibold text-blue-700">
            Operasional Penjual
          </span>
        </div>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Kelola Pesanan</h1>
        <p class="text-xs text-slate-500">Pantau verifikasi, proses pengemasan, dan pengiriman pesanan toko Anda.</p>
      </div>
      <Button
        label="Segarkan"
        icon="pi pi-refresh"
        size="small"
        outlined
        class="rounded-xl!"
        :loading="loading"
        @click="loadOrders"
      />
    </header>

    <!-- Top Status Summary Filter Cards -->
    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
      <button
        v-for="tab in summaryTabs"
        :key="tab.value"
        type="button"
        class="group relative flex flex-col justify-between rounded-xl border p-3.5 text-left transition cursor-pointer"
        :class="[
          selectedStatus === tab.value
            ? 'border-blue-500 bg-blue-50/50 shadow-xs ring-2 ring-blue-500/20'
            : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'
        ]"
        @click="selectedStatus = tab.value"
      >
        <div class="flex items-center justify-between">
          <span
            class="text-[11px] font-medium leading-tight transition"
            :class="selectedStatus === tab.value ? 'text-blue-700 font-bold' : 'text-slate-500'"
          >
            {{ tab.label }}
          </span>
          <i
            :class="[
              tab.icon,
              'text-xs transition',
              selectedStatus === tab.value ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'
            ]"
          ></i>
        </div>
        <p
          class="mt-2 text-xl font-black transition tracking-tight"
          :class="selectedStatus === tab.value ? 'text-blue-900' : 'text-slate-800'"
        >
          {{ statusCounts[tab.value] ?? 0 }}
        </p>
      </button>
    </div>

    <!-- Search and Status Select Bar -->
    <div class="flex flex-col gap-2.5 rounded-xl border border-slate-200/80 bg-white p-3 shadow-2xs sm:flex-row sm:items-center">
      <div class="relative flex-1">
        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
        <InputText
          v-model="search"
          placeholder="Cari nomor order, nama pembeli, atau produk..."
          class="w-full rounded-lg! py-2! pl-8! text-xs!"
        />
      </div>
      <div class="flex items-center gap-2">
        <select
          v-model="selectedStatus"
          class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-blue-500 focus:outline-none"
        >
          <option value="">Semua Status</option>
          <option value="awaiting_verification">Menunggu Verifikasi</option>
          <option value="processing">Dikonfirmasi</option>
          <option value="packed">Dikemas</option>
          <option value="shipped">Dikirim</option>
          <option value="cod_meeting">Ketemuan (COD)</option>
          <option value="completed">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
        <Button
          v-if="search || selectedStatus"
          label="Reset"
          icon="pi pi-times"
          text
          size="small"
          class="text-xs! text-slate-500 hover:text-slate-700"
          @click="search = ''; selectedStatus = ''"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center rounded-2xl bg-white py-20 text-slate-400 shadow-2xs">
      <ProgressSpinner style="width: 36px; height: 36px" />
      <p class="mt-3 text-xs">Memuat daftar pesanan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="rounded-2xl border border-rose-100 bg-white p-8 text-center shadow-2xs">
      <i class="pi pi-exclamation-triangle text-2xl text-rose-500"></i>
      <p class="mt-2 text-xs font-semibold text-rose-600">{{ error }}</p>
      <Button label="Coba Lagi" outlined size="small" class="mt-3 rounded-lg!" @click="loadOrders" />
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredOrders.length === 0" class="rounded-2xl border border-slate-100 bg-white p-16 text-center shadow-2xs">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 text-slate-300">
        <i class="pi pi-inbox text-2xl"></i>
      </div>
      <h3 class="mt-4 text-sm font-bold text-slate-800">Tidak ada pesanan ditemukan</h3>
      <p class="mt-1 text-xs text-slate-400">Belum ada transaksi yang sesuai dengan filter pencarian Anda.</p>
    </div>

    <!-- Order List Cards -->
    <div v-else class="space-y-3">
      <article
        v-for="order in filteredOrders"
        :key="order.id"
        class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs transition hover:border-blue-200 hover:shadow-xs"
      >
        <!-- Card Top: Order Number & Status Badge -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2.5">
            <span class="font-mono text-xs font-bold text-slate-900">{{ order.order_number }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-[11px] text-slate-500">{{ formatDate(order.created_at) }}</span>
          </div>
          <OrderStatusBadge :status="order.status" size="small" role="seller" />
        </div>

        <!-- Card Middle: Buyer, Products, Courier, Total -->
        <div class="grid grid-cols-1 gap-4 py-3 sm:grid-cols-12 sm:items-center">
          <!-- Buyer info & Product Thumbnail -->
          <div class="sm:col-span-6 flex items-start gap-3">
            <div
              v-if="order.items?.[0]?.product?.images?.[0]?.url"
              class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-slate-100 bg-slate-50"
            >
              <img
                :src="order.items[0].product.images[0].url"
                :alt="order.items[0].product.name"
                class="h-full w-full object-cover"
              />
            </div>
            <div
              v-else
              class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-300"
            >
              <i class="pi pi-box text-lg"></i>
            </div>

            <div class="min-w-0 flex-1">
              <p class="text-xs font-bold text-slate-800 line-clamp-1">
                {{ order.items?.[0]?.product?.name || 'Produk Pesanan' }}
              </p>
              <p class="text-[11px] text-slate-500 mt-0.5">
                {{ order.items?.length || 1 }} produk • Pembeli: <strong class="text-slate-700">{{ order.buyer?.name || 'Pembeli' }}</strong>
              </p>
              <p v-if="order.notes" class="text-[10px] text-amber-700 bg-amber-50 rounded px-1.5 py-0.5 mt-1 inline-block border border-amber-200/60 line-clamp-1">
                Catatan: "{{ order.notes }}"
              </p>
            </div>
          </div>

          <!-- Shipping / Courier info -->
          <div class="sm:col-span-3 text-xs">
            <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Pengiriman</span>
            <p class="mt-0.5 font-bold text-slate-800">
              {{ order.shipping_method === 'cod' ? 'COD (Ketemuan)' : formatCourierDisplay(order.courier) }}
            </p>
            <p v-if="order.tracking_number" class="text-[11px] font-mono text-blue-600 mt-0.5">
              Resi: {{ order.tracking_number }}
            </p>
          </div>

          <!-- Total Amount -->
          <div class="sm:col-span-3 text-right">
            <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Total Pesanan</span>
            <p class="mt-0.5 text-sm font-extrabold text-blue-600">{{ formatCurrency(order.total_amount) }}</p>
            <span class="text-[10px] text-slate-400 capitalize">{{ order.payment_method === 'transfer' ? 'Transfer Bank' : 'COD' }}</span>
          </div>
        </div>

        <!-- Specific Notice for awaiting_verification -->
        <div
          v-if="order.status === 'awaiting_verification'"
          class="mt-1 mb-2 flex items-center gap-2 rounded-lg bg-amber-50/80 border border-amber-200/70 px-3 py-2 text-[11px] text-amber-800"
        >
          <i class="pi pi-info-circle text-amber-600 shrink-0"></i>
          <span>Pembayaran sedang diverifikasi admin. Pesanan dapat diproses setelah pembayaran disetujui.</span>
        </div>

        <!-- Card Bottom Actions -->
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
          <div class="flex items-center gap-2">
            <Button
              label="Chat Pembeli"
              icon="pi pi-comments"
              severity="secondary"
              text
              size="small"
              class="text-xs! text-blue-600! hover:bg-blue-50!"
              @click="openChat(order)"
            />
          </div>

          <div class="flex items-center gap-2">
            <Button
              label="Detail"
              icon="pi pi-arrow-right"
              iconPos="right"
              severity="secondary"
              outlined
              size="small"
              class="text-xs! rounded-lg!"
              @click="router.push(`/seller/pesanan/${order.id}`)"
            />

            <!-- Action based on status -->
            <template v-if="order.status === 'processing'">
              <Button
                label="Proses Pesanan"
                icon="pi pi-box"
                size="small"
                class="bg-blue-600! border-blue-600! text-xs! rounded-lg! font-bold!"
                :loading="actionLoadingId === order.id"
                @click="handlePackOrder(order)"
              />
            </template>

            <template v-else-if="order.status === 'packed'">
              <Button
                label="Konfirmasi Pengiriman"
                icon="pi pi-send"
                size="small"
                class="bg-blue-600! border-blue-600! text-xs! rounded-lg! font-bold!"
                :loading="actionLoadingId === order.id"
                @click="openShipModal(order)"
              />
            </template>

            <template v-else-if="order.status === 'shipped'">
              <Button
                label="Lihat Pengiriman"
                icon="pi pi-truck"
                severity="info"
                outlined
                size="small"
                class="text-xs! rounded-lg!"
                @click="router.push(`/seller/pesanan/${order.id}`)"
              />
            </template>

            <template v-else-if="order.status === 'cod_meeting'">
              <Button
                label="Selesaikan COD"
                icon="pi pi-check"
                severity="success"
                size="small"
                class="text-xs! rounded-lg!"
                :loading="actionLoadingId === order.id"
                @click="handleCodComplete(order)"
              />
            </template>
          </div>
        </div>
      </article>
    </div>

    <!-- Modal Konfirmasi Pengiriman & Input Resi -->
    <Dialog
      v-model:visible="shipModalVisible"
      modal
      header="Konfirmasi Pengiriman Pesanan"
      :style="{ width: 'min(460px, 92vw)' }"
      class="rounded-2xl!"
    >
      <div class="space-y-4 pt-1">
        <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100 text-xs space-y-1.5">
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Nomor Order:</span>
            <strong class="text-slate-800 font-mono">{{ selectedOrderToShip?.order_number }}</strong>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Pilihan Kurir Pembeli:</span>
            <span class="font-bold text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded text-[11px]">
              {{ formatCourierDisplay(selectedOrderToShip?.courier) }}
            </span>
          </div>
          <div class="flex items-start justify-between gap-2 pt-0.5 border-t border-slate-200/50">
            <span class="text-slate-500 shrink-0">Alamat Tujuan:</span>
            <span class="text-slate-700 text-right line-clamp-2">{{ selectedOrderToShip?.shipping_address }}</span>
          </div>
        </div>

        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold text-slate-700">Layanan Kurir <span class="text-rose-500">*</span></label>
            <span class="text-[10px] text-slate-400 font-medium">Otomatis terpilih 1-1 sesuai buyer</span>
          </div>
          <select
            v-model="selectedCourier"
            class="w-full h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-800 focus:border-blue-500 focus:outline-none"
          >
            <option v-for="item in courierOptionsForSelect" :key="item.value" :value="item.value">
              {{ item.label }}
            </option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-700">Nomor Resi Pengiriman <span class="text-rose-500">*</span></label>
          <InputText
            v-model="trackingNumberInput"
            placeholder="Contoh: JNE123456789 / SPXID0987654321"
            class="w-full text-xs! py-2.5! rounded-lg!"
          />
          <p class="text-[11px] text-slate-400">Nomor resi wajib diisi agar pembeli dapat melacak keberadaan paket.</p>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
          <Button
            label="Batal"
            severity="secondary"
            outlined
            size="small"
            class="rounded-lg! text-xs!"
            @click="shipModalVisible = false"
          />
          <Button
            label="Kirim Pesanan"
            icon="pi pi-send"
            size="small"
            class="bg-blue-600! border-blue-600! rounded-lg! text-xs! font-bold!"
            :loading="isShipSubmitting"
            @click="handleConfirmShip"
          />
        </div>
      </div>
    </Dialog>
  </section>
</template>
