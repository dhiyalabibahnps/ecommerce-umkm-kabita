<script setup lang="ts">
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import { ref } from 'vue';

const emit = defineEmits<{
  (e: 'export-pdf'): void;
  (e: 'export-excel'): void;
  (e: 'date-change', range: Date[]): void;
}>();

const dates = ref<Date[]>([
  new Date(2026, 6, 1),
  new Date(2026, 6, 28)
]);

const handleDateSelect = () => {
  if (dates.value && dates.value.length === 2) {
    emit('date-change', dates.value);
  }
};
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
    <div>
      <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan & Analitik</h1>
      <p class="text-sm text-slate-500 mt-0.5">Analisis performa platform Kabita</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <div class="relative">
        <DatePicker v-model="dates" selectionMode="range" dateFormat="d M yy" placeholder="1 - 28 Jul 2026" class="w-56"
          inputClass="py-2! px-3! text-sm! rounded-lg! border-slate-300!" @update:modelValue="handleDateSelect" />
      </div>

      <Button label="Export PDF" icon="pi pi-file-pdf" severity="secondary" outlined size="small"
        class="rounded-lg! text-sm! font-medium! py-2!" @click="emit('export-pdf')" />

      <Button label="Export Excel" icon="pi pi-file-excel" severity="secondary" outlined size="small"
        class="rounded-lg! text-sm! font-medium! py-2!" @click="emit('export-excel')" />
    </div>
  </div>
</template>