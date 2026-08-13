<script setup lang="ts">
import type { User } from '@/types/entities';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const props = defineProps<{
  visible: boolean
  user: Partial<User> | null
}>()

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void
}>()

const close = () => emit('update:visible', false)

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<template>
  <Dialog :visible="visible" modal :showHeader="false" :style="{ width: '32rem' }"
    @update:visible="emit('update:visible', $event)">
    <div v-if="user" class="relative p-6 bg-white rounded-2xl">
      <button @click="close"
        class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors">
        <i class="pi pi-times text-sm"></i>
      </button>

      <div class="flex flex-col items-center mt-4 mb-6">
        <div class="relative mb-3">
          <img :src="user.proof_image || `https://ui-avatars.com/api/?name=${user.name}&background=random`"
            class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-white ring-1 ring-slate-200" />
          <span v-if="user.status === 'active'"
            class="absolute bottom-0 right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></span>
        </div>
        <h3 class="text-xl font-bold text-slate-800">{{ user.name }}</h3>
        <div class="flex items-center gap-2 mt-2">
          <span class="px-3 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 capitalize">{{ user.status
            }}</span>
          <span class="px-3 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-600 capitalize">{{ user.role ===
            'seller' ? 'Vendor' : user.role }}</span>
        </div>
      </div>

      <div class="mb-6">
        <h4 class="text-[11px] font-bold text-slate-500 tracking-wider mb-3">INFORMASI DASAR</h4>
        <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-4 grid grid-cols-2 gap-4">
          <div>
            <div class="flex items-center gap-2 text-slate-500 mb-1">
              <i class="pi pi-envelope text-sm"></i> <span class="text-xs font-semibold">Email</span>
            </div>
            <p class="text-sm font-semibold text-slate-800 truncate">{{ user.email }}</p>
          </div>
          <div>
            <div class="flex items-center gap-2 text-slate-500 mb-1">
              <i class="pi pi-phone text-sm"></i> <span class="text-xs font-semibold">Nomor Telepon</span>
            </div>
            <p class="text-sm font-semibold text-slate-800">{{ user.phone || '-' }}</p>
          </div>
          <div>
            <div class="flex items-center gap-2 text-slate-500 mb-1">
              <i class="pi pi-id-card text-sm"></i> <span class="text-xs font-semibold">Peran (Role)</span>
            </div>
            <p class="text-sm font-semibold text-slate-800 capitalize">{{ user.role === 'seller' ? 'Penjual (UMKM)' :
              user.role }}</p>
          </div>
          <div>
            <div class="flex items-center gap-2 text-slate-500 mb-1">
              <i class="pi pi-calendar text-sm"></i> <span class="text-xs font-semibold">Tanggal Registrasi</span>
            </div>
            <p class="text-sm font-semibold text-slate-800">{{ formatDate(user.created_at) }}</p>
          </div>
        </div>
      </div>

      <div class="mb-6">
        <h4 class="text-[11px] font-bold text-slate-500 tracking-wider mb-3">STATISTIK AKTIVITAS</h4>
        <div class="grid grid-cols-3 gap-3">
          <div
            class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 flex flex-col items-center justify-center text-center">
            <i class="pi pi-sign-in text-blue-500 mb-2"></i>
            <span class="text-xs text-slate-500 mb-1">Login Terakhir</span>
            <span class="text-sm font-bold text-slate-800">Hari ini</span>
          </div>
          <div
            class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 flex flex-col items-center justify-center text-center">
            <i class="pi pi-shopping-bag text-emerald-500 mb-2"></i>
            <span class="text-xs text-slate-500 mb-1">Transaksi</span>
            <span class="text-lg font-bold text-slate-800">142</span>
          </div>
          <div
            class="bg-slate-50/80 border border-slate-100 rounded-xl p-3 flex flex-col items-center justify-center text-center">
            <i class="pi pi-star text-amber-500 mb-2"></i>
            <span class="text-xs text-slate-500 mb-1">Ulasan</span>
            <span class="text-lg font-bold text-slate-800">89</span>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <Button label="Tutup" outlined class="text-slate-600! border-slate-300! px-5!" @click="close" />
        <Button label="Edit User" icon="pi pi-pencil" class="bg-blue-600! border-blue-600! text-white! px-5!" />
      </div>
    </div>
  </Dialog>
</template>