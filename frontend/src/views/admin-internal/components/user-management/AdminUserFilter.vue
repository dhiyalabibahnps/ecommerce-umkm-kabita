<script setup lang="ts">
import Button from 'primevue/button';

const props = defineProps<{
  activeTab: string
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: string): void
}>()

const tabs = [
  { label: 'Semua', value: 'all' },
  { label: 'Buyer', value: 'buyer' },
  { label: 'Seller', value: 'seller' },
  { label: 'Admin', value: 'admin' }
]
</script>

<template>
  <div
    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm mb-5">
    <div class="flex items-center gap-1.5">
      <button v-for="tab in tabs" :key="tab.value" @click="emit('update:activeTab', tab.value)"
        class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200" :class="props.activeTab === tab.value
          ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
          : 'bg-transparent text-slate-600 hover:bg-slate-100'
          ">
        {{ tab.label }}
      </button>
    </div>

    <div class="flex items-center gap-3">
      <Button outlined class="rounded-full! text-slate-600! border-slate-300! px-4! py-2! text-sm!">
        <span class="mr-2">Status: Semua</span>
        <i class="pi pi-angle-down text-xs"></i>
      </Button>
      <Button outlined class="rounded-full! text-slate-600! border-slate-300! px-4! py-2! text-sm!">
        <span class="mr-2">Role: Semua</span>
        <i class="pi pi-angle-down text-xs"></i>
      </Button>
    </div>
  </div>
</template>