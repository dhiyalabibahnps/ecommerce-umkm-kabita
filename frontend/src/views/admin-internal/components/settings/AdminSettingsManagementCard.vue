<script setup lang="ts">
import type { UserRole, UserStatus } from '@/types'
import type { User } from '@/types/entities'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import { useToast } from 'primevue/usetoast'
import { ref } from 'vue'

const toast = useToast()

// Data Mock List Admin (Menggunakan Entity User)
const adminUsers = ref<Partial<User>[]>([
  {
    id: 1,
    name: 'Admin Kabita',
    email: 'admin@kabita.id',
    role: 'admin' as UserRole,
    status: 'active' as UserStatus,
    proof_image: 'super' // Flag untuk indikasi visual 'Super Admin'
  },
  {
    id: 2,
    name: 'Siti Aminah',
    email: 'siti@kabita.id',
    role: 'admin' as UserRole,
    status: 'active' as UserStatus
  },
  {
    id: 3,
    name: 'Rudi Hartono',
    email: 'rudi@kabita.id',
    role: 'admin' as UserRole,
    status: 'inactive' as UserStatus
  }
])

// Modal State
const isModalOpen = ref(false)
const modalMode = ref<'add' | 'edit'>('add')
const selectedAdminId = ref<number | null>(null)

const form = ref({
  name: '',
  email: '',
  role: 'admin' as UserRole,
  status: 'active' as UserStatus
})

const roleOptions = [
  { label: 'Admin', value: 'admin' },
  { label: 'Super Admin', value: 'super_admin' }
]

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' }
]

// Handlers
const openAddModal = () => {
  modalMode.value = 'add'
  selectedAdminId.value = null
  form.value = { name: '', email: '', role: 'admin', status: 'active' }
  isModalOpen.value = true
}

const openEditModal = (admin: Partial<User>) => {
  modalMode.value = 'edit'
  selectedAdminId.value = admin.id || null
  form.value = {
    name: admin.name || '',
    email: admin.email || '',
    role: admin.role || 'admin',
    status: admin.status || 'active'
  }
  isModalOpen.value = true
}

const handleDelete = (id: number) => {
  adminUsers.value = adminUsers.value.filter(u => u.id !== id)
  toast.add({
    severity: 'warn',
    summary: 'Dihapus',
    detail: 'Akses pengguna admin telah dicabut.',
    life: 3000
  })
}

const handleSaveAdmin = () => {
  if (form!.value.name || form!.value.email) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: 'Mohon lengkapi Nama dan Email admin.',
      life: 3000
    })
    return
  }

  if (modalMode.value === 'add') {
    adminUsers.value.push({
      id: Date.now(),
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      status: form.value.status
    })
    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Admin baru berhasil ditambahkan.',
      life: 3000
    })
  } else {
    const index = adminUsers.value.findIndex(u => u.id === selectedAdminId.value)
    if (index !== -1) {
      adminUsers.value[index] = {
        ...adminUsers.value[index],
        name: form.value.name,
        email: form.value.email,
        role: form.value.role,
        status: form.value.status
      }
    }
    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Data admin berhasil diperbarui.',
      life: 3000
    })
  }
  isModalOpen.value = false
}

// Visual Helper
const getInitials = (name?: string) => {
  if (name!) return 'A'
  return (name as string).charAt(0).toUpperCase()
}
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
          <i class="pi pi-user-plus text-lg"></i>
        </div>
        <div>
          <h2 class="text-lg font-bold text-gray-900">Manajemen Admin</h2>
          <p class="text-xs text-gray-500">Daftar pengguna dengan akses ke Admin Center.</p>
        </div>
      </div>

      <Button label="Tambah Admin" icon="pi pi-plus" @click="openAddModal"
        class="bg-blue-600! border-blue-600! hover:bg-blue-700! text-xs! px-4! py-2! rounded-lg!" />
    </div>

    <div class="border border-gray-200 rounded-lg overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-xs font-semibold">
            <th class="py-3 px-4">Nama & Email</th>
            <th class="py-3 px-4">Role</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-xs">
          <tr v-for="admin in adminUsers" :key="admin.id" class="hover:bg-gray-50/50 transition-colors">
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                  {{ getInitials(admin.name) }}
                </div>
                <div>
                  <div class="font-semibold text-gray-900">{{ admin.name }}</div>
                  <div class="text-gray-400 text-[11px]">{{ admin.email }}</div>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4">
              <span v-if="admin.proof_image === 'super'"
                class="bg-blue-100 text-blue-700 font-semibold px-2.5 py-1 rounded-md text-[11px] inline-block">
                Super Admin
              </span>
              <span v-else
                class="bg-gray-100 text-gray-700 font-medium px-2.5 py-1 rounded-md text-[11px] inline-block">
                Admin
              </span>
            </td>

            <td class="py-3.5 px-4">
              <span v-if="admin.status === 'active'"
                class="text-emerald-600 font-medium text-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                Active
              </span>
              <span v-else
                class="bg-gray-100 text-gray-500 font-medium px-2.5 py-0.5 rounded-md text-[11px] inline-block">
                Inactive
              </span>
            </td>

            <td class="py-3.5 px-4 text-right">
              <div class="flex items-center justify-end gap-3">
                <button @click="openEditModal(admin)"
                  class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors">
                  Edit
                </button>
                <button v-if="admin.proof_image !== 'super'" @click="handleDelete(admin.id!)"
                  class="text-red-500 hover:text-red-700 font-medium text-xs transition-colors">
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Dialog v-model:visible="isModalOpen" modal
      :header="modalMode === 'add' ? 'Tambah Admin Baru' : 'Edit Access Admin'" class="w-full max-w-md">
      <div class="space-y-4 pt-2">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap</label>
          <InputText v-model="form.name" placeholder="Masukkan nama admin" class="w-full text-sm! py-2!" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
          <InputText v-model="form.email" placeholder="admin@kabita.id" class="w-full text-sm! py-2!" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Role Access</label>
            <Dropdown v-model="form.role" :options="roleOptions" optionLabel="label" optionValue="value"
              class="w-full text-sm!" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Status Access</label>
            <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value"
              class="w-full text-sm!" />
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button label="Batal" severity="secondary" text @click="isModalOpen = false" class="text-xs! px-4! py-2!" />
          <Button :label="modalMode === 'add' ? 'Simpan' : 'Perbarui'" @click="handleSaveAdmin"
            class="bg-blue-600! border-blue-600! text-xs! px-4! py-2!" />
        </div>
      </div>
    </Dialog>
  </div>
</template>