<script setup lang="ts">
import type { Shop } from '@/types/entities'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import Textarea from 'primevue/textarea'
import { ref } from 'vue'

defineProps<{
  visible: boolean
  shop: Partial<Shop> | null
}>()

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void
  (e: 'confirm', payload: { reason: string }): void
}>()

const selectedCategory = ref(null)
const rejectionDetail = ref('')

const categories = [
  { label: 'Dokumen Tidak Lengkap', value: 'dokumen_tidak_lengkap' },
  { label: 'Informasi Tidak Valid', value: 'informasi_tidak_valid' },
  { label: 'Indikasi Pelanggaran Kebijakan', value: 'pelanggaran' }
]

const handleReject = () => {
  emit('confirm', { reason: rejectionDetail.value })
  rejectionDetail.value = ''
  selectedCategory.value = null
}
</script>

<template>
  <Dialog :visible="visible" modal :showHeader="false" :style="{ width: '30rem' }"
    @update:visible="emit('update:visible', $event)">
    <div class="bg-white rounded-2xl relative">
      <div class="flex items-center justify-between p-4 border-b border-slate-100">
        <div class="flex items-center gap-2 text-red-600 font-bold text-lg">
          <i class="pi pi-exclamation-triangle"></i>
          <span>Tolak Verifikasi Toko</span>
        </div>
        <button @click="emit('update:visible', false)" class="text-slate-400 hover:text-slate-600"><i
            class="pi pi-times"></i></button>
      </div>

      <div class="p-6 space-y-4">
        <p class="text-xs text-slate-500 leading-relaxed mb-4">
          Anda akan menolak pengajuan verifikasi toko ini. Silakan berikan alasan yang jelas agar pemilik toko dapat
          memperbaiki pengajuannya.
        </p>

        <div>
          <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Kategori Pelanggaran <span
              class="text-red-500">*</span></label>
          <Dropdown v-model="selectedCategory" :options="categories" optionLabel="label"
            placeholder="Pilih kategori penolakan..." class="w-full border-slate-300!" />
        </div>

        <div>
          <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Alasan Penolakan (Detail) <span
              class="text-red-500">*</span></label>
          <Textarea v-model="rejectionDetail" rows="4"
            placeholder="Jelaskan secara spesifik apa yang kurang atau salah dari pengajuan ini..."
            class="w-full border-slate-300! text-sm!" />
        </div>
      </div>

      <div class="p-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
        <Button label="Batal" outlined class="border-slate-300! text-slate-600! px-5! bg-white!"
          @click="emit('update:visible', false)" />
        <Button label="Tolak Sekarang" class="bg-red-600! border-red-600! px-5!" icon="pi pi-ban"
          @click="handleReject" />
      </div>
    </div>
  </Dialog>
</template>