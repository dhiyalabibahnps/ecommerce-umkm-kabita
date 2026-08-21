<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'

import OrderStatusBadge from '@/components/ui/OrderStatusBadge.vue'
import OrderStepper from '@/views/seller/components/order-detail/OrderStepper.vue'
import OrderTimeline from '@/views/seller/components/order-detail/OrderTimeline.vue'
import OrderInvoiceModal from '@/components/invoice/OrderInvoiceModal.vue'
import { formatCourierDisplay } from '@/constants/courier'
import { getApiErrorMessage } from '@/services/apiError'
import { buyerOrderService } from '@/services/buyerOrderService'
import { buyerPaymentService } from '@/services/buyerPaymentService'
import { useChatStore } from '@/stores/chat'
import type { Order } from '@/types'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const chatStore = useChatStore()

const order = ref<Order | null>(null)
const loading = ref(true)
const actionLoading = ref(false)
const uploadLoading = ref(false)
const errorMessage = ref('')
const proofModalVisible = ref(false)
const invoiceModalVisible = ref(false)
const reuploadFileInput = ref<HTMLInputElement | null>(null)
const resiCopied = ref(false)

const isCod = computed(() => order.value?.shipping_method === 'cod' || order.value?.payment_method === 'cod')
const canConfirmReceived = computed(() => order.value?.status === 'shipped' || order.value?.status === 'cod_meeting')

const handleOpenChat = () => {
  if (order.value) {
    chatStore.openOrderChat(
      order.value.id,
      order.value.order_number,
      order.value.shop?.name,
      'buyer'
    )
  }
}

const loadOrder = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const orderId = Number(route.query.id)
    if (!orderId) {
      errorMessage.value = 'ID pesanan tidak valid.'
      return
    }
    order.value = await buyerOrderService.getDetail(orderId)
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Detail pesanan tidak dapat dimuat.')
  } finally {
    loading.value = false
  }
}

const confirmReceived = async () => {
  if (!order.value || !canConfirmReceived.value) return
  actionLoading.value = true
  try {
    order.value = await buyerOrderService.confirmReceived(order.value.id)
    toast.add({
      severity: 'success',
      summary: 'Pesanan Selesai',
      detail: 'Terima kasih telah mengonfirmasi penerimaan barang.',
      life: 3000,
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Pesanan belum dapat diselesaikan.'),
      life: 4000,
    })
  } finally {
    actionLoading.value = false
  }
}

const handleUploadProof = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file || !order.value) return

  if (!file.type.startsWith('image/')) {
    toast.add({ severity: 'warn', summary: 'Format Salah', detail: 'Harap unggah file gambar (.PNG, .JPG, .WEBP)', life: 3000 })
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    toast.add({ severity: 'warn', summary: 'Ukuran Terlalu Besar', detail: 'Ukuran file maksimal 2MB', life: 3000 })
    return
  }

  uploadLoading.value = true
  try {
    const paymentId = order.value.payment?.id ?? order.value.id
    await buyerPaymentService.uploadProof(paymentId, { proof_image: file })
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Bukti pembayaran berhasil diunggah.', life: 3000 })
    await loadOrder()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: getApiErrorMessage(error, 'Gagal mengunggah bukti pembayaran.'), life: 4000 })
  } finally {
    uploadLoading.value = false
  }
}

const copyTrackingNumber = () => {
  if (!order.value?.tracking_number) return
  navigator.clipboard.writeText(order.value.tracking_number)
  resiCopied.value = true
  toast.add({ severity: 'success', summary: 'Tersalin', detail: 'Nomor resi berhasil disalin.', life: 2000 })
  setTimeout(() => (resiCopied.value = false), 2000)
}

const formatCurrency = (val: string | number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(val) || 0)

const formatDate = (val?: string) =>
  val
    ? new Date(val).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      })
    : '—'

onMounted(loadOrder)
</script>

