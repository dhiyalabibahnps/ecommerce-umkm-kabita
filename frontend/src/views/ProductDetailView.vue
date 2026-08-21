<script setup lang="ts">
import { getApiErrorMessage } from '@/services/apiError'
import { publicProductService } from '@/services/publicProductService'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useChatStore } from '@/stores/chat'
import type { Product } from '@/types'
import { formatRupiah } from '@/utils/format'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const slug = computed(() => String(route.params.id ?? ''))
const product = ref<Product | null>(null)
const isLoadingGet = ref<boolean>(true)
const showChatDialog = ref<boolean>(false)
// const productStore = useProductStore()
const cartStore = useCartStore()
const chatStore = useChatStore()
const toast = useToast()
const authStore = useAuthStore();

async function getProduct() {
  try {
    const response = await publicProductService.getBySlug(slug.value);

    if (response.success && response.data) {
      product.value = response.data;
    }
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal memuat detail barang'),
      life: 3000,
    })
  } finally {
    isLoadingGet.value = false;
  }
}

onMounted(() => {
  getProduct()
})

const selectedImageIndex = ref(0)
const quantity = ref(1)

watchEffect(() => {
  selectedImageIndex.value = 0
  quantity.value = 1
})

const selectedImage = computed(() => {
  const images = product.value?.images
  return images?.[selectedImageIndex.value]?.url ?? images?.[0]?.url ?? 'https://placehold.co/600x600?text=Produk'
})

const incrementQuantity = () => {
  if (!product.value) return
  if (quantity.value < product.value.stock) {
    quantity.value += 1
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value -= 1
  }
}

const changeImage = (index: number) => {
  selectedImageIndex.value = index
}

const handleAddToCart = async () => {
  if (!product.value) return
  const success = await cartStore.addToCart(product.value, quantity.value)
  toast.add({
    severity: success ? 'success' : 'error',
    summary: success ? 'Berhasil' : 'Gagal',
    detail: success ? `${product.value.name} berhasil ditambahkan ke keranjang` : (cartStore.error || 'Gagal menambahkan produk'),
    life: 3000,
  })
}

const goToShop = () => {
  if (product.value?.shop) {
    const slugOrId = product.value.shop.slug || product.value.shop.id;
    router.push(`/toko/${slugOrId}`);
  }
};

const openChatWithSeller = () => {
  if (!authStore.user) {
    toast.add({
      severity: 'info',
      summary: 'Perhatian',
      detail: 'Silakan masuk terlebih dahulu untuk mengirim pesan ke penjual',
      life: 3000,
    });
    router.push('/login');
    return;
  }
  if (product.value?.shop?.id) {
    chatStore.openShopChat(product.value.shop.id, product.value.shop.name, product.value.name);
  }
};

const handleBuyNow = async () => {
  if (!product.value) return
  if (!authStore.user) {
    toast.add({
      severity: 'info',
      summary: 'Perhatian',
      detail: 'Silakan masuk terlebih dahulu untuk membeli produk',
      life: 3000,
    })
    router.push('/login')
    return
  }

  const success = await cartStore.addToCart(product.value, quantity.value)
  if (!success) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: cartStore.error || 'Gagal menambahkan produk', life: 3000 })
    return
  }

  const cartItem = cartStore.flatItems.find(
    (i) => i.product_id === product.value?.id || i.product?.id === product.value?.id
  )

  if (cartStore.cart && cartItem) {
    const shopGroup = cartStore.cart.groups_by_shop.find((g) =>
      g.items.some((i) => i.id === cartItem.id)
    )
    if (shopGroup) {
      const selectedCheckoutItems = [
        {
          ...cartStore.cart,
          groups_by_shop: [
            {
              ...shopGroup,
              items: [cartItem],
            },
          ],
        },
      ]
      localStorage.setItem('checkoutItems', JSON.stringify(selectedCheckoutItems))
    }
  }

  router.push('/checkout')
}
</script>

