<script setup lang="ts">
import { FLAT_SHIPPING_OPTIONS, type FlatShippingOption } from '@/constants/courier'
import CODAddressModal from '@/components/cod/CODAddressModal.vue'
import { buyerPaymentService } from '@/services/buyerPaymentService'
import { checkoutService } from '@/services/checkoutService'
import { locationService } from '@/services/locationService'
import { useCartStore } from '@/stores/cart'
import type { Cart, CodLocation } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const cartStore = useCartStore()
const checkoutItems = ref<Cart[]>([])
const shippingByItem = ref<Record<number, string>>({})

const loadCheckoutItems = () => {
  const raw = localStorage.getItem('checkoutItems')
  if (raw) {
    checkoutItems.value = JSON.parse(raw) as Cart[]
  }
}

const flatItems = computed(() => {
  return checkoutItems.value.flatMap((cart) =>
    cart.groups_by_shop.flatMap((group) =>
      group.items.map((item) => ({
        ...item,
        shop: group.shop,
        selectedShipping: shippingByItem.value[item.id] ?? 'reguler_15',
      }))
    )
  )
})

const groupsByShop = computed(() => {
  const map = new Map<number, { shop: any; items: typeof flatItems.value }>()
  for (const item of flatItems.value) {
    const shopId = item.shop?.id || 0
    if (!map.has(shopId)) {
      map.set(shopId, {
        shop: item.shop,
        items: [],
      })
    }
    map.get(shopId)!.items.push(item)
  }
  return Array.from(map.values())
})

const totalQuantity = computed(() => {
  return flatItems.value.reduce((sum, item) => sum + item.quantity, 0)
})

const setShipping = (itemId: number, value: string) => {
  shippingByItem.value[itemId] = value
}

// --- STATE LOADING SIMULASI API GET ---
const isLoadingPage = ref(true)

// --- STATE SIMULASI POST PESANAN ---
const isSubmittingOrder = ref(false)
const isSuccessModalOpen = ref(false)
const createdOrderData = ref({
  orderNumber: '',
  totalAmount: 0,
  paymentMethod: ''
})

// --- DATA STATE CHECKOUT ---
// interface AddressItem {
//   id: number
//   name: string
//   phone: string
//   fullAddress: string
//   isPrimary: boolean
// }

const listAddress = ref<CodLocation[]>([])
const selectedAddressId = ref<number | null>(null)
const selectedAddress = ref<CodLocation | null>(null)

const activeAddress = computed(() => {
  return selectedAddress.value ?? listAddress.value[0] ?? null
})

// Modal / Dialog States Alamat
const isAddressModalOpen = ref(false)
const showEditModal = ref(false)
const addressModalMode = ref<'add' | 'edit'>('edit')

const selectAddress = (id: number) => {
  selectedAddressId.value = id
  selectedAddress.value = listAddress.value.find((addr) => addr.id === id) ?? null
  isAddressModalOpen.value = false
}

const openAddAddress = () => {
  addressModalMode.value = 'add'
  isAddressModalOpen.value = false
  showEditModal.value = true
}

const openEditAddress = () => {
  if (!selectedAddress.value) return openAddAddress()
  addressModalMode.value = 'edit'
  isAddressModalOpen.value = false
  showEditModal.value = true
}

// saveNewAddress migrated to CODAddressModal
// Data Pengiriman & Pesanan
const courierOptions = FLAT_SHIPPING_OPTIONS
const defaultCourierOption = FLAT_SHIPPING_OPTIONS[0]!
const selectedCourierKey = ref(defaultCourierOption.key)
const buyerNotes = ref('')

const selectedCourierObj = computed<FlatShippingOption>(() => {
  return courierOptions.find(c => c.key === selectedCourierKey.value) ?? defaultCourierOption
})

const paymentOptions = [
  { label: 'Transfer Bank Manual', value: 'transfer' },
  { label: 'COD (Ketemuan)', value: 'cod' }
]

const selectedPaymentMethod = ref<'transfer' | 'cod'>('transfer')

