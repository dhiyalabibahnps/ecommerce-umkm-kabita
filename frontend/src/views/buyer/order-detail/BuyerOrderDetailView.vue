<script setup lang="ts">
import { getApiErrorMessage } from '@/services/apiError'
import { buyerOrderService } from '@/services/buyerOrderService'
import type { Order } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const order = ref<Order | null>(null)
const loading = ref(true)
const actionLoading = ref(false)
const errorMessage = ref('')
const helpVisible = ref(false)

const isCod = computed(() => order.value?.shipping_method === 'cod')
const isCourier = computed(() => order.value?.shipping_method === 'kurir')
const totalItems = computed(() => order.value?.items?.reduce((sum, item) => sum + item.quantity, 0) || 0)
const canConfirm = computed(() => order.value?.status === 'shipped' || order.value?.status === 'cod_meeting')
const canConfirmCod = computed(() => isCod.value && order.value?.status === 'cod_meeting')
const isStepDone = (step: string) => {
  const rank: Record<string, number> = { awaiting_verification: 1, processing: 2, packed: 3, shipped: 4, cod_meeting: 5, completed: 6, cancelled: 0 }
  const stepRank: Record<string, number> = { 'Pesanan dibuat': 1, Diproses: 2, Dikemas: 3, Dikirim: 4, Ketemuan: 5, Selesai: 6 }
  return (rank[order.value?.status || 'awaiting_verification'] || 0) >= (stepRank[step] || 0)
}

const shippingSteps = computed(() => {
  if (isCod.value) return ['Pesanan dibuat', 'Diproses', 'Dikemas', 'Dikirim', 'Ketemuan', 'Selesai']
  return ['Pesanan dibuat', 'Diproses', 'Dikemas', 'Dikirim', 'Selesai']
})

const shippingTitle = computed(() => {
  if (isCod.value) return 'Status Pengiriman COD'
  if (isCourier.value) return 'Status Pengiriman Kurir'
  return 'Status Pesanan'
})

const shippingBadge = computed(() => {
  if (isCod.value) return { label: 'COD', severity: 'warn' as const }
  if (isCourier.value) return { label: 'Kurir', severity: 'info' as const }
  return { label: order.value?.shipping_method || 'Reguler', severity: 'secondary' as const }
})

const shippingNote = computed(() => {
  if (isCod.value) return 'Bayar saat barang diterima ke kurir/penjual.'
  if (isCourier.value) return 'Pesanan akan dikirim melalui jasa kurir.'
  return ''
})

const currency = (value: string | number) => new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
}).format(Number(value) || 0)

const date = (value?: string) => value ? new Intl.DateTimeFormat('id-ID', {
  dateStyle: 'medium', timeStyle: 'short',
}).format(new Date(value)) : '—'

const loadOrder = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    order.value = await buyerOrderService.getDetail(Number(route.query.id))
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Detail pesanan tidak dapat dimuat.')
  } finally {
    loading.value = false
  }
}

const confirmReceived = async () => {
  if (!order.value || !canConfirm.value) return
  actionLoading.value = true
  try {
    order.value = await buyerOrderService.confirmReceived(order.value.id)
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Pesanan belum dapat diselesaikan.')
  } finally {
    actionLoading.value = false
  }
}

const confirmCodPayment = async () => {
  if (!order.value || !canConfirmCod.value) return
  actionLoading.value = true
  try {
    order.value = await buyerOrderService.confirmCodPayment(order.value.id)
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Konfirmasi pembayaran COD gagal.')
  } finally {
    actionLoading.value = false
  }
}

