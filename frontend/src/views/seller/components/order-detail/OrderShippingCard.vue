<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { formatCourierDisplay } from '@/constants/courier';
import type { OrderStatus } from '@/types/enums';

const props = defineProps<{
  shippingMethod: string;
  courier?: string | null;
  trackingNumber?: string | null;
  shippingAddress: string;
  status?: OrderStatus | string;
}>();

const toast = useToast();
const copied = ref(false);

const copyTrackingNumber = () => {
  if (!props.trackingNumber) return;
  navigator.clipboard.writeText(props.trackingNumber);
  copied.value = true;
  toast.add({
    severity: 'success',
    summary: 'Tersalin',
    detail: 'Nomor resi berhasil disalin ke clipboard.',
    life: 2500,
  });
  setTimeout(() => {
    copied.value = false;
  }, 2000);
};
</script>

<template>
  <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs mb-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
      <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
        {{ shippingMethod === 'cod' ? 'Informasi Pengiriman (COD)' : 'Informasi Pengiriman' }}
      </h3>
      <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-semibold">
        {{ shippingMethod === 'cod' ? 'COD' : 'Ekspedisi' }}
      </span>
    </div>

    <div class="space-y-3 text-xs">
      <div>
        <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Layanan Kurir</span>
        <p class="mt-0.5 font-bold text-slate-800">
          {{ shippingMethod === 'cod' ? 'Cash On Delivery (Ketemuan)' : formatCourierDisplay(courier) }}
        </p>
      </div>

      <div v-if="shippingMethod !== 'cod'">
        <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Nomor Resi</span>
        <div v-if="trackingNumber" class="mt-1 flex items-center gap-2">
          <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200/80 px-2.5 py-1 rounded-lg">
            {{ trackingNumber }}
          </span>
          <Button
            :icon="copied ? 'pi pi-check' : 'pi pi-copy'"
            :label="copied ? 'Tersalin' : 'Salin Resi'"
            size="small"
            severity="secondary"
            outlined
            class="text-xs! py-1! px-2.5! rounded-lg!"
            @click="copyTrackingNumber"
          />
        </div>
        <div v-else class="mt-1">
          <span v-if="status === 'shipped' || status === 'completed'" class="text-xs text-slate-600 font-mono">
            Resi diproses
          </span>
          <span v-else class="text-xs text-slate-400 italic">
            Belum diinput (diisi saat kirim pesanan)
          </span>
        </div>
      </div>

      <div v-if="shippingMethod === 'cod'" class="mt-2">
        <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider mb-1">Lokasi Titik Temu</span>
        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-100 flex items-start gap-2">
          <i class="pi pi-map-marker text-rose-500 text-sm mt-0.5"></i>
          <p class="text-xs text-slate-700 leading-relaxed">{{ shippingAddress }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
