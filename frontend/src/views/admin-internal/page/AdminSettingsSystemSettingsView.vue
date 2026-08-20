<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner'
import { onMounted, ref } from 'vue'
import AdminSettingsManagementCard from '../components/settings/AdminSettingsManagementCard.vue'
import AdminSettingsPaymentSettingsCard from '../components/settings/AdminSettingsPaymentSettingsCard.vue'
import AdminSettingsShippingSettingsCard from '../components/settings/AdminSettingsShippingSettingsCard.vue'
// import AdminPaymentSettingsCard from './components/AdminPaymentSettingsCard.vue'
// import AdminShippingSettingsCard from './components/AdminShippingSettingsCard.vue'
// import AdminManagementCard from './components/AdminManagementCard.vue'

// State Fullscreen Circular Progress Spinner (No Shimmer)
const isLoading = ref(true)

onMounted(() => {
  // Simulasi penarikan data konfigurasi awal via API
  setTimeout(() => {
    isLoading.value = false
  }, 700)
})
</script>

<template>
  <div class="p-6 max-w-2xl lg:max-w-5xl xl:max-w-7xl mx-auto relative min-h-screen">

    <Transition name="fade">
      <div v-if="isLoading"
        class="fixed inset-0 z-50 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center">
        <ProgressSpinner style="width: 55px; height: 55px" strokeWidth="4" animationDuration=".8s"
          aria-label="Loading System Settings" />
        <p class="mt-3 text-xs font-semibold text-gray-600 tracking-wide">
          Memuat Pengaturan Sistem...
        </p>
      </div>
    </Transition>

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pengaturan Sistem</h1>
      <p class="text-xs text-gray-500 mt-1">Konfigurasi dasar platform Kabita</p>
    </div>

    <hr class="border-gray-200 mb-6" />

    <div v-if="!isLoading">
      <AdminSettingsPaymentSettingsCard />

      <AdminSettingsShippingSettingsCard />

      <AdminSettingsManagementCard />
    </div>
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