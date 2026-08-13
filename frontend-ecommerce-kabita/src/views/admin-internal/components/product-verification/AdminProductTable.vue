<script setup lang="ts">
import type { Product } from '@/types/entities';
import Button from 'primevue/button';

defineProps<{
  products: Partial<Product>[]
}>()

const emit = defineEmits<{
  (e: 'viewDetail', product: Partial<Product>): void
  (e: 'approve', product: Partial<Product>): void
  (e: 'reject', product: Partial<Product>): void
}>()

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num || 0)
}
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="border-b border-slate-100 text-slate-400 text-[11px] font-bold uppercase tracking-wider bg-slate-50/50">
            <th class="py-4 px-6 w-1/2">PRODUK</th>
            <th class="py-4 px-6">TOKO / PENJUAL</th>
            <th class="py-4 px-6 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm">
          <tr v-if="products.length === 0">
            <td colspan="3" class="py-12 text-center text-slate-400 font-medium">
              <i class="pi pi-box text-3xl text-slate-300 mb-2 block"></i>
              Tidak ada produk yang memerlukan tindakan.
            </td>
          </tr>
          <tr v-for="product in products" :key="product.id" class="hover:bg-slate-50/80 transition-colors">
            <td class="py-4 px-6">
              <div class="flex items-center gap-4">
                <img :src="product.images?.[0]?.url || 'https://via.placeholder.com/150'" alt="Product Image"
                  class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0" />
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800 text-base leading-snug line-clamp-1">{{ product.name }}</span>
                  <span class="text-xs text-slate-400 mt-0.5">{{ product.category?.name || 'Kategori' }}</span>
                  <span class="text-sm font-extrabold text-blue-600 mt-1">{{ formatRupiah(product.price || 0) }}</span>
                </div>
              </div>
            </td>

            <td class="py-4 px-6">
              <div class="flex flex-col">
                <span class="font-bold text-slate-800 text-sm">{{ product.shop?.name || 'Nama Toko' }}</span>
                <span class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                  <i class="pi pi-user text-[10px]"></i>
                  {{ product.shop?.seller?.name || 'Seller' }}
                </span>
              </div>
            </td>

            <td class="py-4 px-6 text-right">
              <div class="flex items-center justify-end gap-2">
                <Button label="Review" outlined size="small"
                  class="px-3!.5 py-1!.5 text-xs! font-bold! border-blue-200! text-blue-600! hover:bg-blue-50!"
                  @click="emit('viewDetail', product)" />
                <Button v-if="product.status === 'pending'" label="Setujui" size="small"
                  class="px-3!.5 py-1!.5 text-xs! font-bold! bg-blue-600! border-blue-600! hover:bg-blue-700!"
                  @click="emit('approve', product)" />
                <Button v-if="product.status === 'pending'" label="Tolak" size="small"
                  class="px-3!.5 py-1!.5 text-xs! font-bold! bg-red-50! border-transparent! text-red-600! hover:bg-red-100!"
                  @click="emit('reject', product)" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>