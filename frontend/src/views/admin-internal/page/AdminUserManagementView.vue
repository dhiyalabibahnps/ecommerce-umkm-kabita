<script setup lang="ts">
import { getApiErrorMessage } from '@/services/apiError'
import { adminUserService } from '@/services/adminUserService'
import type { User } from '@/types/entities'
import Button from 'primevue/button'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref, watch } from 'vue'

import AdminUserDetailModal from '../components/user-management/AdminUserDetailModal.vue'
import AdminUserFilter from '../components/user-management/AdminUserFilter.vue'
import AdminUserTable from '../components/user-management/AdminUserTable.vue'

const toast = useToast()

const isLoading = ref(true)
const isError = ref(false)
const errorMessage = ref('')
const activeTab = ref('all')
const users = ref<Partial<User>[]>([])
const isModalVisible = ref(false)
const selectedUser = ref<Partial<User> | null>(null)

const filteredUsers = computed(() => users.value)

const fetchUsers = async () => {
  isLoading.value = true
  isError.value = false
  errorMessage.value = ''

  try {
    const response = await adminUserService.list({
      role: activeTab.value === 'all' ? undefined : activeTab.value,
      per_page: 100,
    })

    users.value = response.data
  } catch (error) {
    isError.value = true
    errorMessage.value = getApiErrorMessage(error, 'Gagal memuat data pengguna.')
  } finally {
    isLoading.value = false
  }
}

const openUserDetail = (user: Partial<User>) => {
  selectedUser.value = user
  isModalVisible.value = true
}

const handleSuspendToggle = async (user: Partial<User>) => {
  if (!user.id) return

  try {
    const updatedUser =
      user.status === 'suspended'
        ? await adminUserService.activate(user.id)
        : await adminUserService.suspend(user.id)

    users.value = users.value.map((existingUser) =>
      existingUser.id === updatedUser.id ? updatedUser : existingUser,
    )

    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: user.status === 'suspended' ? 'Pengguna berhasil diaktifkan.' : 'Pengguna berhasil ditangguhkan.',
      life: 3000,
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal memperbarui status pengguna.'),
      life: 3000,
    })
  }
}

watch(activeTab, fetchUsers)
onMounted(fetchUsers)
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

    <Message v-if="isError" severity="error" class="mb-4">{{ errorMessage }}</Message>

    <AdminUserFilter v-model:activeTab="activeTab" />

    <AdminUserTable :users="filteredUsers" @view="openUserDetail" @edit="openUserDetail" @ban="handleSuspendToggle" />

    <AdminUserDetailModal v-model:visible="isModalVisible" :user="selectedUser" />
  </div>
</template>
