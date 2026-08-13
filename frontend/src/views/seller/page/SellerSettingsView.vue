<script setup lang="ts">
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
import ToggleSwitch from 'primevue/toggleswitch'
import { useToast } from 'primevue/usetoast'
import { onMounted, ref } from 'vue'

const toast = useToast()

// State Loading Fullscreen 1 Halaman (GET Data)
const isLoadingGet = ref(true)

// State Loading Simpan Perubahan (POST Data)
const isSubmittingPassword = ref(false)
const isSubmittingPreferences = ref(false)

// State Modal / Dialog Konfirmasi Zona Berbahaya
const showDeactivateDialog = ref(false)
const showDeleteAccountDialog = ref(false)

// Form Security / Keamanan Akun
const securityForm = ref({
  email: 'seller@kabita.id',
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Form Preferensi Toko
const preferenceForm = ref({
  timeZone: 'WIB',
  minStockAlert: 5,
  isVacationMode: false
})

// Data Status Akun Toko (Read-only)
const accountStatus = ref({
  isVerified: true,
  role: 'Admin Utama',
  memberId: 'KB-98234-A',
  joinedDate: '12 Jan 2023',
  lastActive: 'Hari ini, 09:41'
})

const timeZoneOptions = [
  { label: 'Waktu Indonesia Barat (WIB)', value: 'WIB' },
  { label: 'Waktu Indonesia Tengah (WITA)', value: 'WITA' },
  { label: 'Waktu Indonesia Timur (WIT)', value: 'WIT' }
]

// ----------------------------------------------------------------
// 1. SIMULASI GET DATA SETTINGS (Full Screen Circular Loader)
// ----------------------------------------------------------------
const fetchSettingsData = () => {
  isLoadingGet.value = true
  setTimeout(() => {
    // Data terisi dari API
    securityForm.value.email = 'seller@kabita.id'
    preferenceForm.value = {
      timeZone: 'WIB',
      minStockAlert: 5,
      isVacationMode: false
    }
    isLoadingGet.value = false
  }, 1000)
}

onMounted(() => {
  fetchSettingsData()
})

// ----------------------------------------------------------------
// 2. HANDLER UPDATE PASSWORD
// ----------------------------------------------------------------
const handleUpdatePassword = () => {
  if (!securityForm.value.oldPassword || !securityForm.value.newPassword) {
    toast.add({
      severity: 'warn',
      summary: 'Peringatan',
      detail: 'Password lama dan password baru wajib diisi!',
      life: 3000
    })
    return
  }

  if (securityForm.value.newPassword !== securityForm.value.confirmPassword) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: 'Konfirmasi password baru tidak cocok!',
      life: 3000
    })
    return
  }

  isSubmittingPassword.value = true
  setTimeout(() => {
    isSubmittingPassword.value = false
    securityForm.value.oldPassword = ''
    securityForm.value.newPassword = ''
    securityForm.value.confirmPassword = ''

    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Password akun Anda berhasil diperbarui!',
      life: 3000
    })
  }, 1200)
}

// ----------------------------------------------------------------
// 3. HANDLER SIMPAN PREFERENSI TOKO
// ----------------------------------------------------------------
const handleSavePreferences = () => {
  isSubmittingPreferences.value = true
  setTimeout(() => {
    isSubmittingPreferences.value = false
    toast.add({
      severity: 'success',
      summary: 'Berhasil Disimpan',
      detail: 'Preferensi toko Anda berhasil diperbarui.',
      life: 3000
    })
  }, 1000)
}

const handleResetPreferences = () => {
  fetchSettingsData()
  toast.add({
    severity: 'info',
    summary: 'Di-reset',
    detail: 'Pengaturan dikembalikan ke nilai awal.',
    life: 2500
  })
}

// ----------------------------------------------------------------
// 4. HANDLER ZONA BERBAHAYA (MODAL ACTIONS)
// ----------------------------------------------------------------
const confirmDeactivate = () => {
  showDeactivateDialog.value = false
  toast.add({
    severity: 'warn',
    summary: 'Toko Dinonaktifkan',
    detail: 'Toko Anda telah ditutup sementara.',
    life: 3500
  })
}

const confirmDeleteAccount = () => {
  showDeleteAccountDialog.value = false
  toast.add({
    severity: 'error',
    summary: 'Akun Dihapus',
    detail: 'Proses penghapusan akun sedang diproses sistem.',
    life: 3500
  })
}
</script>

