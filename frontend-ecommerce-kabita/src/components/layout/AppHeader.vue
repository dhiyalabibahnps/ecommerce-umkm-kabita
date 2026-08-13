<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import Button from 'primevue/button'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()
const searchQuery = ref('')

const isLoggedIn = computed(() => !!authStore.user)
const cartItemCount = computed(() => cartStore.totalItems)

function handleSearch() {
  const query = searchQuery.value.trim()
  if (query) {
    router.push(`/produk?search=${encodeURIComponent(query)}`)
  }
}
</script>

<template>
  <header class="bg-[#faf8ff] shadow-sm sticky top-0 z-50">
    <div class="container max-w-7xl mx-auto px-4 py-4">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-8">
          <router-link to="/" class="text-xl font-bold text-primary">Kabita</router-link>
          <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-slate-700">
            <router-link to="/" class="no-underline hover:text-slate-900">Beranda</router-link>
            <router-link to="/produk" class="no-underline hover:text-slate-900">Kategori</router-link>
            <router-link to="/" class="no-underline hover:text-slate-900">Tentang Kami</router-link>
            <router-link to="/" class="no-underline hover:text-slate-900">Bantuan</router-link>
          </nav>
        </div>

        <div class="flex items-center gap-2">
          <!-- <div class=" min-w-0">
            <div class="relative flex items-center rounded-full bg-surface-container-low px-4 py-2.5 shadow-sm">
              <span class="absolute left-4 text-slate-400">
                <i class="pi pi-search"></i>
              </span>

              <InputText v-model="searchQuery" placeholder="Cari di Kabita..." unstyled
                class="w-full border-0 bg-transparent pl-10 pr-4 text-sm text-slate-700 outline-none ring-0 shadow-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0" />
            </div>
          </div> -->
          <div class="relative">
            <Button icon="pi pi-shopping-cart" text aria-label="Keranjang" @click="router.push('/produk')" />
            <span v-if="cartItemCount"
              class="absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-semibold text-white">
              {{ cartItemCount }}
            </span>
          </div>
          <Button icon="pi pi-bell" text aria-label="Notifikasi" />
          <template v-if="isLoggedIn">
            <Button label="Profil" icon="pi pi-user" text @click="router.push('/profil')" />
          </template>
          <template v-else>
            <Button label="Masuk" text severity="secondary" @click="router.push('/login')" />
            <Button label="Daftar" severity="primary" @click="router.push('/register')" />
          </template>
        </div>
      </div>
    </div>
  </header>
</template>
