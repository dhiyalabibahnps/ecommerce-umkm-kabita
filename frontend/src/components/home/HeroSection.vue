<script setup lang="ts">
import Button from 'primevue/button'
import { useRouter } from 'vue-router'

const router = useRouter()
const heroImage = 'https://www.figma.com/api/mcp/asset/6da6ba2f-43cf-44b6-bbfb-43bee41e75e9.png'

function gotoProducts() {
  router.push('/produk')
}
</script>

<template>
  <section class="relative overflow-hidden min-h-136">
    <div class="absolute inset-0 bg-cover bg-center" :style="{ backgroundImage: `url(${heroImage})` }" />
    <div class="absolute inset-0 bg-linear-to-r from-slate-950/80 via-slate-950/40 to-transparent"></div>
    <div class="relative z-10 container mx-auto max-w-7xl px-4 py-24">
      <div class="max-w-2xl">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-4">
          Dukung UMKM Lokal, Belanja Lebih Mudah
        </h1>
        <p class="text-base sm:text-lg text-slate-200 mb-8 max-w-full">
          Temukan produk berkualitas dari jutaan penjual terpercaya di seluruh Indonesia.
        </p>
        <Button label="Mulai Belanja" severity="primary" class="p-button-raised" @click="gotoProducts" />
      </div>
    </div>
  </section>
</template>
