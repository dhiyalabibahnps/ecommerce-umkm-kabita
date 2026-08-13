<script setup lang="ts">
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import ToggleSwitch from 'primevue/toggleswitch'
import { useToast } from 'primevue/usetoast'
import { ref } from 'vue'

const toast = useToast()
const isSaving = ref(false)

// State Form Pembayaran
const paymentConfig = ref({
  transfer_bank: true,
  cod: true,
  rekening_admin: '1234567890 a.n. Kabita',
  biaya_admin_percent: 1
})

const handleSave = () => {
  isSaving.value = true
  setTimeout(() => {
    isSaving.value = false
    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Pengaturan pembayaran berhasil diperbarui!',
      life: 3000
    })
  }, 600)
}
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
        <i class="pi pi-credit-card text-lg"></i>
      </div>
      <div>
        <h2 class="text-lg font-bold text-gray-900">Pengaturan Pembayaran</h2>
        <p class="text-xs text-gray-500">Metode pembayaran yang tersedia untuk pelanggan.</p>
      </div>
    </div>

    <div class="space-y-3 mb-6">
      <div class="flex items-center justify-between p-3.5 rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center gap-3">
          <i class="pi pi-building text-gray-600"></i>
          <span class="text-sm font-medium text-gray-800">Transfer Bank</span>
        </div>
        <ToggleSwitch v-model="paymentConfig.transfer_bank" />
      </div>

      <div class="flex items-center justify-between p-3.5 rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center gap-3">
          <i class="pi pi-wallet text-gray-600"></i>
          <span class="text-sm font-medium text-gray-800">Cash on Delivery (COD)</span>
        </div>
        <ToggleSwitch v-model="paymentConfig.cod" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Rekening Admin (BCA)</label>
        <InputText v-model="paymentConfig.rekening_admin" placeholder="Nomor & Nama Rekening"
          class="w-full text-sm! py-2!" />
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Biaya Admin Transfer (%)</label>
        <InputNumber v-model="paymentConfig.biaya_admin_percent" suffix=" %" :min="0" :max="100" class="w-full text-sm!"
          inputClass="w-full py-2! text-sm!" />
      </div>
    </div>

    <p class="text-[11px] text-gray-400 mb-6 flex items-center gap-1">
      <i class="pi pi-info-circle text-[11px]"></i>
      Metode pembayaran aktif akan langsung terlihat saat checkout.
    </p>

    <div class="flex justify-end">
      <Button label="Simpan Perubahan" icon="pi pi-check" :loading="isSaving" @click="handleSave"
        class="bg-blue-600! border-blue-600! hover:bg-blue-700! text-xs! px-5! py-2!.5 rounded-lg!" />
    </div>
  </div>
</template>