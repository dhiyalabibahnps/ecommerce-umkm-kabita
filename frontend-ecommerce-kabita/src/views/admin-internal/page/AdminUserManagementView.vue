<script setup lang="ts">
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import { computed, onMounted, ref } from 'vue'

import type { User } from '@/types/entities'
import AdminUserDetailModal from '../components/user-management/AdminUserDetailModal.vue'
import AdminUserFilter from '../components/user-management/AdminUserFilter.vue'
import AdminUserTable from '../components/user-management/AdminUserTable.vue'

// State
const isLoading = ref(true)
const activeTab = ref('all')
const isModalVisible = ref(false)
const selectedUser = ref<Partial<User> | null>(null)

// Mock Data Users
const rawUsers = ref<Partial<User>[]>([
  {
    id: 1,
    name: 'Siti Rahmawati',
    email: 'siti.rahma@email.com',
    role: 'buyer',
    phone: '+62 812-3456-7890',
    status: 'active',
    created_at: '2023-10-12T14:30:00Z',
    proof_image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150'
  },
  {
    id: 2,
    name: 'Budi Santoso',
    email: 'budi.toko@email.com',
    role: 'seller',
    phone: '+62 856-1234-5678',
    status: 'active',
    created_at: '2023-09-05T09:15:00Z',
    proof_image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=150'
  },
  {
    id: 3,
    name: 'Agus Wijaya',
    email: 'agus.w@email.com',
    role: 'buyer',
    phone: '+62 899-7777-8888',
    status: 'inactive',
    created_at: '2023-08-20T11:45:00Z',
    proof_image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150'
  },
  {
    id: 4,
    name: 'Dian Anggraini',
    email: 'dian.toko@email.com',
    role: 'seller',
    phone: '+62 822-3333-4444',
    status: 'suspended', // Visual diset sebagai "Pending" mengikuti request
    created_at: new Date().toISOString(),
    proof_image: ''
  }
])

// Filter Logic
const filteredUsers = computed(() => {
  if (activeTab.value === 'all') return rawUsers.value
  return rawUsers.value.filter(u => u.role === activeTab.value)
})

// Handlers
const openUserDetail = (user: Partial<User>) => {
  selectedUser.value = user
  isModalVisible.value = true
}

const mockFetchData = () => {
  isLoading.value = true
  // Simulasi pemanggilan API
  setTimeout(() => {
    isLoading.value = false
  }, 1200)
}

onMounted(() => {
  mockFetchData()
})
</script>

<template>
  <div class="relative min-h-[80vh]">
    <div v-if="isLoading"
      class="fixed inset-0 z-100 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm transition-all duration-300">
      <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
      <span class="mt-4 text-sm font-semibold text-slate-600 animate-pulse">Memuat data pengguna...</span>
    </div>

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Manajemen User</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola semua pengguna platform Kabita</p>
      </div>
      <Button label="Export Data" icon="pi pi-download" outlined
        class="text-blue-600! border-blue-300! hover:bg-blue-50! rounded-xl! px-4!" />
    </div>

    <AdminUserFilter v-model:activeTab="activeTab" />

    <AdminUserTable :users="filteredUsers" @view="openUserDetail" />

    <AdminUserDetailModal v-model:visible="isModalVisible" :user="selectedUser" />
  </div>
</template>