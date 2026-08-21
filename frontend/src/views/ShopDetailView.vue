<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/services/apiClient';
import { formatRupiah } from '@/utils/format';
import type { Shop, Product } from '@/types';
import ProgressSpinner from 'primevue/progressspinner';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const route = useRoute();
const router = useRouter();

const shop = ref<Shop | null>(null);
const isLoading = ref(true);
const errorMessage = ref('');

const slug = computed(() => String(route.params.slug || ''));

const fetchShopData = async () => {
  if (!slug.value) return;
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await apiClient.get(`/shops/${slug.value}`);
    shop.value = response.data.data;
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Toko tidak ditemukan.';
  } finally {
    isLoading.value = false;
  }
};

const getProductThumbnail = (prod: Product) => {
  if (prod.images && prod.images.length > 0) {
    return prod.images[0]?.url || 'https://placehold.co/300x300?text=Produk';
  }
  return 'https://placehold.co/300x300?text=Produk';
};

watch(
  () => route.params.slug,
  () => {
    fetchShopData();
  }
);

onMounted(() => {
  fetchShopData();
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
      <!-- Breadcrumb -->
      <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
        <ol class="flex items-center gap-2">
          <li>
            <router-link to="/" class="hover:text-slate-700">Home</router-link>
          </li>
          <li><span>/</span></li>
          <li class="text-slate-900 font-semibold">
            {{ shop?.name ?? 'Toko' }}
          </li>
        </ol>
      </nav>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-3">
        <ProgressSpinner style="width: 48px; height: 48px" strokeWidth="4" />
        <span class="text-sm font-semibold text-slate-500">Memuat profil toko...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage || !shop" class="bg-white rounded-2xl border border-slate-200 p-12 text-center max-w-lg mx-auto shadow-sm">
        <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-2xl">
          <i class="pi pi-store"></i>
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Toko Tidak Ditemukan</h2>
        <p class="text-sm text-slate-500 mb-6">{{ errorMessage || 'Toko yang Anda cari tidak tersedia atau belum diverifikasi.' }}</p>
        <Button label="Kembali ke Beranda" icon="pi pi-home" size="small" @click="router.push('/')" />
      </div>

      <!-- Shop Content -->
      <div v-else class="space-y-8">
        <!-- Shop Header Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <!-- Banner -->
          <div class="h-44 sm:h-56 bg-gradient-to-r from-blue-700 to-indigo-800 relative">
            <img
              v-if="shop.banner"
              :src="shop.banner"
              alt="Shop Banner"
              class="w-full h-full object-cover opacity-80"
            />
          </div>

          <!-- Profile info below banner -->
          <div class="px-6 py-5 relative">
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5 -mt-16 sm:-mt-20 mb-4">
              <!-- Logo -->
              <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white border-4 border-white shadow-md overflow-hidden flex items-center justify-center relative z-10">
                <img
                  v-if="shop.logo"
                  :src="shop.logo"
                  :alt="shop.name"
                  class="w-full h-full object-cover"
                />
                <i v-else class="pi pi-store text-4xl text-slate-400"></i>
              </div>

              <!-- Shop Title & Badges -->
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <h1 class="text-2xl font-bold text-slate-900">{{ shop.name }}</h1>
                  <Tag
                    v-if="shop.status === 'verified'"
                    value="Terverifikasi"
                    severity="success"
                    icon="pi pi-check-circle"
                    class="text-xs"
                  />
                </div>
                <p class="text-xs text-slate-500 flex items-center gap-1.5 flex-wrap">
                  <span v-if="shop.address"><i class="pi pi-map-marker text-slate-400"></i> {{ shop.address }}</span>
                  <span v-if="shop.address && shop.phone">•</span>
                  <span v-if="shop.phone"><i class="pi pi-phone text-slate-400"></i> {{ shop.phone }}</span>
                </p>
              </div>

              <!-- Summary stats -->
              <div class="flex items-center gap-6 self-start sm:self-auto bg-slate-50 px-4 py-3 rounded-xl border border-slate-100">
                <div class="text-center">
                  <div class="text-lg font-bold text-blue-600">{{ shop.products?.length ?? 0 }}</div>
                  <div class="text-[11px] font-medium text-slate-500">Total Produk</div>
                </div>
              </div>
            </div>

            <!-- Description -->
            <p v-if="shop.description" class="text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
              {{ shop.description }}
            </p>
          </div>
        </div>

        <!-- Shop Products Section -->
        <div>
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
              <i class="pi pi-box text-blue-600"></i>
              Produk dari {{ shop.name }}
            </h2>
            <span class="text-xs text-slate-500">{{ shop.products?.length ?? 0 }} Produk ditemukan</span>
          </div>

          <!-- Empty Products -->
          <div v-if="!shop.products || shop.products.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <i class="pi pi-inbox text-4xl text-slate-300 mb-3 block"></i>
            <h3 class="text-sm font-bold text-slate-800 mb-1">Belum Ada Produk</h3>
            <p class="text-xs text-slate-500">Toko ini belum menambahkan produk yang tersedia.</p>
          </div>

          <!-- Product Cards Grid -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <router-link
              v-for="prod in shop.products"
              :key="prod.id"
              :to="`/produk/${prod.slug || prod.id}`"
              class="group bg-white rounded-xl border border-slate-200 shadow-xs hover:shadow-md transition duration-200 overflow-hidden flex flex-col"
            >
              <div class="aspect-square w-full bg-slate-100 overflow-hidden relative">
                <img
                  :src="getProductThumbnail(prod)"
                  :alt="prod.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                />
              </div>
              <div class="p-3 flex-1 flex flex-col justify-between">
                <div>
                  <div class="text-[10px] text-blue-600 font-semibold mb-1 uppercase tracking-wide truncate">
                    {{ prod.category?.name || 'UMKM' }}
                  </div>
                  <h3 class="text-xs font-bold text-slate-800 line-clamp-2 mb-2 group-hover:text-blue-600 transition">
                    {{ prod.name }}
                  </h3>
                </div>
                <div class="mt-2">
                  <div class="text-sm font-bold text-blue-600">
                    {{ formatRupiah(prod.price) }}
                  </div>
                  <div class="text-[10px] text-slate-400 mt-0.5">
                    Stok: {{ prod.stock }}
                  </div>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
