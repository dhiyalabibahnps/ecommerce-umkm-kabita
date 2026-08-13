<script setup lang="ts">
import type { Product } from '@/types/entities'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref } from 'vue'
import AdminProductApproveModal from '../components/product-verification/AdminProductApproveModal.vue'
import AdminProductDetailModal from '../components/product-verification/AdminProductDetailModal.vue'
import AdminProductFilter from '../components/product-verification/AdminProductFilter.vue'
import AdminProductRejectModal from '../components/product-verification/AdminProductRejectModal.vue'
import AdminProductTable from '../components/product-verification/AdminProductTable.vue'

const toast = useToast()

// States
const isLoading = ref(true)
const activeTab = ref('pending')
const selectedProduct = ref<Partial<Product> | null>(null)

// Modal Visibilities
const showDetailModal = ref(false)
const showApproveModal = ref(false)
const showRejectModal = ref(false)

// Mock Data Produk
const mockProducts = ref<Partial<Product>[]>([
  {
    id: 201,
    name: 'Kemeja Batik Pria Motif Megamendung',
    price: '150000',
    stock: 45,
    weight: 250,
    status: 'pending',
    description: 'Kemeja batik pria lengan panjang dengan bahan katun premium yang adem dan nyaman dipakai harian maupun acara formal.',
    category: { id: 1, name: 'Pakaian Pria', slug: 'pakaian-pria' } as any,
    shop: { id: 10, name: 'Batik Kencana', seller: { name: 'Budi Santoso', email: 'budi@batikkencana.com', phone: '+62 812 3456 7890' } } as any,
    images: [{ id: 1, url: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&q=80&w=400' }]
  },
  {
    id: 202,
    name: 'Biji Kopi Arabika Toraja 1kg',
    price: '220000',
    stock: 120,
    weight: 1000,
    status: 'pending',
    description: 'Biji kopi pilihan khas Toraja dengan roasted profile medium-to-dark. Aroma harum dan cita rasa otentik.',
    category: { id: 2, name: 'Makanan & Minuman', slug: 'makanan-minuman' } as any,
    shop: { id: 11, name: 'Kopi Senja', seller: { name: 'Dewi Lestari', email: 'dewi@kopisenja.com', phone: '+62 811 9988 7766' } } as any,
    images: [{ id: 2, url: 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&q=80&w=400' }]
  },
  {
    id: 203,
    name: 'Set Pisau Dapur Dapur Stainless',
    price: '85000',
    stock: 30,
    weight: 600,
    status: 'pending',
    description: 'Set pisau dapur isi 5 pcs tajam dan anti karat. Dilengkapi dengan telenan plastik antislip.',
    category: { id: 3, name: 'Perlengkapan Rumah', slug: 'perlengkapan-rumah' } as any,
    shop: { id: 12, name: 'Makmur Nusantara', seller: { name: 'Agus Wijaya', email: 'agus@makmur.com', phone: '+62 856 1122 3344' } } as any,
    images: [{ id: 3, url: 'https://images.unsplash.com/photo-1593618998160-e34014e67546?auto=format&fit=crop&q=80&w=400' }]
  },
  {
    id: 204,
    name: 'Pupuk Organik Cair 500ml',
    price: '45000',
    stock: 200,
    weight: 550,
    status: 'pending',
    description: 'Pupuk organik cair penyubur tanaman hias dan sayur-sayuran.',
    category: { id: 4, name: 'Pertanian', slug: 'pertanian' } as any,
    shop: { id: 13, name: 'Tani Subur', seller: { name: 'Rian Pratama', email: 'rian@tanisubur.com', phone: '+62 822 5544 3322' } } as any,
    images: [{ id: 4, url: 'https://images.unsplash.com/photo-1585314062340-f1a5a7c9328d?auto=format&fit=crop&q=80&w=400' }]
  }
])

const pendingCount = computed(() => mockProducts.value.filter((p) => p.status === 'pending').length)
const filteredProducts = computed(() => mockProducts.value.filter((p) => p.status === activeTab.value))

// Handlers Modal
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

// Action Handlers
const executeApprove = () => {
  if (selectedProduct.value?.id) {
    const pIndex = mockProducts.value.findIndex((p) => p.id === selectedProduct.value?.id)
    if (pIndex !== -1 && mockProducts.value[pIndex]) {
      mockProducts.value[pIndex].status = 'approved'
    }
  }
  showApproveModal.value = false
  showDetailModal.value = false
  toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Produk disetujui untuk ditayangkan.', life: 3000 })
}

const executeReject = (payload: { reason: string }) => {
  if (selectedProduct.value?.id) {
    const pIndex = mockProducts.value.findIndex((p) => p.id === selectedProduct.value?.id)
    if (pIndex !== -1 && mockProducts.value[pIndex]) {
      mockProducts.value[pIndex].status = 'rejected'
      mockProducts.value[pIndex].rejection_reason = payload.reason
    }
  }
  showRejectModal.value = false
  showDetailModal.value = false
  toast.add({ severity: 'warn', summary: 'Ditolak', detail: 'Pengajuan produk telah ditolak.', life: 3000 })
}

onMounted(() => {
  // Simulasi fetching API dengan Fullscreen Circular Spinner
  setTimeout(() => {
    isLoading.value = false
  }, 1000)
})
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

    <AdminProductFilter v-model:activeTab="activeTab" :pendingCount="pendingCount" />

    <AdminProductTable :products="filteredProducts" @viewDetail="openDetail" @approve="openApprove"
      @reject="openReject" />

    <AdminProductDetailModal v-model:visible="showDetailModal" :product="selectedProduct"
      @approve="openApprove(selectedProduct!)" @reject="openReject(selectedProduct!)" />

    <AdminProductApproveModal v-model:visible="showApproveModal" :product="selectedProduct" @confirm="executeApprove" />

    <AdminProductRejectModal v-model:visible="showRejectModal" :product="selectedProduct" @confirm="executeReject" />
  </div>
</template>