<template>
  <div class="relative min-h-[80vh]">
    <Transition name="fade">
      <div v-if="isLoadingGet"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
        <ProgressSpinner style="width: 56px; height: 56px" strokeWidth="4" fill="transparent" animationDuration=".8s" />
        <p class="mt-4 font-medium text-slate-600 text-sm animate-pulse">
          Memuat Pengaturan Toko...
        </p>
      </div>
    </Transition>

    <div v-if="!isLoadingGet" class="max-w-6xl mx-auto space-y-6 pb-12">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Pengaturan Toko</h1>
          <p class="text-xs text-slate-500 mt-1">
            Kelola preferensi sistem, keamanan akun, dan konfigurasi toko Anda.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-8 space-y-6">

          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
              <i class="pi pi-lock text-blue-600"></i> Keamanan Akun
            </h2>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Saat Ini</label>
              <InputText v-model="securityForm.email" disabled
                class="w-full! rounded-xl! text-sm! py-2.5! bg-slate-50/80! text-slate-500!" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password Lama</label>
              <InputText v-model="securityForm.oldPassword" type="password" placeholder="••••••••"
                class="w-full! rounded-xl! text-sm! py-2.5!" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password Baru</label>
                <InputText v-model="securityForm.newPassword" type="password" placeholder="••••••••"
                  class="w-full! rounded-xl! text-sm! py-2.5!" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                <InputText v-model="securityForm.confirmPassword" type="password" placeholder="••••••••"
                  class="w-full! rounded-xl! text-sm! py-2.5!" />
              </div>
            </div>

            <div class="pt-2">
              <Button label="Update Password" :loading="isSubmittingPassword"
                class="bg-blue-600! hover:bg-blue-700! border-blue-600! rounded-xl! text-xs! font-semibold! px-5! py-2.5!"
                @click="handleUpdatePassword" />
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
            <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
              <i class="pi pi-shop text-blue-600"></i> Preferensi Toko
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Zona Waktu</label>
                <Dropdown v-model="preferenceForm.timeZone" :options="timeZoneOptions" optionLabel="label"
                  optionValue="value" class="w-full! rounded-xl! text-sm! bg-slate-50/50!" />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Minimal Stok Peringatan</label>
                <InputNumber v-model="preferenceForm.minStockAlert" :min="1" class="w-full!"
                  inputClass="!w-full !rounded-xl !text-sm !py-2.5 !bg-slate-50/50" />
              </div>
            </div>

            <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/60 flex items-center justify-between">
              <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                  <i class="pi pi-send text-slate-500 text-xs"></i>
                  <span class="text-sm font-bold text-slate-800">Mode Liburan</span>
                </div>
                <p class="text-xs text-slate-500">
                  Tutup toko sementara. Pembeli tidak dapat membuat pesanan baru.
                </p>
              </div>

              <ToggleSwitch v-model="preferenceForm.isVacationMode" />
            </div>
          </div>

        </div>

        <div class="lg:col-span-4 space-y-6">

          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h2 class="font-bold text-slate-800 text-base">Status Akun</h2>
              <Tag severity="success" value="TERVERIFIKASI" class="text-[10px]! px-2.5! py-1! rounded-md!" />
            </div>

            <div class="space-y-2.5 text-xs">
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-400">Role:</span>
                <span class="font-bold text-slate-800">{{ accountStatus.role }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-400">Member ID:</span>
                <span class="font-bold font-mono text-slate-800">{{ accountStatus.memberId }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-400">Bergabung:</span>
                <span class="font-semibold text-slate-700">{{ accountStatus.joinedDate }}</span>
              </div>
              <div class="flex justify-between py-1">
                <span class="text-slate-400">Aktivitas Terakhir:</span>
                <span class="font-semibold text-slate-700">{{ accountStatus.lastActive }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 border border-red-100 shadow-sm space-y-4">
            <h2 class="font-bold text-red-600 text-base flex items-center gap-2">
              <i class="pi pi-exclamation-triangle"></i> Zona Berbahaya
            </h2>
            <p class="text-xs text-slate-500 leading-relaxed">
              Tindakan di bawah ini dapat mempengaruhi operasional toko Anda secara signifikan.
            </p>

            <div class="space-y-2.5 pt-1">
              <Button label="Nonaktifkan Toko Sementara" severity="danger" outlined
                class="w-full rounded-xl! text-xs! py-2.5! border-red-300! hover:bg-red-50!"
                @click="showDeactivateDialog = true" />
              <Button label="Hapus Akun Permanen" severity="danger"
                class="w-full rounded-xl! text-xs! py-2.5! bg-red-600! hover:bg-red-700! border-red-600!"
                @click="showDeleteAccountDialog = true" />
            </div>
          </div>

          <div class="space-y-2.5">
            <Button label="Simpan Semua Perubahan" icon="pi pi-check" :loading="isSubmittingPreferences"
              class="w-full bg-blue-600! hover:bg-blue-700! border-blue-600! rounded-xl! text-sm! font-semibold! py-3! shadow-md shadow-blue-500/20"
              @click="handleSavePreferences" />
            <Button label="Reset ke Default" severity="secondary"
              class="w-full bg-slate-100! hover:bg-slate-200! text-slate-700! border-none! rounded-xl! text-xs! font-semibold! py-2.5!"
              @click="handleResetPreferences" />
          </div>

        </div>

      </div>
    </div>

    <Dialog v-model:visible="showDeactivateDialog" modal header="Nonaktifkan Toko Sementara" :style="{ width: '400px' }"
      class="rounded-2xl!">
      <div class="space-y-3 py-2">
        <p class="text-xs text-slate-600">
          Apakah Anda yakin ingin menonaktifkan toko? Produk Anda tidak akan tampil di pencarian pembeli sampai Anda
          mengaktifkannya kembali.
        </p>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Batal" severity="secondary" text class="text-xs! rounded-xl!"
            @click="showDeactivateDialog = false" />
          <Button label="Ya, Nonaktifkan" severity="danger" class="text-xs! rounded-xl! bg-red-600!"
            @click="confirmDeactivate" />
        </div>
      </template>
    </Dialog>

    <Dialog v-model:visible="showDeleteAccountDialog" modal header="Hapus Akun Permanen" :style="{ width: '400px' }"
      class="rounded-2xl!">
      <div class="space-y-3 py-2">
        <p class="text-xs text-slate-600">
          Tindakan ini <strong class="text-red-600">TIDAK DAPAT DIBATALKAN</strong>. Seluruh riwayat toko, produk, dan
          saldo
          terendap akan dihapus permanen.
        </p>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Batal" severity="secondary" text class="text-xs! rounded-xl!"
            @click="showDeleteAccountDialog = false" />
          <Button label="Hapus Permanen" severity="danger" class="text-xs! rounded-xl! bg-red-700!"
            @click="confirmDeleteAccount" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>