const shippingMethod = computed<'kurir' | 'cod'>(() => (selectedPaymentMethod.value === 'cod' ? 'cod' : 'kurir'))

const paymentMethodLabel = computed(() => (selectedPaymentMethod.value === 'cod' ? 'COD (Ketemuan)' : 'Transfer Bank Manual'))

const paymentInfo = ref({
  bankName: 'Bank BCA',
  accountNumber: '123 456 7890',
  accountHolder: 'PT Kabita Indonesia'
})

const isCopied = ref(false)

const copyAccountNumber = () => {
  navigator.clipboard.writeText(paymentInfo.value.accountNumber.replace(/\s/g, ''))
  isCopied.value = true
  setTimeout(() => (isCopied.value = false), 2000)
}

const formatCurrency = (val: number) => {
  return 'Rp ' + val.toLocaleString('id-ID')
}

// Total Calc
const totalItemsPrice = computed(() => {
  return flatItems.value.reduce((sum, g) => sum + (Number(g.product?.price) || 0) * g.quantity, 0)
})
const totalShippingCost = computed(() => (selectedPaymentMethod.value === 'cod' ? 0 : selectedCourierObj.value.cost))
const grandTotal = computed(() => totalItemsPrice.value + totalShippingCost.value)

// --- STATE & SIMULASI UPLOAD BUKTI TRANSFER ---
const fileInput = ref<HTMLInputElement | null>(null)
const previewImage = ref<string | null>(null)
const selectedFile = ref<File | null>(null)
const isUploading = ref(false)
const uploadProgress = ref(0)
const isSuccessUpload = ref(false)

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    processUpload(target.files[0])
  }
}

const handleDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
    processUpload(event.dataTransfer.files[0])
  }
}

const processUpload = (file: File) => {
  if (!file.type.startsWith('image/')) {
    alert('Harap unggah file gambar (.PNG, .JPG, .JPEG)')
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    alert('Ukuran file maksimal 2MB')
    return
  }

  const reader = new FileReader()
  reader.onload = (e) => {
    previewImage.value = e.target?.result as string
  }
  reader.readAsDataURL(file)

  selectedFile.value = file

  isUploading.value = false
  isSuccessUpload.value = true
  uploadProgress.value = 100
}

