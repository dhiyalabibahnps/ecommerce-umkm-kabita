<script setup lang="ts">
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

import type { Shop } from '@/types/entities'
import SellerDashboardView from './page/SellerDashboardView.vue'

const props = withDefaults(
  defineProps<{
    slug?: string
  }>(),
  {
    slug: 'dashboard'
  }
)

const route = useRoute()
const isLoading = ref(true)

const currentShop = ref<Partial<Shop>>({
  id: 1,
  name: '',
  logo: ''
})

const sidebarMenus = ref([
  { label: 'Dashboard', slug: 'dashboard', icon: 'pi pi-th-large' },
  { label: 'Produk', slug: 'produk', icon: 'pi pi-box' },
  { label: 'Pesanan', slug: 'pesanan', icon: 'pi pi-shopping-bag' },
  { label: 'Profil Toko', slug: 'profil-toko', icon: 'pi pi-shop' },
  { label: 'Analitik', slug: 'analitik', icon: 'pi pi-chart-bar' },
  { label: 'Pengaturan', slug: 'pengaturan', icon: 'pi pi-cog' }
])

const activeSlug = computed(() => props.slug || (route.params.slug as string) || 'dashboard')

const activeComponent = computed(() => {
  switch (activeSlug.value) {
    case 'dashboard':
      return SellerDashboardView
    default:
      return SellerDashboardView
  }
})

const headerInfo = computed(() => {
  switch (activeSlug.value) {
    case 'analitik':
      return { title: 'Analitik Penjualan', subtitle: 'Pantau statistik dan statistik performa toko Anda.' }
    case 'produk':
      return { title: 'Kelola Produk', subtitle: 'Daftar dan stok produk yang tersedia di toko Anda.' }
    case 'pesanan':
      return { title: 'Daftar Pesanan', subtitle: 'Kelola transaksi dan pengiriman ke pembeli.' }
    case 'profil-toko':
      return { title: 'Profil Toko', subtitle: 'Pengaturan identitas dan tampilan toko Anda.' }
    case 'pengaturan':
      return { title: 'Pengaturan System', subtitle: 'Konfigurasi akun dan preference seller center.' }
    default:
      return { title: 'Ringkasan Toko', subtitle: 'Ringkasan performa toko Anda waktu nyata.' }
  }
})

const fetchSellerData = async () => {
  isLoading.value = true
  setTimeout(() => {
    currentShop.value = {
      id: 1,
      name: 'Toko Saya',
      logo: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=250'
    }
    isLoading.value = false
  }, 800)
}

onMounted(() => {
  fetchSellerData()
})
</script>

<template>
  <div class="h-screen w-full overflow-hidden bg-[#F8F9FA] flex font-sans text-slate-800">

    <aside
      class="w-64 h-full bg-white border-r border-slate-200 flex flex-col justify-between shrink-0 select-none z-20">
      <div class="flex-1 overflow-y-auto">
        <div class="p-6 text-center border-b border-slate-100">
          <div class="relative w-20 h-20 mx-auto mb-3">
            <template v-if="isLoading">
              <div class="w-20 h-20 rounded-full bg-slate-200 animate-pulse mx-auto"></div>
            </template>
            <template v-else>
              <img
                :src="currentShop.logo || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=250'"
                alt="Seller Profile"
                class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-white ring-2 ring-blue-500/20" />
            </template>
          </div>
          <h2 class="text-xl font-bold text-blue-600 leading-tight">Seller Center</h2>
          <p class="text-xs text-slate-400 mt-0.5">Shop Dashboard</p>
        </div>

        <nav class="p-4 space-y-1.5">
          <router-link v-for="menu in sidebarMenus" :key="menu.slug" :to="`/seller/${menu.slug}`" :class="[
            'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200',
            activeSlug === menu.slug
              ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 font-semibold'
              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
          ]">
            <i :class="[menu.icon, 'text-lg']"></i>
            <span>{{ menu.label }}</span>
          </router-link>
        </nav>
      </div>

      <div class="p-4 border-t border-slate-100 space-y-2 shrink-0 bg-white">
        <router-link to="/seller/help"
          class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition-colors">
          <i class="pi pi-question-circle text-lg"></i>
          <span>Pusat Bantuan</span>
        </router-link>

        <router-link to="/logout"
          class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition-colors">
          <i class="pi pi-sign-out text-lg"></i>
          <span>Keluar</span>
        </router-link>

        <div class="pt-2">
          <Button label="Tambah Produk Baru" icon="pi pi-plus"
            class="w-full bg-blue-600! hover:bg-blue-700! border-none! py-3! rounded-xl! text-sm! font-semibold! shadow-sm!" />
        </div>
      </div>
    </aside>

    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden">

      <header class="h-20 bg-white border-b border-slate-200/80 px-8 flex items-center justify-between shrink-0 z-10">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">{{ headerInfo.title }}</h1>
          <p class="text-xs text-slate-500 mt-0.5">{{ headerInfo.subtitle }}</p>
        </div>

        <div class="flex items-center gap-4">
          <span class="relative inline-flex">
            <Button icon="pi pi-bell" text rounded class="!p-2.5 !bg-slate-100 hover:!bg-slate-200 !text-slate-600" />
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
          </span>

          <div class="relative w-72">
            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <InputText placeholder="Search system..."
              class="w-full! pl-10! pr-4! py-2! bg-slate-50/80! border-slate-200! rounded-full! text-sm! focus:bg-white! focus:ring-2! focus:ring-blue-500/20!" />
          </div>

          <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
            <template v-if="isLoading">
              <div class="w-8 h-8 rounded-full bg-slate-200 animate-pulse"></div>
              <div class="w-20 h-4 bg-slate-200 rounded animate-pulse"></div>
            </template>
            <template v-else>
              <img
                :src="currentShop.logo || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=100'"
                alt="Shop Logo" class="w-8 h-8 rounded-full object-cover" />
              <span class="text-xs font-semibold text-slate-700">{{ currentShop.name }}</span>
            </template>
          </div>
        </div>
      </header>

      <main class="flex-1 p-8 overflow-y-auto">
        <div v-if="isLoading" class="h-full min-h-100 flex flex-col items-center justify-center gap-3 text-slate-500">
          <ProgressSpinner style="width: 48px; height: 48px" strokeWidth="4" animationDuration=".8s" />
          <p class="text-sm font-medium">Memuat Dashboard Seller...</p>
        </div>

        <component v-else :is="activeComponent" />
      </main>

    </div>

  </div>
</template>