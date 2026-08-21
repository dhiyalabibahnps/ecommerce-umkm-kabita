<script setup lang="ts">
import type { OrderStatus } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
  status: OrderStatus;
  notes?: string | null;
  shippingMethod?: string;
}>();

const alertConfig = computed(() => {
  switch (props.status) {
    case 'awaiting_verification':
      return {
        bg: 'bg-amber-50 border-amber-200 text-amber-800',
        icon: 'pi pi-clock text-amber-600',
        title: 'Pembayaran Sedang Diverifikasi',
        desc: 'Bukti transfer sedang dicek. Pesanan akan lanjut ke proses setelah verifikasi.'
      };
    case 'processing':
      return {
        bg: 'bg-emerald-50 border-emerald-200 text-emerald-800',
        icon: 'pi pi-check-circle text-emerald-600',
        title: 'Dikonfirmasi',
        desc: 'Pesanan sudah dikonfirmasi dan siap dikemas.'
      };
    case 'packed':
      return {
        bg: 'bg-indigo-50 border-indigo-200 text-indigo-800',
        icon: 'pi pi-box text-indigo-600',
        title: 'Dikemas',
        desc: 'Barang sedang dipacking oleh seller.'
      };
    case 'shipped':
      return {
        bg: 'bg-blue-50 border-blue-200 text-blue-800',
        icon: 'pi pi-truck text-blue-600',
        title: props.shippingMethod === 'cod' ? 'Sedang Dikirim (COD / Ketemuan)' : 'Dikirim',
        desc: props.shippingMethod === 'cod' ? 'Pesanan sedang dalam perjalanan menuju titik temu.' : 'Paket dalam perjalanan menuju alamat pembeli.'
      };
    case 'cod_meeting':
      return {
        bg: 'bg-orange-50 border-orange-200 text-orange-800',
        icon: 'pi pi-map-marker text-orange-600',
        title: 'Ketemuan',
        desc: 'Pesanan sudah sampai di lokasi ketemuan. Selesaikan pesanan setelah buyer menerima barang.'
      };
    case 'completed':
      return {
        bg: 'bg-emerald-50 border-emerald-200 text-emerald-800',
        icon: 'pi pi-check-circle text-emerald-600',
        title: 'Pesanan Selesai',
        desc: 'Dana diteruskan ke saldo toko. Terima kasih telah memproses pesanan dengan baik.'
      };
    case 'cancelled':
      return {
        bg: 'bg-red-50 border-red-200 text-red-800',
        icon: 'pi pi-times-circle text-red-600',
        title: 'Pesanan Dibatalkan / Ditolak',
        desc: props.notes || 'Pesanan ini telah dibatalkan oleh sistem atau penjual.'
      };
    default:
      return {
        bg: 'bg-blue-50 border-blue-200 text-blue-800',
        icon: 'pi pi-info-circle text-blue-600',
        title: 'Informasi Pesanan',
        desc: 'Status pesanan sedang diperbarui secara otomatis.'
      };
  }
});
</script>

<template>
  <div :class="['p-4 rounded-xl border flex items-start gap-3 mb-6', alertConfig.bg]">
    <i :class="[alertConfig.icon, 'text-xl mt-0.5']"></i>
    <div>
      <h4 class="font-bold text-sm mb-0.5">{{ alertConfig.title }}</h4>
      <p class="text-xs leading-relaxed opacity-90">{{ alertConfig.desc }}</p>
    </div>
  </div>
</template>