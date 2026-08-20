<script setup lang="ts">
import ProductCard from '@/components/ui/ProductCard.vue';
import type { Product } from '@/types';

const props = defineProps<{ products: Product[] }>()

</script>

<template>
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6">
    <div v-for="product in props.products" :key="product.id">
      <ProductCard :product="product" />
    </div>
  </div>
</template>