<template>
  <div class="max-w-2xl lg:max-w-5xl xl:max-w-7xl container mx-auto px-4 py-8">
    <div class="flex flex-col gap-6">
      <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
        <ol class="flex flex-wrap items-center gap-2">
          <li>
            <router-link to="/" class="text-slate-500 hover:text-slate-700">Home</router-link>
          </li>
          <li>
            <span class="text-slate-300">/</span>
          </li>
          <li>
            <router-link :to="product?.category ? `/kategori/${product.category.slug}` : '/produk'"
              class="text-slate-500 hover:text-slate-700">
              {{ product?.category?.name ?? 'Produk' }}
            </router-link>
          </li>
          <li>
            <span class="text-slate-300">/</span>
          </li>
          <li class="text-slate-900 font-semibold">
            {{ product?.name ?? 'Produk tidak ditemukan' }}
          </li>
        </ol>
      </nav>

      <section v-if="product" class="grid gap-8 sm:grid-cols-[5fr_7fr]">
        <div class="space-y-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="aspect-square overflow-hidden rounded-xl bg-slate-50 flex items-center justify-center">
              <img :src="selectedImage" :alt="product.name" class="h-full w-full object-contain" />
            </div>
          </div>

          <div class="grid grid-cols-4 gap-3">
            <button v-for="(image, index) in product.images" :key="image.id" type="button"
              class="rounded-xl border bg-white p-1 transition focus:outline-none cursor-pointer"
              :class="selectedImageIndex === index ? 'border-blue-600 ring-2 ring-blue-500/20 shadow-xs' : 'border-slate-200 hover:border-slate-300'"
              @click="changeImage(index)">
              <img :src="image.url ?? 'https://placehold.co/120x120?text=Gambar'" :alt="`Thumbnail ${index + 1}`"
                class="h-20 w-full object-contain rounded-lg" />
            </button>
          </div>
        </div>

        <div class="space-y-6">
          <div class="space-y-3">
            <h1 class="text-2xl font-bold leading-tight text-slate-900 lg:text-3xl">
              {{ product.name }}
            </h1>

            <div class="text-3xl font-extrabold text-blue-600 leading-tight">
              {{ formatRupiah(product.price) }}
            </div>
          </div>

          <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div class="flex flex-row flex-wrap items-center gap-4 min-w-30">
              <div class="text-sm font-bold text-slate-900">Kuantitas</div>
              <div>
                <div class="inline-flex overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xs">
                  <button type="button"
                    class="px-3 py-1.5 text-base text-slate-600 transition hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                    @click="decrementQuantity" :disabled="quantity <= 1">
                    −
                  </button>
                  <div class="flex min-w-12 items-center justify-center px-4 text-sm font-bold text-slate-900 border-x border-slate-200">
                    {{ quantity }}
                  </div>
                  <button type="button"
                    class="px-3 py-1.5 text-base text-slate-600 transition hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                    @click="incrementQuantity" :disabled="quantity >= product.stock">
                    +
                  </button>
                </div>
              </div>
              <div class="text-xs text-slate-500 font-medium">Stok Tersedia: <strong class="text-slate-800">{{ product.stock }}</strong></div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center pt-2">
              <button type="button" @click="handleAddToCart"
                class="border-2 border-blue-600 bg-white text-blue-600 hover:bg-blue-50 inline-flex flex-1 items-center justify-center gap-2 rounded-xl p-3 text-sm font-bold transition shadow-xs cursor-pointer">
                <i class="pi pi-shopping-cart text-base"></i>
                Tambah Keranjang
              </button>
              <button type="button" @click="handleBuyNow"
                class="border-2 border-blue-600 bg-blue-600 text-white hover:bg-blue-700 inline-flex flex-1 items-center justify-center gap-2 rounded-xl p-3 text-sm font-bold transition shadow-md shadow-blue-500/20 cursor-pointer">
                <i class="pi pi-bolt text-base"></i>
                Beli Sekarang
              </button>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 shadow-xs">
            <div class="flex flex-wrap items-center gap-4">
              <div class="flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-white shadow-2xs text-blue-600">
                <i class="pi pi-shop text-lg"></i>
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-base font-bold text-slate-900 cursor-pointer hover:text-blue-600 transition" @click="goToShop">
                  {{ product.shop?.name ?? 'Toko Seller' }}
                </div>
                <div class="text-xs font-medium text-slate-500 mt-0.5">{{ product.shop?.address ?? 'Indonesia' }}</div>
              </div>
              <div class="flex items-center gap-2">
                <button type="button" @click="openChatWithSeller"
                  class="rounded-xl border border-blue-600 bg-white text-blue-600 px-3.5 py-2 text-xs font-bold transition hover:bg-blue-50 cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                  <i class="pi pi-comments text-xs"></i>
                  Chat Penjual
                </button>
                <button type="button" @click="goToShop"
                  class="rounded-xl border border-slate-300 bg-white text-slate-700 px-3.5 py-2 text-xs font-bold transition hover:bg-slate-50 hover:border-slate-400 cursor-pointer shadow-2xs">
                  Kunjungi Toko
                </button>
              </div>
            </div>
          </div>

          <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
            <div>
              <h2 class="text-base font-bold text-slate-900">Deskripsi Produk</h2>
            </div>
            <div class="text-xs sm:text-sm leading-relaxed text-slate-600 whitespace-pre-line">
              <p>{{ product.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <section v-else class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-slate-700 shadow-sm">
        <i class="pi pi-inbox text-4xl text-slate-300 mb-3 block"></i>
        <p class="text-lg font-bold text-slate-900">Produk tidak ditemukan.</p>
        <p class="mt-1 text-xs text-slate-500">Silakan kembali ke daftar produk atau cari produk UMKM lainnya.</p>
      </section>
    </div>
  </div>
</template>

<style scoped>
.detail-page__border {
  border: 1px solid #c3c6d7;
}

.detail-page__gallery-thumb {
  border: 1px solid #c3c6d7;
}

.detail-page__gallery-thumb--active {
  border-color: #004ac6;
}

.detail-page__quantity-control {
  border: 1px solid #c3c6d7;
}

.detail-page__color-option {
  border: 1px solid #c3c6d7;
  color: #434655;
  background-color: #ffffff;
}

.detail-page__color-option--active {
  border-color: #004ac6;
  background-color: #dbe1ff;
  color: #004ac6;
}

.detail-page__primary-button {
  border: 2px solid #004ac6;
  background-color: #004ac6;
}

.detail-page__secondary-button {
  border: 2px solid #004ac6;
  color: #004ac6;
  background-color: #ffffff;
}
</style>
