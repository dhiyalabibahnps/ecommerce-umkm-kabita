<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref } from 'vue'

import AdminStoreApproveModal from '../components/store-verification/AdminStoreApproveModal.vue'
import AdminStoreDetailModal from '../components/store-verification/AdminStoreDetailModal.vue'
import AdminStoreFilter from '../components/store-verification/AdminStoreFilter.vue'
import AdminStoreRejectModal from '../components/store-verification/AdminStoreRejectModal.vue'
import AdminStoreTable from '../components/store-verification/AdminStoreTable.vue'

import type { Shop } from '@/types/entities'

const toast = useToast()

// States
const isLoading = ref(true)
const activeTab = ref('pending')
const selectedShop = ref<Partial<Shop> | null>(null)

// Modal Visibilities
const showDetailModal = ref(false)
const showApproveModal = ref(false)
const showRejectModal = ref(false)

// Mock Data
const mockShops = ref<Partial<Shop>[]>([
  {
    id: 101,
    name: 'Toko Roti Makmur',
    status: 'pending',
    logo: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=150',
    description: 'Toko Roti Makmur adalah usaha mikro yang bergerak di bidang pembuatan roti tawar dan roti manis dengan resep tradisional keluarga. Kapasitas produksi harian mencapai 500 pcs.',
    seller: { id: 1, name: 'Budi Santoso', email: 'budi.makmur@example.com', phone: '+62 812 3456 7890', address: 'Jl. Sudirman No. 45, Komplek Ruko Sentra Bisnis Blok B2, Kelurahan Melawai, Kecamatan Kebayoran Baru, Jakarta Selatan, 12160', role: 'seller', status: 'active' } as any
  },
  {
    id: 102,
    name: 'Maju Jaya Elektronik',
    status: 'pending',
    logo: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&q=80&w=150',
    description: 'Distributor alat elektronik rumah tangga.',
    seller: { id: 2, name: 'Agus Wijaya', email: 'agus@example.com', phone: '+62 899 7777 8888', role: 'seller', status: 'active' } as any
  },
  {
    id: 103,
    name: 'Cantika Fashion',
    status: 'pending',
    logo: 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?auto=format&fit=crop&q=80&w=150',
    description: 'Menjual aneka pakaian wanita masa kini.',
    seller: { id: 3, name: 'Siti Aminah', email: 'siti@example.com', phone: '+62 811 2222 3333', role: 'seller', status: 'active' } as any
  }
])

const pendingCount = computed(() => mockShops.value.filter(s => s.status === 'pending').length)
const filteredShops = computed(() => mockShops.value.filter(s => s.status === activeTab.value))

// Handlers
const openDetail = (shop: Partial<Shop>) => {
  selectedShop.value = shop
  showDetailModal.value = true
}

const openApprove = (shop: Partial<Shop>) => {
  selectedShop.value = shop
  showApproveModal.value = true
}

const openReject = (shop: Partial<Shop>) => {
  selectedShop.value = shop
  showRejectModal.value = true
}

// Action Mocks
const executeApprove = () => {
  if (selectedShop.value) {
    const shopIndex = mockShops.value.findIndex(s => s.id === selectedShop.value?.id)
    if (shopIndex !== -1) {
      const shop = mockShops.value[shopIndex]
      if (shop) shop.status = 'verified'
    }
  }
  showApproveModal.value = false
  showDetailModal.value = false
  toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Verifikasi toko disetujui.', life: 3000 })
}

const executeReject = (payload: { reason: string }) => {
  if (selectedShop.value) {
    const shopIndex = mockShops.value.findIndex(s => s.id === selectedShop.value?.id)
    if (shopIndex !== -1) {
      const shop = mockShops.value[shopIndex]
      if (shop) shop.status = 'rejected'
    }
  }
  showRejectModal.value = false
  toast.add({ severity: 'warn', summary: 'Ditolak', detail: 'Toko telah ditolak dengan alasan yang direkam.', life: 3000 })
}

onMounted(() => {
  // Simulasi GET Data dengan Fullscreen Circular Loader
  setTimeout(() => {
    isLoading.value = false
  }, 1200)
})
</script>

<template>
  <div class="relative min-h-[80vh]">

    <div v-if="isLoading"
      class="fixed inset-0 z-100 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm transition-all duration-300">
      <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
      <span class="mt-4 text-sm font-semibold text-slate-600 animate-pulse">Memuat pengajuan toko...</span>
    </div>

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Verifikasi Toko</h1>
      <p class="text-sm text-slate-500 mt-1">Tinjau dan verifikasi toko yang terdaftar di platform</p>
    </div>

    <AdminStoreFilter v-model:activeTab="activeTab" :pendingCount="pendingCount" />

    <AdminStoreTable :shops="filteredShops" @viewDetail="openDetail" @approve="openApprove" @reject="openReject" />

    <AdminStoreDetailModal v-model:visible="showDetailModal" :shop="selectedShop" @approve="executeApprove" />
    <AdminStoreApproveModal v-model:visible="showApproveModal" :shop="selectedShop" @confirm="executeApprove" />
    <AdminStoreRejectModal v-model:visible="showRejectModal" :shop="selectedShop" @confirm="executeReject" />
  </div>
</template>