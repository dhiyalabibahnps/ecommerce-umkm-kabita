<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import Button from 'primevue/button'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()
const searchQuery = ref('')
const isMenuOpen = ref(false)
const isMobileSearchOpen = ref(false)
const searchInputRef = ref<HTMLInputElement | null>(null)

const isLoggedIn = computed(() => !!authStore.user)
const cartItemCount = computed(() => cartStore.totalItems)

onMounted(() => {
  if (authStore.user) void cartStore.loadCart()
})

watch(() => authStore.user?.id, (userId) => {
  if (userId) void cartStore.loadCart()
})

watch(isMobileSearchOpen, (open) => {
  if (open) {
    setTimeout(() => searchInputRef.value?.focus(), 100)
  }
})

function handleSearch() {
  const query = searchQuery.value.trim()
  if (query) {
    isMobileSearchOpen.value = false
    router.push(`/produk?search=${encodeURIComponent(query)}`)
  }
}

function closeMobileSearch() {
  isMobileSearchOpen.value = false
  searchQuery.value = ''
}

function closeMenu() {
  isMenuOpen.value = false
}

// close mobile menu on resize to desktop
function onResize() {
  if (window.innerWidth >= 1024) {
    isMenuOpen.value = false
    isMobileSearchOpen.value = false
  }
}
onMounted(() => window.addEventListener('resize', onResize))
onUnmounted(() => window.removeEventListener('resize', onResize))

async function handleLogout() {
  closeMenu()
  await authStore.logout()
}
</script>

