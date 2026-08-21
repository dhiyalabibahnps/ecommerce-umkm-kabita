<script setup lang="ts">
import ProductGrid from '@/components/home/ProductGrid.vue';
import { publicProductService } from '@/services/publicProductService';
import type { Product } from '@/types';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';
import { onMounted, ref, watch } from 'vue';

const isLoadingGet = ref(true)
const listProduct = ref<Product[]>([]);
const toast = useToast()
console.log("[DEBUG] isLoadingGet : ", isLoadingGet)

async function getProduct() {
  console.log("[DEBUG] masuk 1")
  try {
    console.log("[DEBUG] masuk 2")
    const response = await publicProductService.listAtHome();

    console.log("[DEBUG] masuk 3")
    if (response.success && response.data) {
      console.log("[DEBUG] masuk 4")
      listProduct.value = response.data;
    }
    console.log("[DEBUG] masuk 5")
    isLoadingGet.value = false;
  } catch (error) {
    console.log("[DEBUG] masuk 6")
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: `Gagal memuat barang`,
      life: 3000,
    })
    console.log("[DEBUG] masuk 7")
    isLoadingGet.value = false;
  } finally {
    console.log("[DEBUG] masuk 8")
    isLoadingGet.value = false;
  }
}

onMounted(() => {
  getProduct();
})

watch(isLoadingGet, (newVal) => {
  console.log('[DEBUG] WATCH - isLoadingGet berubah menjadi : ', newVal);
});

</script>

<template>
  <section class="container max-w-2xl lg:max-w-5xl xl:max-w-7xl mx-auto px-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-950">Semua Produk</h2>
        <p class="text-sm text-slate-500">Temukan pilihan produk UMKM spesial hari ini.</p>
      </div>
      <router-link to="/produk" class="text-sky-600 font-semibold no-underline hover:text-sky-700">Lihat
        Semua</router-link>
    </div>

    <Transition name="fade">
      <div v-if="isLoadingGet"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
        <ProgressSpinner style="width: 56px; height: 56px" strokeWidth="4" fill="transparent" animationDuration=".8s"
          aria-label="Loading Daftar Barang" />
      </div>
    </Transition>

    <div v-if="!isLoadingGet" class="w-full h-fit">
      <ProductGrid :products="listProduct" />
    </div>

  </section>
</template>
