<script setup lang="ts">
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import ToggleSwitch from 'primevue/toggleswitch'
import { useToast } from 'primevue/usetoast'
import { ref } from 'vue'

const toast = useToast()
const isSaving = ref(false)

// State Ekspedisi Kurir & Param Pengiriman
const couriers = ref([
  { id: 'jne', name: 'JNE', active: false },
  { id: 'jnt', name: 'J&T Express', active: true },
  { id: 'sicepat', name: 'SiCepat', active: true },
  { id: 'pos', name: 'POS Indonesia', active: true },
  { id: 'anteraja', name: 'AnterAja', active: false },
  { id: 'grab', name: 'GrabExpress', active: false }
])

const shippingParams = ref({
  max_cod_radius: 10,
  default_shipping_cost: 15000
})

const handleSave = () => {
  isSaving.value = true
  setTimeout(() => {
    isSaving.value = false
    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Pengaturan logistik pengiriman berhasil diperbarui!',
      life: 3000
    })
  }, 600)
}
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
        <i class="pi pi-truck text-lg"></i>
      </div>
      <div>
        <h2 class="text-lg font-bold text-gray-900">Pengaturan Pengiriman</h2>
        <p class="text-xs text-gray-500">Layanan logistik yang didukung oleh platform.</p>
      </div>
    </div>

    <label class="block text-xs font-bold text-gray-800 mb-3">Kurir Tersedia</label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
      <div v-for="courier in couriers" :key="courier.id"
        class="flex items-center justify-between p-3.5 rounded-lg border border-gray-200 bg-white">
        <span class="text-sm font-medium text-gray-800">{{ courier.name }}</span>
        <ToggleSwitch v-model="courier.active" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Radius COD Maksimum</label>
        <div class="p-inputgroup">
          <InputNumber v-model="shippingParams.max_cod_radius" placeholder="10" class="w-full text-sm!"
            inputClass="w-full py-2! text-sm!" />
          <span class="p-inputgroup-addon bg-gray-50! text-gray-500! text-xs!">km</span>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Pembeli hanya bisa pilih COD dalam radius ini dari lokasi toko</p>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Estimasi Biaya Pengiriman Default</label>
        <div class="p-inputgroup">
          <span class="p-inputgroup-addon bg-gray-50! text-gray-500! text-xs!">Rp</span>
          <InputNumber v-model="shippingParams.default_shipping_cost" placeholder="15000" class="w-full text-sm!"
            inputClass="w-full py-2! text-sm!" />
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Estimasi awal sebelum dihitung berdasarkan berat & jarak</p>
      </div>
    </div>

    <div class="flex justify-end">
      <Button label="Simpan Perubahan" icon="pi pi-check" :loading="isSaving" @click="handleSave"
        class="bg-blue-600! border-blue-600! hover:bg-blue-700! text-xs! px-5! py-2!.5 rounded-lg!" />
    </div>
  </div>
</template>