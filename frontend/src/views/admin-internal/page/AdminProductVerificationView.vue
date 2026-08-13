<script setup lang="ts">
import { getApiErrorMessage } from '@/services/apiError'
import { adminProductService } from '@/services/adminProductService'
import type { Product } from '@/types/entities'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref } from 'vue'
import AdminProductApproveModal from '../components/product-verification/AdminProductApproveModal.vue'
import AdminProductDetailModal from '../components/product-verification/AdminProductDetailModal.vue'
import AdminProductFilter from '../components/product-verification/AdminProductFilter.vue'
import AdminProductRejectModal from '../components/product-verification/AdminProductRejectModal.vue'
import AdminProductTable from '../components/product-verification/AdminProductTable.vue'

const toast = useToast()

const isLoading = ref(true)
const isError = ref(false)
const errorMessage = ref('')
const activeTab = ref('pending')
const products = ref<Partial<Product>[]>([])
const selectedProduct = ref<Partial<Product> | null>(null)

const showDetailModal = ref(false)
const showApproveModal = ref(false)
const showRejectModal = ref(false)

const pendingCount = computed(() => products.value.filter((p) => p.status === 'pending').length)
const filteredProducts = computed(() => products.value.filter((p) => p.status === activeTab.value))

const fetchProducts = async () => {
  isLoading.value = true
  isError.value = false
  errorMessage.value = ''

  try {
    const response = await adminProductService.listPending({ per_page: 100 })
    products.value = response.data
  } catch (error) {
    isError.value = true
    errorMessage.value = getApiErrorMessage(error, 'Gagal memuat data verifikasi produk.')
  } finally {
    isLoading.value = false
  }
}

const openDetail = (product: Partial<Product>) => {
  selectedProduct.value = product
  showDetailModal.value = true
}

const openApprove = (product: Partial<Product>) => {
  selectedProduct.value = product
  showApproveModal.value = true
}

const openReject = (product: Partial<Product>) => {
  selectedProduct.value = product
  showRejectModal.value = true
}

const executeApprove = async () => {
  if (!selectedProduct.value?.id) return

  try {
    const approvedProduct = await adminProductService.approve(selectedProduct.value.id)
    products.value = products.value.map((product) =>
      product.id === approvedProduct.id ? approvedProduct : product,
    )

    showApproveModal.value = false
    showDetailModal.value = false
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Produk disetujui untuk ditayangkan.', life: 3000 })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal menyetujui produk.'),
      life: 3000,
    })
  }
}

const executeReject = async (payload: { reason: string }) => {
  if (!selectedProduct.value?.id) return

  try {
    const rejectedProduct = await adminProductService.reject(selectedProduct.value.id, {
      rejection_reason: payload.reason,
    })

    products.value = products.value.map((product) =>
      product.id === rejectedProduct.id ? rejectedProduct : product,
    )

    showRejectModal.value = false
    showDetailModal.value = false
    toast.add({ severity: 'warn', summary: 'Ditolak', detail: 'Pengajuan produk telah ditolak.', life: 3000 })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal menolak produk.'),
      life: 3000,
    })
  }
}

onMounted(fetchProducts)
</script>

<template>
  <div class="relative min-h-[80vh]">

    <div v-if="isLoading"
      class="fixed inset-0 z-100 flex flex-col items-center justify-center bg-white/90 backdrop-blur-xs transition-all duration-300">
      <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
      <span class="mt-4 text-sm font-semibold text-slate-600 animate-pulse">Memuat pengajuan produk...</span>
    </div>

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Verifikasi Produk</h1>
      <p class="text-sm text-slate-500 mt-1">Tinjau dan verifikasi produk yang diajukan oleh seller</p>
    </div>

    <Message v-if="isError" severity="error" class="mb-4">{{ errorMessage }}</Message>

    <AdminProductFilter v-model:activeTab="activeTab" :pendingCount="pendingCount" />

    <AdminProductTable :products="filteredProducts" @viewDetail="openDetail" @approve="openApprove" @reject="openReject" />

    <AdminProductDetailModal v-model:visible="showDetailModal" :product="selectedProduct"
      @approve="openApprove(selectedProduct!)" @reject="openReject(selectedProduct!)" />

    <AdminProductApproveModal v-model:visible="showApproveModal" :product="selectedProduct" @confirm="executeApprove" />

    <AdminProductRejectModal v-model:visible="showRejectModal" :product="selectedProduct" @confirm="executeReject" />
  </div>
</template>
