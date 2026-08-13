<script setup lang="ts">
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const props = defineProps<{
  activeTab: string
  pendingCount: number
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: string): void
}>()

const tabs = [
  { label: 'Menunggu Verifikasi', value: 'pending', hasBadge: true },
  { label: 'Sudah Diverifikasi', value: 'verified', hasBadge: false },
  { label: 'Ditolak', value: 'rejected', hasBadge: false }
]
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm mb-6">
    <div class="flex items-center gap-6 px-6 border-b border-slate-100">
      <button v-for="tab in tabs" :key="tab.value" @click="emit('update:activeTab', tab.value)"
        class="relative py-4 text-sm font-semibold transition-colors duration-200"
        :class="props.activeTab === tab.value ? 'text-blue-600' : 'text-slate-500 hover:text-slate-700'">
        {{ tab.label }} <span v-if="tab.hasBadge">({{ pendingCount }})</span>
        <div v-if="props.activeTab === tab.value"
          class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 rounded-t-full"></div>
      </button>
    </div>

    <div class="p-4 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 rounded-b-2xl">
      <div class="relative w-full sm:w-80">
        <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText placeholder="Cari nama toko atau seller..."
          class="w-full pl-10! py-2! text-sm! border-slate-300! rounded-xl!" />
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto">
        <Button outlined class="text-slate-600! border-slate-300! rounded-xl! px-4! py-2! text-sm!">
          <span class="mr-2">Tanggal: Semua</span>
          <i class="pi pi-angle-down text-xs"></i>
        </Button>
        <Button outlined class="text-slate-600! border-slate-300! rounded-xl! px-4! py-2! text-sm!">
          <span class="mr-2">Status: Semua</span>
          <i class="pi pi-angle-down text-xs"></i>
        </Button>
      </div>
    </div>
  </div>
</template>