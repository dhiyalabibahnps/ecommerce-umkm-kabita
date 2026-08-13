<script setup lang="ts">
import type { Product } from '@/types/entities';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { ref, watch } from 'vue';

const props = defineProps<{
  visible: boolean
  product: Partial<Product> | null
}>()

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void
  (e: 'approve'): void
  (e: 'reject'): void
}>()

const activeImageIndex = ref(0)

watch(
  () => props.product,
  () => {
    activeImageIndex.value = 0
  }
)

const formatRupiah = (val: string | number) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num || 0)
}
</script>

<template>
  <Dialog :visible="visible" modal :showHeader="false" :style="{ width: '48rem' }"
    @update:visible="emit('update:visible', $event)">
    <div v-if="product" class="bg-white rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between p-5 border-b border-slate-100">
        <div class="flex items-center gap-2 text-blue-600 font-bold text-lg">
          <i class="pi pi-shield"></i>
          <span>Detail & Review Produk</span>
        </div>
        <button @click="emit('update:visible', false)"
          class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 transition-colors">
          <i class="pi pi-times text-sm"></i>
        </button>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[75vh] overflow-y-auto bg-slate-50/50">
        <div class="space-y-5">
          <div class="bg-white p-4 rounded-2xl border border-slate-200/80">
            <div class="aspect-square w-full rounded-xl overflow-hidden bg-slate-100 mb-3 border border-slate-200">
              <img
                :src="product.images?.[activeImageIndex]?.url || product.images?.[0]?.url || 'https://via.placeholder.com/400'"
                alt="Main Product Preview" class="w-full h-full object-cover" />
            </div>
            <div v-if="product.images && product.images.length > 1"
              class="flex items-center gap-2 overflow-x-auto pb-1">
              <button v-for="(img, idx) in product.images" :key="img.id || idx" @click="activeImageIndex = idx"
                class="w-14 h-14 rounded-lg overflow-hidden border-2 shrink-0 transition-all"
                :class="activeImageIndex === idx ? 'border-blue-600 ring-2 ring-blue-500/20' : 'border-slate-200 opacity-70 hover:opacity-100'">
                <img :src="img.url as string" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl border border-slate-200/80 space-y-3">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Spesifikasi Produk</p>
            <div class="grid grid-cols-2 gap-3 text-xs">
              <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] mb-0.5">Harga Produk</span>
                <span class="font-extrabold text-blue-600 text-sm">{{ formatRupiah(product.price || 0) }}</span>
              </div>
              <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] mb-0.5">Stok Tersedia</span>
                <span class="font-bold text-slate-800 text-sm">{{ product.stock || 0 }} Pcs</span>
              </div>
              <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] mb-0.5">Berat Barang</span>
                <span class="font-bold text-slate-800 text-sm">{{ product.weight || 0 }} gram</span>
              </div>
              <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] mb-0.5">Kategori</span>
                <span class="font-bold text-slate-800 text-sm">{{ product.category?.name || 'Umum' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-5">
          <div class="bg-white p-4 rounded-2xl border border-slate-200/80">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">Informasi Toko & Seller</p>
            <div class="flex items-center gap-3.5 pb-3 mb-3 border-b border-slate-100">
              <img :src="product.shop?.logo || 'https://via.placeholder.com/150'"
                class="w-12 h-12 rounded-xl object-cover border border-slate-200" />
              <div>
                <h4 class="font-bold text-slate-800 text-base leading-tight">{{ product.shop?.name || 'Toko Kabita' }}
                </h4>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                  <i class="pi pi-user text-[10px]"></i>
                  Pemilik: {{ product.shop?.seller?.name || 'Seller' }}
                </p>
              </div>
            </div>
            <div class="space-y-2 text-xs text-slate-600">
              <div class="flex items-center justify-between">
                <span class="text-slate-400">Email Seller:</span>
                <span class="font-semibold text-slate-700">{{ product.shop?.seller?.email || '-' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400">No. Telepon:</span>
                <span class="font-semibold text-slate-700">{{ product.shop?.seller?.phone || '-' }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white p-4 rounded-2xl border border-slate-200/80">
            <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Deskripsi Produk</p>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 min-h-30">
              <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                {{ product.description || 'Tidak ada deskripsi produk.' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="p-4 border-t border-slate-100 flex items-center justify-between bg-white">
        <Button label="Tutup" outlined class="border-slate-300! text-slate-600! px-5!"
          @click="emit('update:visible', false)" />
        <div class="flex items-center gap-2">
          <Button v-if="product.status === 'pending'" label="Tolak" severity="danger" outlined
            class="border-red-200! text-red-600! hover:bg-red-50! px-5!" @click="emit('reject')" />
          <Button v-if="product.status === 'pending'" label="Setujui Produk" icon="pi pi-check"
            class="bg-blue-600! border-blue-600! px-5!" @click="emit('approve')" />
        </div>
      </div>
    </div>
  </Dialog>
</template>