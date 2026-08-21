<script setup lang="ts">
import { locationService } from '@/services/locationService'
import type { CodLocation, StoreCodLocationRequest, UpdateLocationRequest } from '@/types'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import { useToast } from 'primevue/usetoast'
import { computed, ref, watch } from 'vue'

const toast = useToast()

const props = defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
  location?: CodLocation | null
}>()

const emit = defineEmits<{
  'update:visible': [value: boolean]
  saved: []
}>()

const isSubmitting = ref(false)

const form = ref<StoreCodLocationRequest>({
  name: '',
  address: '',
  phone: '',
  latitude: null,
  longitude: null,
  is_default: false,
})

watch(
  () => props.visible,
  (val) => {
    if (!val) return
    if (props.mode === 'edit' && props.location) {
      form.value = {
        name: props.location.name,
        address: props.location.address,
        phone: props.location.phone,
        latitude: props.location.latitude,
        longitude: props.location.longitude,
        is_default: props.location.is_default ?? false,
      }
    } else {
      form.value = {
        name: '',
        address: '',
        phone: '',
        latitude: null,
        longitude: null,
        is_default: false,
      }
    }
  }
)

const title = computed(() => (props.mode === 'add' ? 'Tambah Alamat' : 'Edit Alamat'))

const handleSave = async () => {
  if (!form.value.name || !form.value.phone || !form.value.address) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Nama, telepon, dan alamat wajib diisi.', life: 3000 })
    return
  }

  isSubmitting.value = true
  try {
    if (props.mode === 'add') {
      await locationService.create(form.value)
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Alamat berhasil ditambahkan.', life: 3000 })
    } else if (props.mode === 'edit' && props.location?.id) {
      const payload: UpdateLocationRequest = {
        name: form.value.name,
        address: form.value.address,
        phone: form.value.phone,
        latitude: form.value.latitude,
        longitude: form.value.longitude,
        is_default: form.value.is_default,
      }
      await locationService.update(props.location.id, payload)
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Alamat berhasil diperbarui.', life: 3000 })
    }

    emit('saved')
    emit('update:visible', false)
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Terjadi kesalahan saat menyimpan.', life: 3000 })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Dialog :visible="visible" @update:visible="emit('update:visible', $event)" modal :header="title"
    :style="{ width: '90%', maxWidth: '560px' }" class="rounded-2xl">
    <div class="space-y-4 py-2">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-slate-700">Nama Penerima</label>
        <InputText v-model="form.name" placeholder="mis. Budi Santoso" class="w-full text-xs!" />
      </div>

      <div class="space-y-1">
        <label class="text-xs font-semibold text-slate-700">Nomor Telepon</label>
        <InputText v-model="form.phone" placeholder="mis. 081234567890" class="w-full text-xs!" />
      </div>

      <div class="space-y-1">
        <label class="text-xs font-semibold text-slate-700">Alamat Lengkap</label>
        <Textarea v-model="form.address" rows="3" placeholder="Jalan, No. Rumah, RT/RW, Kecamatan, Kota, Kode Pos"
          class="w-full text-xs!" />
      </div>

      <div class="flex items-center gap-2">
        <Checkbox v-model="form.is_default" :binary="true" inputId="address_is_default" />
        <label for="address_is_default" class="text-xs font-semibold text-slate-700">Jadikan alamat utama</label>
      </div>

      <div class="flex gap-2 pt-2">
        <Button label="Batal" severity="secondary" outlined class="flex-1 text-xs!"
          @click="emit('update:visible', false)" />
        <Button label="Simpan" :loading="isSubmitting" class="flex-1 text-xs!" @click="handleSave" />
      </div>
    </div>
  </Dialog>
</template>
