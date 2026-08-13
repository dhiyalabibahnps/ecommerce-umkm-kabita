<script setup lang="ts">
import type { Shop } from '@/types/entities';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

defineProps<{
  visible: boolean
  shop: Partial<Shop> | null
}>()

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void
  (e: 'approve'): void
}>()
</script>

<template>
  <Dialog :visible="visible" modal :showHeader="false" :style="{ width: '45rem' }"
    @update:visible="emit('update:visible', $event)">
    <div v-if="shop" class="bg-white rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <div class="flex items-center gap-2 text-blue-600 font-bold text-lg">
          <i class="pi pi-shield"></i>
          <span>Detail Verifikasi Toko</span>
        </div>
        <button @click="emit('update:visible', false)"
          class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100">
          <i class="pi pi-times text-sm"></i>
        </button>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5 bg-slate-50/50">
        <div class="space-y-5">
          <div class="bg-white p-4 rounded-2xl border border-slate-100 flex items-center gap-4">
            <img :src="shop.logo || 'https://via.placeholder.com/150'"
              class="w-16 h-16 rounded-xl object-cover border border-slate-200" />
            <div>
              <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-1">Nama Toko</p>
              <h4 class="text-base font-bold text-slate-800">{{ shop.name }}</h4>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5"><i class="pi pi-user text-[10px]"></i> {{
                shop.seller?.name }}</p>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl border border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-4">Informasi Kontak</p>
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <i class="pi pi-phone text-blue-500 mt-0.5"></i>
                <div>
                  <p class="text-[11px] text-slate-500">Nomor Telepon</p>
                  <p class="text-sm font-semibold text-slate-800">{{ shop.seller?.phone || '-' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <i class="pi pi-envelope text-blue-500 mt-0.5"></i>
                <div>
                  <p class="text-[11px] text-slate-500">Email</p>
                  <p class="text-sm font-semibold text-slate-800">{{ shop.seller?.email }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-5">
          <div class="bg-white p-4 rounded-2xl border border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-4">Legalitas & Lokasi</p>
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <i class="pi pi-id-card text-emerald-500 mt-0.5"></i>
                <div>
                  <p class="text-[11px] text-slate-500">Nomor Induk Berusaha (NIB)</p>
                  <span
                    class="inline-block mt-1 px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-md font-mono border border-slate-200">
                    9120304567890
                  </span>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <i class="pi pi-map-marker text-slate-400 mt-0.5"></i>
                <div>
                  <p class="text-[11px] text-slate-500 mb-0.5">Alamat Lengkap</p>
                  <p class="text-xs text-slate-700 leading-relaxed">
                    {{ shop.seller?.address || 'Jl. Sudirman No. 45, Komplek Ruko Sentra Bisnis Blok B2...' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl border border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Deskripsi Toko</p>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
              <p class="text-xs text-slate-600 leading-relaxed">{{ shop.description || 'Tidak ada deskripsi.' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="p-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
        <Button label="Tutup" outlined class="border-slate-300! text-slate-600! px-5!"
          @click="emit('update:visible', false)" />
        <Button v-if="shop.status === 'pending'" label="Verifikasi" icon="pi pi-check"
          class="bg-blue-600! border-blue-600! px-5!" @click="emit('approve')" />
      </div>
    </div>
  </Dialog>
</template>