const contactSeller = () => {
  const phone = order.value?.shop?.phone?.replace(/\D/g, '')
  if (phone) {
    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(`Halo, saya ingin menanyakan pesanan ${order.value?.order_number}.`)}`, '_blank', 'noopener')
  } else {
    helpVisible.value = true
  }
}

onMounted(loadOrder)
</script>

<template>
  <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-5">
      <button class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600"
        @click="router.push('/profile/orders')">
        <i class="pi pi-arrow-left"></i>Kembali ke pesanan
      </button>

      <div v-if="loading" class="flex flex-col items-center justify-center rounded-2xl bg-white py-24 shadow-sm">
        <ProgressSpinner style="width: 42px; height: 42px" />
        <span class="mt-3 text-sm text-slate-500">Memuat detail pesanan...</span>
      </div>
      <Message v-else-if="errorMessage" severity="error">{{ errorMessage }}</Message>

      <template v-else-if="order">
        <div class="flex flex-col justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm sm:flex-row sm:items-center">
          <div>
            <h1 class="text-xl font-bold text-slate-900">Detail Pesanan</h1>
            <p class="mt-1 text-sm text-slate-500">{{ order.order_number }} · {{ date(order.created_at) }}</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <Tag :value="shippingBadge.label" :severity="shippingBadge.severity" />
            <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-bold capitalize text-blue-700">{{
              order.status }}</span>
          </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_340px]">
          <div class="space-y-5">
            <section class="rounded-2xl bg-white p-5 shadow-sm">
              <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900">{{ shippingTitle }}</h2>
                <Tag :value="shippingBadge.label" :severity="shippingBadge.severity" />
              </div>
              <p v-if="shippingNote" class="mt-1 text-xs text-slate-500">{{ shippingNote }}</p>
              <div class="mt-4 flex items-center gap-2 text-xs">
                <span v-for="step in shippingSteps" :key="step" class="flex flex-1 items-center gap-2">
                  <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                    :class="isStepDone(step) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'"><i
                      :class="isStepDone(step) ? 'pi pi-check' : 'pi pi-circle-fill text-[6px]'"></i></span>
                  <span class="hidden sm:inline" :class="isStepDone(step) ? 'text-slate-700' : 'text-slate-400'">{{ step
                    }}</span>
                </span>
              </div>
              <div v-if="isCourier && order.tracking_number"
                class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                <span class="font-semibold">No. Resi:</span> {{ order.tracking_number }}
              </div>
              <Button v-if="canConfirm && !isCod" label="Pesanan Diterima / Selesaikan" icon="pi pi-check"
                :loading="actionLoading" class="mt-6 w-full" @click="confirmReceived" />
              <Button v-else-if="canConfirmCod" label="Selesaikan Pesanan COD" icon="pi pi-check"
                :loading="actionLoading" class="mt-6 w-full" @click="confirmReceived" />
              <p v-else-if="order.status === 'completed'"
                class="mt-5 rounded-xl bg-emerald-50 p-3 text-center text-sm font-semibold text-emerald-700">Pesanan
                sudah selesai.</p>
            </section>

            <section class="rounded-2xl bg-white p-5 shadow-sm">
              <h2 class="mb-4 font-bold text-slate-900">Produk ({{ totalItems }})</h2>
              <div v-for="item in order.items" :key="item.id"
                class="flex gap-3 border-b border-slate-100 py-3 last:border-0">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
                  <i class="pi pi-box text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="font-semibold text-slate-800">{{ item.product?.name || 'Produk' }}</p>
                  <p class="text-xs text-slate-500">{{ item.quantity }} × {{ currency(item.price_snapshot) }}</p>
                </div>
                <strong class="text-sm text-slate-800">{{ currency(Number(item.price_snapshot) * item.quantity)
                }}</strong>
              </div>
            </section>
          </div>

          <aside class="space-y-5">
            <section class="rounded-2xl bg-white p-5 shadow-sm">
              <h2 class="mb-4 font-bold text-slate-900">Ringkasan Pembayaran</h2>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ currency(order.subtotal) }}</span></div>
                <div class="flex justify-between"><span>Pengiriman</span><span>{{ currency(order.shipping_cost)
                }}</span></div>
                <div class="flex justify-between border-t pt-3 font-bold"><span>Total</span><span
                    class="text-blue-600">{{ currency(order.total_amount) }}</span></div>
              </div>
            </section>
            <section v-if="isCod" class="rounded-2xl bg-white p-5 shadow-sm">
              <h2 class="mb-4 font-bold text-slate-900">Informasi Ketemuan COD</h2>
              <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Status Pembayaran</span>
                  <Tag :value="order.payment?.status || 'pending'"
                    :severity="order.payment?.status === 'verified' ? 'success' : order.payment?.status === 'rejected' ? 'danger' : 'warn'" />
                </div>
                <div v-if="order.payment?.proof_image" class="rounded-xl border border-slate-100 p-3">
                  <p class="mb-2 text-xs font-semibold text-slate-500">Bukti Pembayaran</p>
                  <img :src="order.payment.proof_image" alt="Bukti pembayaran COD"
                    class="h-40 w-full rounded-lg object-cover" />
                </div>
                <div class="rounded-xl border border-slate-100 p-3">
                  <p class="mb-2 text-xs font-semibold text-slate-500">Informasi Ketemuan</p>
                  <div class="w-full h-36 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                    <div class="text-center">
                      <i class="pi pi-map-marker text-red-500 text-2xl"></i>
                      <p class="mt-2 text-xs font-medium px-2 text-center">{{ order.shipping_address }}</p>
                    </div>
                  </div>
                </div>
                <p v-if="order.payment?.status === 'pending'" class="text-xs text-slate-500">Bayar saat barang diterima
                  ke kurir/penjual.</p>
                <p v-else-if="order.payment?.status === 'verified'" class="text-xs text-emerald-600">Pembayaran COD
                  sudah dikonfirmasi.</p>
                <p v-else-if="order.payment?.status === 'rejected'" class="text-xs text-red-600">Bukti pembayaran
                  ditolak. Hubungi penjual.</p>
              </div>
            </section>
            <section class="rounded-2xl bg-white p-5 shadow-sm">
              <h2 class="mb-1 font-bold text-slate-900">{{ order.shop?.name || 'Toko' }}</h2>
              <p class="mb-4 text-xs text-slate-500">{{ order.shipping_address || 'Alamat belum tersedia' }}</p><Button
                label="Hubungi Penjual" icon="pi pi-whatsapp" class="mb-2 w-full" @click="contactSeller" /><Button
                label="Bantuan Pesanan" icon="pi pi-question-circle" outlined class="w-full"
                @click="helpVisible = true" />
            </section>
          </aside>
        </div>
      </template>
    </div>
    <Dialog v-model:visible="helpVisible" modal header="Bantuan Pesanan" :style="{ width: 'min(440px, 92vw)' }">
      <p class="text-sm leading-6 text-slate-600">Simpan nomor pesanan saat menghubungi bantuan. Untuk kendala
        pembayaran,
        pengiriman, atau barang diterima, hubungi admin Kabita melalui pusat bantuan.</p><Button
        label="Ke Profil Pesanan" class="mt-4 w-full" @click="helpVisible = false; router.push('/profile/orders')" />
    </Dialog>
  </div>
</template>
