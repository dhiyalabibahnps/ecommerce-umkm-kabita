<script setup lang="ts">
import { useCartStore } from '@/stores/cart'
import type { Product } from '@/types'
import { formatRupiah } from '@/utils/format'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'

const props = defineProps<{ product: Product }>()

const cartStore = useCartStore()
const toast = useToast()

function handleAddToCart() {
  cartStore.addToCart(props.product)
  toast.add({
    severity: 'success',
    summary: 'Berhasil',
    detail: `${props.product.name} berhasil ditambahkan ke keranjang`,
    life: 3000,
  })
}
</script>

<template>
  <div class="h-full overflow-hidden rounded-[24px] border border-[#e1e2ed] bg-white shadow-sm">
    <router-link :to="`/produk/${props.product.slug}`" class="block overflow-hidden bg-surface-container p-4">
      <img :src="props.product.images?.[0]?.url ?? 'https://placehold.co/400x300?text=Produk'" :alt="props.product.name"
        class="w-full h-45.5 object-contain" />
    </router-link>

    <div class="p-6">
      <router-link :to="`/produk/${props.product.id}`"
        class="no-underline text-base font-semibold text-slate-950 hover:text-slate-700">
        {{ props.product.name }}
      </router-link>
      <p class="text-sm text-slate-500 mt-2 mb-4">{{ props.product.shop?.name }}</p>
      <div class="flex items-center justify-between gap-3">
        <span class="text-lg font-bold text-slate-950">{{ formatRupiah(props.product.price) }}</span>
        <Button icon="pi pi-plus" class="p-button-rounded p-button-primary" aria-label="Tambah ke Keranjang"
          @click="handleAddToCart" />
      </div>
    </div>
  </div>
</template>
