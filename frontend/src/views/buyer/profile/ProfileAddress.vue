<script setup lang="ts">
import { locationService } from '@/services/locationService'
import type { CodLocation } from '@/types'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import { useToast } from 'primevue/usetoast'
import { onMounted, ref } from 'vue'
import AddressFormModal from './AddressFormModal.vue'

const toast = useToast()

const addresses = ref<CodLocation[]>([])
const loading = ref(false)
const error = ref('')
const showForm = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const selectedLocation = ref<CodLocation | null>(null)
const deleteTarget = ref<CodLocation | null>(null)
const deleteDialogVisible = ref(false)
const settingPrimaryId = ref<number | null>(null)

const loadAddresses = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await locationService.list()
    addresses.value = response.data ?? []
  } catch {
    error.value = 'Gagal memuat alamat.'
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.value, life: 3000 })
  } finally {
    loading.value = false
  }
}

const openAdd = () => {
  formMode.value = 'add'
  selectedLocation.value = null
  showForm.value = true
}

const openEdit = (location: CodLocation) => {
  formMode.value = 'edit'
  selectedLocation.value = location
  showForm.value = true
}

const confirmDelete = (location: CodLocation) => {
  deleteTarget.value = location
  deleteDialogVisible.value = true
}

const handleDelete = async () => {
  const target = deleteTarget.value
  if (!target?.id) return
  deleteDialogVisible.value = false
  try {
    await locationService.delete(target.id)
    addresses.value = addresses.value.filter((item) => item.id !== target.id)
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Alamat berhasil dihapus.', life: 3000 })
  } catch {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menghapus alamat.', life: 3000 })
  } finally {
    deleteTarget.value = null
  }
}

const handleSaved = () => {
  loadAddresses()
}

const setPrimary = async (id: number) => {
  const target = addresses.value.find((item) => item.id === id)
  if (!target) return
  settingPrimaryId.value = id
  try {
    const response = await locationService.setDefault(id)
    addresses.value.forEach((item) => {
      item.is_default = item.id === id
    })
    toast.add({ severity: 'success', summary: 'Berhasil', detail: response.message ?? 'Alamat utama diperbarui.', life: 3000 })
  } catch (error: unknown) {
    const message = error instanceof Error ? error.message : 'Gagal mengatur alamat utama.'
    toast.add({ severity: 'error', summary: 'Gagal', detail: message, life: 3000 })
  } finally {
    settingPrimaryId.value = null
  }
}

onMounted(loadAddresses)
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8 border border-slate-100">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-100 gap-4">
      <div>
        <h1 class="text-lg font-bold text-slate-800">Alamat Saya</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola alamat pengiriman untuk mempermudah transaksi belanja Anda.</p>
      </div>
      <Button label="Tambah Alamat Baru" icon="pi pi-plus"
        class="bg-blue-600! border-blue-600! text-xs! px-4! py-2!.5 rounded-xl!" @click="openAdd" />
    </div>

    <div v-if="loading" class="py-10 text-center text-xs text-slate-500">Memuat alamat...</div>
    <div v-else-if="error" class="py-10 text-center text-xs text-rose-600">{{ error }}</div>

    <div v-else-if="addresses.length" class="space-y-4">
      <div v-for="address in addresses" :key="address.id" :class="[
        'p-5 rounded-xl border transition-colors space-y-3',
        address.is_default ? 'border-blue-500 bg-blue-50/20' : 'border-slate-100 hover:border-slate-200'
      ]">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-800">{{ address.name }}</span>
            <Tag v-if="address.is_default" value="Utama" severity="info" class="text-![8px] px-2! py-0!.5" />
          </div>
          <div class="flex items-center gap-2 text-xs">
            <button class="text-blue-600 hover:underline font-medium" @click="openEdit(address)">Ubah</button>
            <span class="text-slate-300">|</span>
            <button class="text-rose-600 hover:underline font-medium" @click="confirmDelete(address)">Hapus</button>
          </div>
        </div>

        <div>
          <p class="text-xs font-semibold text-slate-700">
            {{ address.name }} <span class="text-slate-400 font-normal">({{ address.phone }})</span>
          </p>
          <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ address.address }}</p>
        </div>

        <div v-if="!address.is_default" class="pt-2">
          <Button label="Atur Sebagai Utama" severity="secondary" outlined class="text-xs! px-3! py-1!.5 rounded-lg!"
            :loading="settingPrimaryId === address.id" @click="setPrimary(address.id)" />
        </div>
      </div>
    </div>

    <div v-else class="py-10 text-center text-xs text-slate-500">Belum ada alamat pengiriman.</div>
  </div>

  <AddressFormModal v-model:visible="showForm" :mode="formMode" :location="selectedLocation" @saved="handleSaved" />
  <Dialog v-model:visible="deleteDialogVisible" modal header="Hapus Alamat" :style="{ width: '90%', maxWidth: '420px' }"
    class="rounded-2xl">
    <div class="text-xs text-slate-600">
      Apakah Anda yakin ingin menghapus alamat <strong>{{ deleteTarget?.name }}</strong>? Tindakan ini tidak dapat
      dibatalkan.
    </div>
    <template #footer>
      <div class="flex gap-2 pt-2">
        <Button label="Batal" severity="secondary" outlined class="flex-1 text-xs!"
          @click="deleteDialogVisible = false" />
        <Button label="Hapus" severity="danger" class="flex-1 text-xs!" autofocus @click="handleDelete" />
      </div>
    </template>
  </Dialog>
</template>