<template>
  <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8 pb-16">
    <!-- Back Button -->
    <div class="mb-4">
      <button
        type="button"
        class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-blue-600 transition cursor-pointer"
        @click="router.push('/profile/orders')"
      >
        <i class="pi pi-arrow-left text-[11px]"></i>
        <span>Kembali ke Daftar Pesanan</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24 text-slate-400">
      <ProgressSpinner style="width: 40px; height: 40px" />
      <p class="mt-3 text-xs">Memuat detail pesanan Anda...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage || !order" class="rounded-2xl border border-rose-100 bg-white p-10 text-center shadow-2xs">
      <i class="pi pi-exclamation-triangle text-3xl text-rose-500"></i>
      <h2 class="mt-3 text-sm font-bold text-slate-800">Detail Pesanan Tidak Ditemukan</h2>
      <p class="mt-1 text-xs text-rose-600">{{ errorMessage || 'Silakan coba periksa kembali nomor pesanan Anda.' }}</p>
      <Button label="Kembali ke Pesanan Saya" size="small" class="mt-4 rounded-xl! text-xs!" @click="router.push('/profile/orders')" />
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-4">
      <!-- Order Header Card -->
      <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2.5">
              <span class="font-mono text-base font-black tracking-tight text-slate-900 sm:text-lg">
                {{ order.order_number }}
              </span>
              <OrderStatusBadge :status="order.status" size="normal" role="buyer" />
            </div>
            <p class="mt-1 text-xs text-slate-500">
              Dipesan pada <strong class="text-slate-700">{{ formatDate(order.created_at) }}</strong>
            </p>
          </div>

          <!-- Top Actions -->
          <div class="flex flex-wrap items-center gap-2">
            <Button
              label="Cetak Invoice"
              icon="pi pi-print"
              severity="secondary"
              outlined
              size="small"
              class="text-xs! rounded-lg! text-slate-700! border-slate-300! hover:bg-slate-50!"
              @click="invoiceModalVisible = true"
            />
            <Button
              label="Hubungi Penjual"
              icon="pi pi-comments"
              severity="secondary"
              outlined
              size="small"
              class="text-xs! rounded-lg! text-blue-600! border-blue-200! hover:bg-blue-50!"
              @click="handleOpenChat"
            />

            <!-- Confirm Received Action -->
            <Button
              v-if="canConfirmReceived"
              label="Konfirmasi Barang Diterima"
              icon="pi pi-check"
              size="small"
              class="bg-blue-600! border-blue-600! text-xs! rounded-lg! font-bold!"
              :loading="actionLoading"
              @click="confirmReceived"
            />
          </div>
        </div>
      </div>

      <!-- Stepper Component -->
      <OrderStepper
        :status="order.status"
        :shippingMethod="order.shipping_method"
        :isVerified="order.payment?.status === 'verified'"
      />

      <!-- Content Grid: Left Items & Info + Right Sidebar -->
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <!-- Left Column -->
        <div class="space-y-4 lg:col-span-8">
          <!-- Product List Card -->
          <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
              <div class="flex items-center gap-2">
                <i class="pi pi-shop text-blue-600 text-sm"></i>
                <strong class="text-xs font-bold text-slate-900">{{ order.shop?.name || 'Toko' }}</strong>
              </div>
              <span class="text-[11px] text-slate-500">{{ order.items?.length || 0 }} Produk</span>
            </div>

            <div class="divide-y divide-slate-100">
              <div
                v-for="item in order.items"
                :key="item.id"
                class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
              >
                <div class="flex items-center gap-3 min-w-0">
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
                    <h4 class="text-xs font-bold text-slate-800 line-clamp-1">
                      {{ item.product?.name || 'Produk' }}
                    </h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                      {{ item.quantity }} × {{ formatCurrency(item.price_snapshot) }}
                    </p>
                  </div>
                </div>

                <div class="text-right shrink-0">
                  <span class="text-xs font-bold text-slate-900">
                    {{ formatCurrency(Number(item.price_snapshot) * item.quantity) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Informasi Pengiriman Card -->
          <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Pengiriman</h3>
              <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-semibold">
                {{ isCod ? 'COD' : 'Kurir Ekspedisi' }}
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div class="space-y-1">
                <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Kurir & Layanan</span>
                <p class="font-bold text-slate-800">
                  {{ isCod ? 'COD (Ketemuan Langsung)' : formatCourierDisplay(order.courier) }}
                </p>
              </div>

              <div v-if="!isCod" class="space-y-1">
                <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Nomor Resi</span>
                <div v-if="order.tracking_number" class="flex items-center gap-2">
                  <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded">
                    {{ order.tracking_number }}
                  </span>
                  <Button
                    :icon="resiCopied ? 'pi pi-check' : 'pi pi-copy'"
                    :label="resiCopied ? 'Tersalin' : 'Salin Resi'"
                    size="small"
                    severity="secondary"
                    outlined
                    class="text-xs! py-0.5! px-2! rounded-lg!"
                    @click="copyTrackingNumber"
                  />
                </div>
                <div v-else>
                  <span v-if="order.status === 'shipped' || order.status === 'completed'" class="text-slate-600 font-mono text-xs">
                    Resi sedang diproses
                  </span>
                  <span v-else class="text-slate-400 italic text-xs">
                    Nomor resi akan diinput oleh penjual saat paket dikirim
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
              <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider mb-1">
                {{ isCod ? 'Titik Temu COD' : 'Alamat Pengiriman' }}
              </span>
              <p class="text-slate-700 leading-relaxed">{{ order.shipping_address }}</p>
            </div>
          </div>

          <!-- Catatan Pembeli Card (if any) -->
          <div
            v-if="order.notes"
            class="rounded-xl bg-amber-50/70 border border-amber-200/80 p-3.5 flex items-start gap-3 shadow-2xs"
          >
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 mt-0.5">
              <i class="pi pi-comment text-xs"></i>
            </div>
            <div class="min-w-0 flex-1">
              <h5 class="text-xs font-bold text-amber-900">Catatan untuk Penjual</h5>
              <p class="text-xs text-amber-800/90 italic mt-0.5 leading-relaxed">
                "{{ order.notes }}"
              </p>
            </div>
          </div>

          <!-- Order Timeline Component -->
          <OrderTimeline
            :status="order.status"
            :shippingMethod="order.shipping_method"
            :paymentMethod="order.payment_method"
            :isVerified="order.payment?.status === 'verified'"
            :hasProofImage="Boolean(order.payment?.proof_image)"
            :courier="order.courier"
            :trackingNumber="order.tracking_number"
            :createdAt="order.created_at"
            :updatedAt="order.updated_at"
          />
        </div>

        <!-- Right Column Sidebar -->
        <div class="space-y-4 lg:col-span-4">
          <!-- Toko Card -->
          <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Penjual</h3>
              <span class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-semibold">Toko UMKM</span>
            </div>

            <div class="space-y-3">
              <div>
                <h4 class="text-xs font-bold text-slate-900">{{ order.shop?.name || 'Toko Kabita' }}</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ order.shop?.address || 'Indonesia' }}</p>
              </div>

              <div class="grid grid-cols-2 gap-2 pt-1">
                <Button
                  label="Chat Penjual"
                  icon="pi pi-comments"
                  severity="secondary"
                  outlined
                  size="small"
                  class="w-full text-xs! rounded-lg! text-blue-600! border-blue-200! hover:bg-blue-50!"
                  @click="handleOpenChat"
                />

                <Button
                  v-if="order.shop?.slug"
                  label="Kunjungi Toko"
                  icon="pi pi-shop"
                  size="small"
                  class="w-full text-xs! rounded-lg! bg-slate-800! border-slate-800! hover:bg-slate-900!"
                  @click="router.push(`/toko/${order.shop.slug}`)"
                />
              </div>
            </div>
          </div>

          <!-- Ringkasan Pembayaran & Bukti Transfer -->
          <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Pembayaran</h3>
              <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-semibold">
                {{ order.payment_method === 'transfer' ? 'Transfer Bank' : 'COD' }}
              </span>
            </div>

            <div class="space-y-2 text-xs text-slate-600">
              <div class="flex justify-between">
                <span>Subtotal Produk</span>
                <span class="font-medium text-slate-800">{{ formatCurrency(order.subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Ongkos Kirim</span>
                <span class="font-medium text-slate-800">{{ formatCurrency(order.shipping_cost) }}</span>
              </div>
              <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-xs text-slate-900">
                <span>Total Belanja</span>
                <span class="text-sm text-blue-600 font-extrabold">{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </div>

            <!-- Bukti Pembayaran Section (Transfer) -->
            <div v-if="!isCod" class="mt-4 pt-3 border-t border-slate-100 space-y-2.5">
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Status Verifikasi</span>
                <span
                  class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                  :class="[
                    order.payment?.status === 'verified'
                      ? 'bg-emerald-50 text-emerald-700'
                      : order.payment?.status === 'rejected'
                        ? 'bg-rose-50 text-rose-700'
                        : 'bg-amber-50 text-amber-700'
                  ]"
                >
                  {{
                    order.payment?.status === 'verified'
                      ? 'Diverifikasi'
                      : order.payment?.status === 'rejected'
                        ? 'Ditolak'
                        : 'Menunggu Verifikasi'
                  }}
                </span>
              </div>

              <!-- Bukti Image Preview Thumbnail -->
              <div v-if="order.payment?.proof_image" class="rounded-lg border border-slate-200/80 p-2.5 space-y-2 bg-slate-50/50">
                <div class="flex items-center justify-between">
                  <span class="text-[11px] font-bold text-slate-700">Bukti Transfer</span>
                  <span class="text-[10px] text-emerald-600 font-semibold">Sudah Diunggah</span>
                </div>
                <img
                  :src="order.payment.proof_image"
                  alt="Bukti Transfer"
                  class="h-32 w-full object-cover rounded border border-slate-200 cursor-pointer hover:opacity-90 transition"
                  @click="proofModalVisible = true"
                />
                <Button
                  label="Lihat Bukti Bayar"
                  icon="pi pi-eye"
                  size="small"
                  outlined
                  class="w-full text-xs! py-1! rounded-lg!"
                  @click="proofModalVisible = true"
                />
              </div>

              <!-- Rejection Notice & Reupload -->
              <div v-if="order.payment?.status === 'rejected'" class="rounded-lg bg-rose-50 p-2.5 text-xs text-rose-700 border border-rose-200/70">
                <p class="font-bold">Bukti Pembayaran Ditolak</p>
                <p v-if="order.payment?.rejection_reason" class="mt-0.5 text-[11px] leading-relaxed">
                  Alasan: {{ order.payment.rejection_reason }}
                </p>
                <p class="mt-1 text-[11px]">Silakan unggah ulang bukti transfer yang valid di bawah ini:</p>
              </div>

              <div
                v-if="(!order.payment?.proof_image || order.payment?.status === 'rejected') && order.status === 'awaiting_verification'"
                class="pt-1"
              >
                <input
                  type="file"
                  ref="reuploadFileInput"
                  accept="image/*"
                  class="hidden"
                  @change="handleUploadProof"
                />
                <Button
                  :label="order.payment?.status === 'rejected' ? 'Unggah Ulang Bukti Bayar' : 'Unggah Bukti Bayar'"
                  icon="pi pi-upload"
                  size="small"
                  :loading="uploadLoading"
                  class="w-full text-xs! rounded-lg! bg-blue-600! border-blue-600! font-bold!"
                  @click="reuploadFileInput?.click()"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Lihat Bukti Transfer Penuh -->
    <Dialog
      v-model:visible="proofModalVisible"
      modal
      header="Bukti Pembayaran Transfer"
      :style="{ width: 'min(500px, 94vw)' }"
      class="rounded-2xl!"
    >
      <div class="p-1 text-center">
        <img
          v-if="order?.payment?.proof_image"
          :src="order.payment.proof_image"
          alt="Bukti Transfer Penuh"
          class="w-full max-h-[70vh] object-contain rounded-lg border border-slate-200"
        />
      </div>
    </Dialog>

    <!-- Modal Cetak Invoice -->
    <OrderInvoiceModal
      v-model:visible="invoiceModalVisible"
      :order="order"
    />
  </div>
</template>
