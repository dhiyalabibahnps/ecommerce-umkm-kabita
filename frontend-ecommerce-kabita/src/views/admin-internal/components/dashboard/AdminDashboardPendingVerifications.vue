<script setup lang="ts">
import type { Shop } from '@/types/entities';
import Button from 'primevue/button';

interface Props {
  pendingShops: Partial<Shop>[]
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'verify', shopId: number): void
  (e: 'reject', shopId: number): void
  (e: 'viewAll'): void
}>()
</script>

<template>
  <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-4">
        <div>
          <h4 class="text-base font-bold text-slate-800">Verifikasi Toko Baru</h4>
          <p class="text-xs text-slate-500">Toko yang menunggu persetujuan Admin</p>
        </div>
        <Button label="Lihat Semua" text size="small" class="text-xs! font-semibold! text-blue-600! hover:bg-blue-50!"
          @click="emit('viewAll')" />
      </div>

      <div v-if="pendingShops.length === 0" class="py-8 text-center text-slate-400 text-xs">
        <i class="pi pi-check-circle text-3xl text-emerald-500 mb-2 block"></i>
        Tidak ada pengajuan toko baru saat ini.
      </div>

      <div v-else class="space-y-3">
        <div v-for="shop in pendingShops" :key="shop.id"
          class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 overflow-hidden">
            <img
              :src="shop.logo || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=100'"
              alt="Shop Logo" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-slate-200" />
            <div class="truncate">
              <h5 class="text-sm font-bold text-slate-800 truncate">{{ shop.name }}</h5>
              <p class="text-xs text-slate-500 truncate">Pemilik: {{ shop.seller?.name || 'Seller Kabita' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 shrink-0">
            <Button icon="pi pi-times" severity="danger" outlined rounded class="w-8! h-8! p-0!" v-tooltip.top="'Tolak'"
              @click="emit('reject', shop.id!)" />
            <Button icon="pi pi-check" severity="success" rounded class="w-8! h-8! p-0!" v-tooltip.top="'Verifikasi'"
              @click="emit('verify', shop.id!)" />
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center gap-1.5">
      <i class="pi pi-info-circle text-amber-500"></i>
      <span>Persetujuan berpengaruh pada izin seller mengunggah produk.</span>
    </div>
  </div>
</template>