const removeImage = () => {
  previewImage.value = null
  selectedFile.value = null
  isSuccessUpload.value = false
  uploadProgress.value = 0
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// --- SIMULASI GET DATA DARI API ---
const loadPaymentSettings = async () => {
  try {
    const data = await buyerPaymentService.getPaymentSettings()
    if (data) {
      paymentInfo.value = {
        bankName: data.bank_name,
        accountNumber: data.account_number,
        accountHolder: data.account_holder_name
      }
    }
  } catch (error) {
    console.error('Gagal memuat pengaturan pembayaran', error)
  }
}

const fetchCheckoutData = async () => {
  isLoadingPage.value = true
  try {
    await Promise.allSettled([
      loadLocations(),
      loadPaymentSettings()
    ])
  } finally {
    isLoadingPage.value = false
  }
}


const loadLocations = async () => {
  try {
    const data = await locationService.list()
    if (data.data.length > 0) {
      listAddress.value = data.data
      selectedAddress.value = data.data.find(addr => addr.is_default) ?? null
    }
  } catch (error) {
    console.error('Gagal memuat alamat COD', error)
  }
}

// --- SIMULASI POST PESANAN KE API ---
const handleCreateOrder = async () => {
  if (!activeAddress.value) {
    alert('Pilih alamat pengiriman terlebih dahulu.')
    return
  }

  isSubmittingOrder.value = true

  try {
    const isCod = selectedPaymentMethod.value === 'cod'
    const isExpress = ['YES', 'BEST', 'SUPER', 'NDS', 'EXPRESS', 'FAST'].includes(selectedCourierObj.value.serviceCode)
    const payload: Parameters<typeof checkoutService.checkout>[0] = {
      // Laravel checkout validates cart_items against cart_items.id,
      // not products.id.
      cart_items: flatItems.value.map((item) => item.id),
      shipping_method: isCod ? 'cod' : 'kurir',
      courier: isCod ? null : selectedCourierObj.value.fullCourierLabel,
      courier_type: isCod ? null : (isExpress ? 'express' : 'reguler'),
      shipping_cost: isCod ? 0 : selectedCourierObj.value.cost,
      payment_method: selectedPaymentMethod.value,
      shipping_address: activeAddress.value.address ?? '',
      location_id: activeAddress.value.id || undefined,
      notes: buyerNotes.value?.trim() || null
    }

    const order = await checkoutService.checkout(payload)

    // The backend checkout consumes the selected cart items. Refresh the
    // shared cart state so the header badge and /cart page are immediately
    // consistent after a successful order.
    await cartStore.loadCart()
    localStorage.removeItem('checkoutItems')

    const paymentId = order.payment?.id ?? order.id
    if (selectedFile.value && paymentId) {
      try {
        await buyerPaymentService.uploadProof(paymentId, {
          proof_image: selectedFile.value
        })
      } catch (uploadError) {
        console.error('Gagal mengunggah bukti transfer', uploadError)
      }
    }

    createdOrderData.value = {
      orderNumber: order.order_number ?? `#KBTA-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}`,
      totalAmount: Number(order.total_amount ?? grandTotal.value),
      paymentMethod: paymentMethodLabel.value
    }

    isSuccessModalOpen.value = true
  } catch (error) {
    console.error('Gagal membuat pesanan', error)
    alert('Gagal membuat pesanan. Coba lagi.')
  } finally {
    isSubmittingOrder.value = false
  }
}

const navigateToOrders = () => {
  isSuccessModalOpen.value = false
  router.push('/profile/orders')
}

const navigateToHome = () => {
  isSuccessModalOpen.value = false
  router.push('/')
}

onMounted(() => {
  loadCheckoutItems()
  fetchCheckoutData()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">

    <!-- LOADING SPINNER MINGGIR / BULAT UTAMA -->
    <div v-if="isLoadingPage" class="min-h-[70vh] flex flex-col items-center justify-center gap-3">
      <ProgressSpinner style="width: 48px; height: 48px" strokeWidth="4" animationDuration=".8s" />
      <span class="text-xs text-slate-500 font-medium">Memuat Data Checkout...</span>
    </div>

    <!-- KONTEN UTAMA DITAMPILKAN SETELAH LOADING -->
    <div v-else class="max-w-2xl lg:max-w-5xl xl:max-w-7xl mx-auto space-y-6">

      <!-- Top Bar Navigation -->
      <div class="flex items-center justify-between gap-3 text-xs text-slate-600">
        <router-link to="/cart" class="flex items-center gap-2 hover:text-blue-600 transition-colors font-medium">
          <i class="pi pi-arrow-left text-xs"></i>
          <span>Kembali</span>
        </router-link>
      </div>

      <!-- Main Layout Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- KOLOM KIRI (Alamat + Ringkasan Pesanan) -->
        <div class="lg:col-span-7 space-y-6">

          <!-- Section 1: Alamat Pengiriman -->
          <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2 font-bold text-slate-800 text-sm">
                <i class="pi pi-map-marker text-blue-600"></i>
                <h2>Alamat Pengiriman</h2>
              </div>
              <button @click="isAddressModalOpen = true"
                class="shrink-0 rounded-lg px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                Ubah Alamat
              </button>
            </div>

            <!-- Display Alamat Aktif -->
            <div class="space-y-1 text-xs text-slate-600">
              <p class="font-bold text-slate-800 text-sm">{{ activeAddress?.name }}</p>
              <p class="text-slate-500">{{ activeAddress?.phone }}</p>
              <p class="leading-relaxed mt-2 text-slate-600">{{ activeAddress?.address }}</p>
            </div>
          </div>

          <!-- Section 2: Ringkasan Pesanan -->
          <div class="space-y-5 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
              <div class="flex items-center gap-2 font-bold text-slate-800 text-sm">
                <i class="pi pi-shopping-bag text-blue-600"></i>
                <h2>Ringkasan Pesanan</h2>
              </div>
              <span class="text-xs text-slate-500 font-medium">
                {{ flatItems.length }} Produk ({{ totalQuantity }} item)
              </span>
            </div>

            <!-- List Toko & Barang -->
            <div class="space-y-4">
              <div v-for="(group, gIdx) in groupsByShop" :key="gIdx"
                class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 space-y-3">
                <div class="flex min-w-0 items-center gap-2 text-xs font-bold text-slate-700">
                  <i class="pi pi-shop text-blue-600"></i>
                  <span class="truncate">{{ group.shop?.name || 'Toko' }}</span>
                </div>

                <div class="divide-y divide-slate-100">
                  <div v-for="item in group.items" :key="item.id"
                    class="flex min-w-0 items-start gap-3 sm:gap-4 py-2.5 first:pt-0 last:pb-0">
                    <div
                      class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-white text-[10px] text-slate-400">
                      <img v-if="item.product?.images?.[0]?.url" :src="item.product.images[0].url!"
                        :alt="item.product?.name" class="h-full w-full object-cover" />
                      <span v-else>Tanpa gambar</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="text-xs font-bold text-slate-800 truncate">{{ item.product?.name }}</h3>
                      <p class="text-xs text-slate-500 mt-1">
                        {{ item.quantity }} x {{ formatCurrency(item.product?.price as number) }}
                      </p>
                    </div>
                    <div class="shrink-0 text-right text-xs font-bold text-slate-800">
                      {{ formatCurrency((item.product?.price as number) * item.quantity) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section Pengiriman -->
            <div v-if="selectedPaymentMethod !== 'cod'" class="min-w-0 space-y-2 rounded-xl border border-blue-100 bg-blue-50/40 p-3.5">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                  <i class="pi pi-truck text-blue-600"></i>
                  Pilih Layanan Pengiriman Kurir
                </label>
                <span class="text-[10px] text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded-full font-semibold">
                  {{ selectedCourierObj.courierName }} {{ selectedCourierObj.serviceCode }}
                </span>
              </div>

              <Select
                v-model="selectedCourierKey"
                :options="courierOptions"
                optionValue="key"
                class="w-full min-w-0! text-xs! bg-white! rounded-lg!"
              >
                <template #value="slotProps">
                  <div v-if="slotProps.value" class="flex items-center justify-between w-full pr-2 text-xs">
                    <div>
                      <strong class="text-slate-800">{{ selectedCourierObj.courierName }}</strong>
                      <span class="text-slate-500 ml-1.5">({{ selectedCourierObj.serviceCode }} - {{ selectedCourierObj.serviceName }})</span>
                      <span class="text-[11px] text-slate-400 ml-1.5">• {{ selectedCourierObj.etd }}</span>
                    </div>
                    <span class="font-bold text-blue-600 shrink-0">Rp {{ selectedCourierObj.cost.toLocaleString('id-ID') }}</span>
                  </div>
                </template>
                <template #option="slotProps">
                  <div class="flex items-center justify-between w-full py-1 text-xs">
                    <div class="min-w-0 pr-2">
                      <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <span>{{ slotProps.option.courierName }}</span>
                        <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">
                          {{ slotProps.option.serviceCode }}
                        </span>
                        <span class="font-normal text-slate-500">({{ slotProps.option.serviceName }})</span>
                      </div>
                      <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                        <i class="pi pi-clock text-[10px]"></i>
                        <span>Estimasi {{ slotProps.option.etd }}</span>
                      </div>
                    </div>
                    <span class="font-bold text-blue-600 shrink-0">
                      Rp {{ slotProps.option.cost.toLocaleString('id-ID') }}
                    </span>
                  </div>
                </template>
              </Select>
            </div>

            <div v-else class="min-w-0 space-y-1 rounded-xl border border-amber-100 bg-amber-50/50 p-3 text-xs text-amber-800">
              <div class="flex items-center gap-1.5 font-bold">
                <i class="pi pi-map-marker text-amber-600"></i>
                <span>Metode COD (Ketemuan Langsung)</span>
              </div>
              <p class="text-[11px] text-amber-700 leading-relaxed">
                Bebas biaya ongkir. Transaksi dan pembayaran dilakukan di lokasi titik temu yang disepakati.
              </p>
            </div>

            <!-- Catatan untuk Penjual -->
            <div class="min-w-0 space-y-1.5 pt-2">
              <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                <span>Catatan untuk Penjual</span>
                <span class="text-[11px] text-slate-400 font-normal">(opsional)</span>
              </label>
              <Textarea
                v-model="buyerNotes"
                placeholder="Contoh: Tolong packing bubble wrap lebih tebal, jangan dibanting..."
                rows="2"
                autoResize
                class="w-full text-xs! rounded-lg! border-slate-200!"
              />
            </div>
          </div>

        </div>

        <!-- KOLOM KANAN (Metode Pembayaran + Ringkasan Biaya) -->
        <div class="lg:col-span-5 space-y-6">

          <!-- Card 1: Metode Pembayaran -->
          <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex items-center gap-2 font-bold text-slate-800 text-sm">
              <i class="pi pi-credit-card text-blue-600"></i>
              <h2>Metode Pembayaran</h2>
            </div>

            <div class="bg-blue-50/60 rounded p-4 border border-blue-100 space-y-3">
              <div class="flex items-center gap-2 text-xs font-bold text-blue-700">
                <i class="pi pi-info-circle text-blue-600"></i>
                <span>{{ paymentMethodLabel }}</span>
              </div>

              <div class="space-y-1.5">
                <label class="text-[11px] text-slate-500 block">Pilih Metode Pembayaran</label>
                <Select v-model="selectedPaymentMethod" :options="paymentOptions" optionLabel="label"
                  optionValue="value" class="w-full min-w-0! text-xs! bg-white! rounded-lg!" />
              </div>

              <p v-if="selectedPaymentMethod === 'transfer'" class="text-[11px] text-slate-600 leading-relaxed">
                Silakan transfer sesuai dengan total tagihan ke rekening berikut:
              </p>

              <div v-if="selectedPaymentMethod === 'transfer'"
                class="bg-white rounded p-3 border border-blue-100 flex items-center justify-between">
                <div>
                  <span class="text-[10px] text-slate-400 uppercase font-semibold block">{{ paymentInfo.bankName
                    }}</span>
                  <span class="text-sm font-bold text-slate-800 tracking-wide">{{ paymentInfo.accountNumber }}</span>
                  <span class="text-[11px] text-slate-500 block mt-0.5">a.n. {{ paymentInfo.accountHolder }}</span>
                </div>
                <button @click="copyAccountNumber"
                  class="text-xs text-blue-600 font-semibold flex items-center gap-1 hover:underline">
                  <i :class="[isCopied ? 'pi pi-check' : 'pi pi-copy', 'text-xs']"></i>
                  <span>{{ isCopied ? 'Tersalin' : 'Salin' }}</span>
                </button>
              </div>

              <p v-if="selectedPaymentMethod === 'cod'" class="text-[11px] text-slate-600 leading-relaxed">
                Pesanan akan diproses dengan pembayaran COD saat barang diterima.
              </p>

              <input v-if="selectedPaymentMethod === 'transfer'" ref="fileInput" type="file" accept="image/*"
                class="hidden" @change="handleFileSelect" />

              <!-- Simulasi Upload Area -->
              <template v-if="selectedPaymentMethod === 'transfer'">
                <div v-if="!previewImage && !isUploading" @click="triggerFileInput" @dragover.prevent
                  @drop.prevent="handleDrop"
                  class="border-2 border-dashed border-blue-200 hover:border-blue-500 rounded p-5 bg-white flex flex-col items-center justify-center cursor-pointer transition-colors group">
                  <div
                    class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="pi pi-cloud-upload text-lg"></i>
                  </div>
                  <p class="text-xs font-semibold text-slate-700">Unggah Bukti Transfer</p>
                  <p class="text-[10px] text-slate-400 mt-1">Klik atau seret gambar ke sini (.PNG, .JPG)</p>
                </div>

                <div v-else-if="isUploading" class="border border-blue-100 rounded p-4 bg-white space-y-2">
                  <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                    <span class="flex items-center gap-2">
                      <i class="pi pi-spin pi-spinner text-blue-600"></i>
                      Mengunggah gambar...
                    </span>
                    <span>{{ uploadProgress }}%</span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 transition-all duration-200" :style="{ width: uploadProgress + '%' }">
                    </div>
                  </div>
                </div>

                <div v-else-if="isSuccessUpload && previewImage" class="space-y-2">
                  <div class="relative border border-slate-200 rounded overflow-hidden bg-white group">
                    <img :src="previewImage" alt="Bukti Transfer" class="w-full h-36 object-cover" />
                    <div
                      class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                      <button @click="triggerFileInput"
                        class="bg-white text-slate-800 text-xs px-3 py-1.5 rounded-lg font-semibold hover:bg-slate-100">
                        Ganti
                      </button>
                      <button @click="removeImage"
                        class="bg-rose-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold hover:bg-rose-700">
                        Hapus
                      </button>
                    </div>
                  </div>

                  <!-- <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                      <i class="pi pi-check-circle text-xs"></i>
                      <span>Berhasil diunggah</span>
                    </div>
                    <button @click="removeImage" class="text-[11px] text-rose-500 hover:underline">Hapus</button>
                  </div> -->
                </div>

                <p class="text-[10px] text-slate-400 italic">
                  *Pesanan akan diproses setelah bukti transfer dikonfirmasi oleh admin.
                </p>
              </template>
            </div>
          </div>

          <!-- Card 2: Ringkasan Biaya -->
          <div class="bg-white rounded p-6 shadow-sm border border-slate-100 space-y-4">
            <h2 class="font-bold text-slate-800 text-sm">Ringkasan Biaya</h2>

            <div class="space-y-2 text-xs border-b border-slate-100 pb-4">
              <div class="flex items-center justify-between text-slate-600">
                <span>Total Harga ({{ flatItems.length }} barang)</span>
                <span class="font-medium text-slate-800">{{ formatCurrency(totalItemsPrice) }}</span>
              </div>
              <div class="flex items-center justify-between text-slate-600">
                <span>Total Ongkos Kirim</span>
                <span class="font-medium text-slate-800">{{ selectedPaymentMethod === 'cod' ? 'Gratis' :
                  formatCurrency(totalShippingCost) }}</span>
              </div>
              <div class="flex items-center justify-between text-slate-600">
                <span>Metode Pembayaran</span>
                <span class="font-medium text-slate-800">{{ paymentMethodLabel }}</span>
              </div>
            </div>

            <div class="flex items-center justify-between pt-1">
              <span class="text-xs font-bold text-slate-800">Total Tagihan</span>
              <span class="text-lg font-bold text-blue-600">{{ formatCurrency(grandTotal) }}</span>
            </div>

            <Button label="Buat Pesanan"
              :disabled="(selectedPaymentMethod === 'transfer' && !isSuccessUpload) || isSubmittingOrder"
              :loading="isSubmittingOrder" @click="handleCreateOrder"
              class="w-full bg-blue-600! border-blue-600! py-3! text-xs! font-bold! rounded! shadow-sm! hover:bg-blue-700! disabled:opacity-50! disabled:cursor-not-allowed!" />
          </div>

        </div>

      </div>

    </div>

    <!-- DIALOG / MODAL PILIH & UBAH ALAMAT -->

    <Dialog v-model:visible="isAddressModalOpen" modal header="Pilih Alamat Pengiriman"
      :style="{ width: 'min(92vw, 560px)' }" class="rounded-2xl!">
      <div class="space-y-3">
        <p class="text-sm text-slate-500">Pilih alamat yang akan digunakan untuk pesanan ini.</p>
        <div v-if="listAddress.length" class="max-h-72 space-y-2 overflow-y-auto pr-1">
          <button v-for="address in listAddress" :key="address.id" type="button"
            class="flex w-full items-start gap-3 rounded-xl border p-4 text-left transition"
            :class="activeAddress?.id === address.id ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50'"
            @click="selectAddress(address.id!)">
            <i class="pi pi-map-marker mt-0.5 text-blue-600"></i>
            <span class="min-w-0 flex-1">
              <span class="flex flex-wrap items-center gap-2 text-sm font-bold text-slate-800">
                {{ address.name }}
                <span v-if="address.is_default"
                  class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Utama</span>
              </span>
              <span class="mt-1 block text-xs text-slate-500">{{ address.phone }}</span>
              <span class="mt-1 block break-words text-xs leading-5 text-slate-600">{{ address.address }}</span>
            </span>
            <i v-if="activeAddress?.id === address.id" class="pi pi-check-circle text-blue-600"></i>
          </button>
        </div>
        <div v-else class="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">
          Belum ada alamat tersimpan.
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
          <Button label="Tutup" severity="secondary" outlined @click="isAddressModalOpen = false" />
          <Button v-if="selectedAddress" label="Edit alamat terpilih" icon="pi pi-pencil" outlined
            @click="openEditAddress" />
          <Button label="Tambah alamat baru" icon="pi pi-plus" @click="openAddAddress" />
        </div>
      </div>
    </Dialog>

    <CODAddressModal v-model:visible="showEditModal" :mode="addressModalMode"
      :codLocation="addressModalMode === 'edit' ? selectedAddress : null" @saved="loadLocations" />
    <!-- POPUP MODAL SUKSES PESANAN (MOCKUP GAMBAR) -->
    <Dialog v-model:visible="isSuccessModalOpen" modal :closable="false" :style="{ width: '90%', maxWidth: '480px' }"
      class="rounded-3xl! overflow-hidden">
      <div class="py-6 px-2 text-center space-y-6">

        <!-- Animated / Green Circle Check Icon -->
        <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto">
          <div class="w-14 h-14 rounded-full bg-emerald-400 text-white flex items-center justify-center shadow-sm">
            <i class="pi pi-check text-2xl font-bold"></i>
          </div>
        </div>

        <!-- Title & Subtitle -->
        <div class="space-y-2">
          <h2 class="text-lg font-extrabold text-slate-800">Pesanan Anda Telah Dibuat!</h2>
          <p class="text-xs text-slate-500 w-full mx-auto leading-relaxed">
            Terima kasih telah berbelanja di Kabita. Pesanan Anda sedang menunggu verifikasi pembayaran oleh admin.
          </p>
        </div>

        <!-- Info Card Banner -->
        <div class="bg-indigo-50/70 border border-indigo-100/80 rounded p-4 flex items-start gap-3 text-left">
          <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
            <i class="pi pi-info-circle text-xs"></i>
          </div>
          <p class="text-[11px] text-slate-600 leading-relaxed">
            Estimasi verifikasi 1×24 jam. Pastikan Anda telah mengunggah bukti bayar pada halaman detail pesanan.
          </p>
        </div>

        <!-- Summary Specs Card -->
        <div class="bg-slate-50/80 border border-slate-100 rounded p-4 grid grid-cols-3 gap-2 text-left">
          <div>
            <span class="text-[10px] text-slate-400 block mb-1">Nomor Pesanan</span>
            <span class="text-xs font-bold text-slate-800 tracking-tight">{{ createdOrderData.orderNumber }}</span>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 block mb-1">Total Pembayaran</span>
            <span class="text-xs font-bold text-blue-600">{{ formatCurrency(createdOrderData.totalAmount) }}</span>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 block mb-1">Metode Pembayaran</span>
            <span class="text-xs font-bold text-slate-800 leading-tight block">{{ createdOrderData.paymentMethod
              }}</span>
          </div>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
          <Button label="Lihat Detail Pesanan" icon="pi pi-file"
            class="flex-1 bg-blue-600! border-blue-600! text-xs! py-3! rounded! font-bold!" @click="navigateToOrders" />
          <Button label="Kembali ke Beranda" severity="secondary" outlined
            class="flex-1 text-xs! py-3! rounded! font-bold! border-slate-300! text-blue-600! hover:bg-slate-50!"
            @click="navigateToHome" />
        </div>

      </div>
    </Dialog>

  </div>
</template>
