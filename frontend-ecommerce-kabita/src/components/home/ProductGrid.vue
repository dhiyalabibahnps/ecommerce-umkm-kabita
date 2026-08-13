<script setup lang="ts">
import ProductCard from '@/components/ui/ProductCard.vue';
import type { Product } from '@/types';

const props = defineProps<{ products: Product[] }>()

</script>

<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    <div v-for="product in props.products" :key="product.id">
      <ProductCard :product="product" />
    </div>
  </div>
</template>
