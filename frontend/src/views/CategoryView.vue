<script setup lang="ts">
import { useRoute } from 'vue-router';

const route = useRoute()
const slug = route.params.slug as string
</script>

<template>
  <div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-4">Kategori</h1>
    <p class="text-color-secondary">Menampilkan produk kategori: <strong>{{ slug }}</strong></p>
  </div>
</template>
