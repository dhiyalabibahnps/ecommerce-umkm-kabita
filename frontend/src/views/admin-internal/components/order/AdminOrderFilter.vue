<script setup lang="ts">
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const props = defineProps<{
  activeTab: string
  counts: Record<string, number>
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: string): void
}>()

const tabs = [
  { label: 'Semua', value: 'all' },
  { label: 'Pending / Bayar', value: 'pending' },
  { label: 'Diproses', value: 'processing' },
  { label: 'Dikirim', value: 'shipped' },
  { label: 'Selesai', value: 'completed' },
  { label: 'Dibatalkan', value: 'cancelled' }
]
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs mb-6">
    <div class="flex items-center gap-2 sm:gap-6 px-6 border-b border-slate-100 overflow-x-auto">
      <button v-for="tab in tabs" :key="tab.value" @click="emit('update:activeTab', tab.value)"
        class="relative py-4 text-sm font-semibold whitespace-nowrap transition-colors duration-200"
        :class="props.activeTab === tab.value ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800'">
        {{ tab.label }}
        <span v-if="counts[tab.value]" class="ml-1.5 px-2 py-0.5 text-xs rounded-full"
          :class="props.activeTab === tab.value ? 'bg-blue-100 text-blue-700 font-bold' : 'bg-slate-100 text-slate-600'">
          {{ counts[tab.value] }}
        </span>
        <div v-if="props.activeTab === tab.value"
          class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 rounded-t-full"></div>
      </button>
    </div>

    <div class="p-4 flex flex-col lg:flex-row items-center justify-between gap-4 bg-slate-50/50 rounded-b-2xl">
      <div class="relative w-full lg:w-96">
        <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <InputText placeholder="Cari No. Pesanan, Toko, atau Pembeli..."
          class="w-full pl-10! py-2! text-sm! border-slate-300! rounded-xl! focus:ring-2! focus:ring-blue-500!/20" />
      </div>

      <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <Button outlined class="text-slate-600! border-slate-300! rounded-xl! px-3!.5 py-2! text-xs! font-semibold">
          <span class="mr-2">Metode Pembayaran: Semua</span>
          <i class="pi pi-angle-down text-xs"></i>
        </Button>
        <Button outlined class="text-slate-600! border-slate-300! rounded-xl! px-3!.5 py-2! text-xs! font-semibold">
          <span class="mr-2">Pengiriman: Semua</span>
          <i class="pi pi-angle-down text-xs"></i>
        </Button>
        <Button outlined class="text-slate-600! border-slate-300! rounded-xl! px-3!.5 py-2! text-xs! font-semibold">
          <i class="pi pi-calendar mr-2 text-slate-400"></i>
          <span>Tanggal</span>
        </Button>
      </div>
    </div>
  </div>
</template>