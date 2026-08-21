<script setup lang="ts">
import { useCartStore } from '@/stores/cart'
import type { Product } from '@/types'
import { formatRupiah } from '@/utils/format'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { computed } from 'vue'

const props = defineProps<{ product: Product }>()

const cartStore = useCartStore()
const toast = useToast()
const cartItem = computed(() => cartStore.flatItems.find((item) => item.product_id === props.product.id))

async function handleAddToCart() {
  const success = await cartStore.addToCart(props.product)
  toast.add({
    severity: success ? 'success' : 'error',
    summary: success ? 'Berhasil' : 'Gagal',
    detail: success ? `${props.product.name} berhasil ditambahkan ke keranjang` : (cartStore.error || 'Gagal menambahkan produk'),
    life: 3000,
  })
}

async function decreaseQuantity() {
  if (!cartItem.value) return
  if (cartItem.value.quantity <= 1) await cartStore.removeItem(cartItem.value.id)
  else await cartStore.updateQuantity(cartItem.value.id, cartItem.value.quantity - 1)
}
</script>

<template>
  <router-link :to="`/produk/${props.product.slug}`" class="group block h-full no-underline">
    <div class="h-full overflow-hidden rounded-2xl border border-slate-200/80 bg-white hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
      <div>
        <div class="relative aspect-square w-full overflow-hidden bg-slate-50">
          <img
            v-if="props.product.images?.[0]?.url"
            :src="props.product.images[0].url"
            :alt="props.product.name"
            class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <div v-else class="flex h-full w-full items-center justify-center text-xs text-slate-400">
            <i class="pi pi-image text-2xl text-slate-300"></i>
          </div>

          <!-- Stock badge if low -->
          <span
            v-if="props.product.stock <= 5 && props.product.stock > 0"
            class="absolute top-2 left-2 rounded-md bg-amber-500/90 backdrop-blur-xs px-2 py-0.5 text-[10px] font-bold text-white shadow-xs"
          >
            Sisa {{ props.product.stock }}
          </span>
        </div>

        <div class="p-3.5 space-y-1.5">
          <div class="flex items-center gap-1.5 text-[11px] text-slate-500 truncate">
            <i class="pi pi-shop text-[10px] text-blue-600"></i>
            <span class="truncate font-medium">{{ props.product.shop?.name || 'Toko UMKM' }}</span>
          </div>

          <h3 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 leading-snug group-hover:text-blue-600 transition-colors">
            {{ props.product.name }}
          </h3>
        </div>
      </div>

      <div class="p-3.5 pt-0 mt-auto">
        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
          <div>
            <span class="text-[10px] text-slate-400 block">Harga</span>
            <span class="text-sm sm:text-base font-extrabold text-blue-600">
              {{ formatRupiah(props.product.price) }}
            </span>
          </div>

          <div v-if="cartItem" class="flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 p-0.5" @click.prevent.stop>
            <Button
              icon="pi pi-minus"
              text
              rounded
              size="small"
              class="w-6! h-6! p-0! text-xs!"
              aria-label="Kurangi jumlah"
              :disabled="cartStore.actionLoading"
              @click.prevent.stop="decreaseQuantity"
            />
            <span class="min-w-5 text-center text-xs font-bold text-blue-700">{{ cartItem.quantity }}</span>
            <Button
              icon="pi pi-plus"
              text
              rounded
              size="small"
              class="w-6! h-6! p-0! text-xs!"
              aria-label="Tambah jumlah"
              :disabled="cartStore.actionLoading"
              @click.prevent.stop="handleAddToCart"
            />
          </div>

          <Button
            v-else
            icon="pi pi-plus"
            rounded
            size="small"
            class="w-8! h-8! p-0! bg-blue-50! border-blue-200! text-blue-600! hover:bg-blue-600! hover:text-white! transition-colors"
            aria-label="Tambah ke Keranjang"
            :loading="cartStore.actionLoading"
            @click.prevent.stop="handleAddToCart"
          />
        </div>
      </div>
    </div>
  </router-link>
</template>
