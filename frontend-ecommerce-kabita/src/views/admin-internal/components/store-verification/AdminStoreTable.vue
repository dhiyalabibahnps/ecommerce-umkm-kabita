<script setup lang="ts">
import type { Shop } from '@/types/entities';
import Button from 'primevue/button';

defineProps<{
  shops: Partial<Shop>[]
}>()

const emit = defineEmits<{
  (e: 'viewDetail', shop: Partial<Shop>): void
  (e: 'approve', shop: Partial<Shop>): void
  (e: 'reject', shop: Partial<Shop>): void
}>()
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="border-b border-slate-100 text-slate-500 text-[11px] font-bold uppercase tracking-wider bg-slate-50/50">
            <th class="py-4 px-6 w-1/2">TOKO</th>
            <th class="py-4 px-6">PENJUAL</th>
            <th class="py-4 px-6 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          <tr v-if="shops.length === 0">
            <td colspan="3" class="py-10 text-center text-slate-400">Tidak ada data toko.</td>
          </tr>
          <tr v-for="shop in shops" :key="shop.id" class="hover:bg-slate-50/80 transition-colors">
            <td class="py-4 px-6">
              <div class="flex items-center gap-4">
                <img :src="shop.logo || 'https://via.placeholder.com/150'"
                  class="w-12 h-12 rounded-xl object-cover border border-slate-200" />
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800 text-base">{{ shop.name }}</span>
                  <span class="text-[11px] font-semibold mt-0.5"
                    :class="shop.status === 'pending' ? 'text-orange-500' : (shop.status === 'verified' ? 'text-emerald-500' : 'text-red-500')">
                    {{ shop.status === 'pending' ?
                      'Menunggu Verifikasi' : (shop.status === 'verified' ? 'Sudah Diverifikasi' : 'Ditolak')
                    }}
                  </span>
                </div>
              </div>
            </td>
            <td class="py-4 px-6 text-slate-600 font-medium">
              {{ shop.seller?.name || 'Seller User' }}
            </td>
            <td class="py-4 px-6 text-right">
              <div class="flex items-center justify-end gap-2">
                <Button label="Lihat Detail" outlined size="small"
                  class="px-3! py-1!.5 text-xs! font-bold! border-blue-200! text-blue-600! hover:bg-blue-50!"
                  @click="emit('viewDetail', shop)" />
                <Button v-if="shop.status === 'pending'" label="Verifikasi" size="small"
                  class="px-3! py-1!.5 text-xs! font-bold! bg-blue-600! border-blue-600!"
                  @click="emit('approve', shop)" />
                <Button v-if="shop.status === 'pending'" label="Tolak" size="small"
                  class="px-3! py-1!.5 text-xs! font-bold! bg-red-50! border-transparent! text-red-600! hover:bg-red-100!"
                  @click="emit('reject', shop)" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>