<template>
  <header class="bg-[#faf8ff] shadow-sm sticky top-0 z-50">
    <div class="container max-w-7xl mx-auto px-4 py-4">
      <!-- Desktop header -->
      <div class="hidden lg:flex items-center justify-between gap-4">
        <div class="flex items-center gap-8">
          <router-link to="/" class="text-xl font-bold text-primary">Kabita</router-link>
          <nav class="flex items-center gap-6 text-sm font-medium text-slate-700">
            <router-link to="/" class="no-underline hover:text-slate-900">Beranda</router-link>
            <router-link to="/produk" class="no-underline hover:text-slate-900">Kategori</router-link>
            <router-link to="/tentang-kami" class="no-underline hover:text-slate-900">Tentang Kami</router-link>
            <router-link to="/bantuan" class="no-underline hover:text-slate-900">Bantuan</router-link>
          </nav>
        </div>

        <div class="flex items-center gap-2">
          <!-- <div class="relative flex items-center rounded-full bg-surface-container-low px-4 py-2.5 shadow-sm">
            <span class="absolute left-4 text-slate-400">
              <i class="pi pi-search"></i>
            </span>
            <InputText v-model="searchQuery" placeholder="Cari di Kabita..." unstyled @keyup.enter="handleSearch"
              class="w-full border-0 bg-transparent pl-10 pr-4 text-sm text-slate-700 outline-none ring-0 shadow-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0" />
          </div> -->
          <div class="relative">
            <Button icon="pi pi-shopping-cart" label="Keranjang" text aria-label="Buka keranjang belanja"
              class="px-2! sm:px-3!" @click="router.push('/cart')" />
            <span v-if="cartItemCount"
              class="absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-semibold text-white">
              {{ cartItemCount }}
            </span>
          </div>
          <Button icon="pi pi-bell" text aria-label="Notifikasi" />
          <template v-if="isLoggedIn">
            <Button label="Profil" icon="pi pi-user" text @click="router.push('/profile/account')" />
            <Button label="Keluar" icon="pi pi-sign-out" text severity="secondary" @click="handleLogout" />
          </template>
          <template v-else>
            <Button label="Masuk" text severity="secondary" @click="router.push('/login')" />
            <Button label="Daftar" severity="primary" @click="router.push('/register')" />
          </template>
        </div>
      </div>

      <!-- Mobile header -->
      <div class="flex items-center justify-between gap-3 lg:hidden">
        <div class="flex items-center gap-3">
          <Button icon="pi pi-bars" text aria-label="Buka menu navigasi" class="shrink-0 !p-2"
            @click="isMenuOpen = !isMenuOpen" />
          <router-link to="/" class="text-lg font-bold text-primary whitespace-nowrap">Kabita</router-link>
        </div>

        <div class="flex items-center gap-1">
          <Button icon="pi pi-search" text aria-label="Cari" class="shrink-0 !p-2"
            @click="isMobileSearchOpen = !isMobileSearchOpen" />
          <div class="relative shrink-0">
            <Button icon="pi pi-shopping-cart" text aria-label="Buka keranjang belanja" class="!p-2"
              @click="router.push('/cart')" />
            <span v-if="cartItemCount"
              class="absolute -top-0.5 -right-0.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-semibold text-white">
              {{ cartItemCount }}
            </span>
          </div>
          <Button icon="pi pi-bell" text aria-label="Notifikasi" class="shrink-0 !p-2" />
          <template v-if="isLoggedIn">
            <Button icon="pi pi-user" text aria-label="Profil" class="shrink-0 !p-2"
              @click="router.push('/profile/account')" />
            <Button icon="pi pi-sign-out" text severity="secondary" aria-label="Keluar" class="shrink-0 !p-2"
              @click="handleLogout" />
          </template>
          <template v-else>
            <Button icon="pi pi-sign-in" text severity="secondary" aria-label="Masuk" class="shrink-0 !p-2"
              @click="router.push('/login')" />
            <Button icon="pi pi-user-plus" severity="primary" aria-label="Daftar" class="shrink-0 !p-2"
              @click="router.push('/register')" />
          </template>
        </div>
      </div>

      <!-- Mobile search bar (collapsible) -->
      <Transition name="slide">
        <div v-if="isMobileSearchOpen" class="lg:hidden mt-3">
          <div class="relative flex items-center rounded-full bg-surface-container-low px-4 py-2.5 shadow-sm">
            <span class="absolute left-4 text-slate-400">
              <i class="pi pi-search"></i>
            </span>
            <!-- <input ref="searchInputRef" v-model="searchQuery" type="text" placeholder="Cari di Kabita..."
            class="w-full border-0 bg-transparent pl-10 pr-10 text-sm text-slate-700 outline-none ring-0 shadow-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0"
            @keyup.enter="handleSearch" /> -->
            <button class="absolute right-3 text-slate-400 hover:text-slate-600" aria-label="Tutup pencarian"
              @click="closeMobileSearch">
              <i class="pi pi-times text-sm"></i>
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Mobile nav menu (dropdown) -->
    <Transition name="slide">
      <nav v-if="isMenuOpen" class="lg:hidden border-t border-slate-100 bg-[#faf8ff]">
        <div class="container max-w-7xl mx-auto px-4 py-3 flex flex-col gap-1">
          <router-link to="/" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
            @click="closeMenu">Beranda</router-link>
          <router-link to="/produk" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
            @click="closeMenu">Kategori & Produk</router-link>
          <router-link to="/tentang-kami" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
            @click="closeMenu">Tentang Kami</router-link>
          <router-link to="/bantuan" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
            @click="closeMenu">Bantuan</router-link>
          <router-link to="/cart" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
            @click="closeMenu">
            Keranjang <span v-if="cartItemCount" class="text-slate-400">({{ cartItemCount }})</span>
          </router-link>
          <template v-if="isLoggedIn">
            <router-link to="/profile/account" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
              @click="closeMenu">Profil & Pesanan</router-link>
            <button type="button" class="rounded-lg px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50"
              @click="handleLogout">Keluar</button>
          </template>
          <template v-else>
            <router-link to="/login" class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
              @click="closeMenu">Masuk</router-link>
            <router-link to="/register" class="rounded-lg px-3 py-2 text-sm font-medium text-primary hover:bg-primary/5"
              @click="closeMenu">Daftar</router-link>
          </template>
        </div>
      </nav>
    </Transition>
  </header>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  max-height: 0;
}

.slide-enter-to,
.slide-leave-from {
  opacity: 1;
  max-height: 500px;
}
</style>
