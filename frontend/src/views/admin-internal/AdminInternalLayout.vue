<script setup lang="ts">
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { User } from '@/types/entities'

interface Props {
  slug?: string
}

const props = withDefaults(defineProps<Props>(), {
  slug: 'dashboard'
})

const route = useRoute()
const router = useRouter()
const isLoading = ref(true)

// Data Admin Aktif
const currentAdmin = ref<Partial<User>>({
  id: 1,
  name: 'Super Admin Kabita',
  email: 'admin@kabita.com',
  role: 'admin',
  status: 'active'
})

// Menu Navigasi khusus Admin Internal
const sidebarMenus = ref([
  { label: 'Dashboard', slug: 'dashboard', path: '/admin/dashboard', icon: 'pi pi-th-large' },
  { label: 'Kelola Kategori', slug: 'kategori', path: '/admin/kategori', icon: 'pi pi-tags' },
  { label: 'Kelola Toko', slug: 'toko', path: '/admin/toko', icon: 'pi pi-shop' },
  { label: 'Kelola User', slug: 'user', path: '/admin/user', icon: 'pi pi-users' },
  { label: 'Verifikasi', slug: 'verifikasi', path: '/admin/verifikasi', icon: 'pi pi-verified' },
  { label: 'Transaksi', slug: 'transaksi', path: '/admin/transaksi', icon: 'pi pi-receipt' },
  { label: 'Analitik Platform', slug: 'analitik', path: '/admin/analitik', icon: 'pi pi-chart-bar' },
  { label: 'Pengaturan', slug: 'pengaturan', path: '/admin/pengaturan', icon: 'pi pi-cog' }
])

// Menentukan menu aktif berdasarkan prop slug atau route path
const activeSlug = computed(() => {
  if (props.slug) return props.slug
  const currentPath = route.path
  const foundMenu = sidebarMenus.value.find((menu) => currentPath.includes(menu.slug))
  return foundMenu ? foundMenu.slug : 'dashboard'
})

const currentTitle = computed(() => {
  const activeMenu = sidebarMenus.value.find((m) => m.slug === activeSlug.value)
  return activeMenu ? activeMenu.label : 'Admin Internal'
})

const navigateTo = (path: string) => {
  router.push(path)
}

onMounted(() => {
  // Simulasi loading inisialisasi data admin
  setTimeout(() => {
    isLoading.value = false
  }, 400)
})
</script>

<template>
  <div class="flex min-h-screen bg-slate-100 font-sans text-slate-800">
    <aside
      class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between shrink-0 shadow-xl border-r border-slate-800">
      <div>
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800 bg-slate-950/50">
          <div
            class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
            K
          </div>
          <div>
            <h1 class="text-base font-bold text-white tracking-wide leading-tight">Kabita Admin</h1>
            <p class="text-[10px] text-blue-400 font-medium tracking-wider uppercase">Internal System</p>
          </div>
        </div>

        <nav class="p-4 space-y-1.5">
          <div class="px-3 py-2 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
            Menu Utama
          </div>
          <button v-for="menu in sidebarMenus" :key="menu.slug" @click="navigateTo(menu.path)"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
            :class="[
              activeSlug === menu.slug
                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold'
                : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
            ]">
            <i :class="[menu.icon, 'text-lg', activeSlug === menu.slug ? 'text-white' : 'text-slate-400']"></i>
            <span>{{ menu.label }}</span>
          </button>
        </nav>
      </div>

      <div class="p-4 border-t border-slate-800 bg-slate-950/30">
        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-800/50">
          <div class="flex items-center gap-2.5 overflow-hidden">
            <div
              class="w-8 h-8 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-semibold text-xs border border-blue-500/30">
              AD
            </div>
            <div class="truncate">
              <p class="text-xs font-medium text-slate-200 truncate">{{ currentAdmin.name }}</p>
              <p class="text-[10px] text-slate-400 truncate">{{ currentAdmin.email }}</p>
            </div>
          </div>
          <Button icon="pi pi-sign-out" text rounded severity="secondary"
            class="text-slate-400! hover:text-red-400! w-8! h-8! p-0!" v-tooltip.top="'Keluar'" />
        </div>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header
        class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between shrink-0 shadow-xs z-10">
        <div>
          <h2 class="text-xl font-bold text-slate-800 tracking-tight">{{ currentTitle }}</h2>
        </div>

        <div class="flex items-center gap-4">
          <div class="relative w-64">
            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <InputText placeholder="Cari data sistem..."
              class="w-full pl-10! pr-4! py-2! bg-slate-50! border-slate-200! rounded-full! text-sm! focus:bg-white! focus:ring-2! focus:ring-blue-500/20!" />
          </div>

          <Button icon="pi pi-bell" severity="secondary" text rounded
            class="relative! text-slate-600! hover:bg-slate-100!">
            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"></span>
          </Button>

          <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
            <template v-if="isLoading">
              <div class="w-8 h-8 rounded-full bg-slate-200 animate-pulse"></div>
              <div class="w-20 h-4 bg-slate-200 rounded animate-pulse"></div>
            </template>
            <template v-else>
              <div
                class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                SA
              </div>
              <div class="flex flex-col">
                <span class="text-xs font-semibold text-slate-800 leading-none">{{ currentAdmin.name }}</span>
                <span class="text-[10px] text-blue-600 font-semibold uppercase mt-0.5">Admin Internal</span>
              </div>
            </template>
          </div>
        </div>
      </header>

      <main class="flex-1 p-8 overflow-y-auto bg-slate-50/50">
        <slot>
          <router-view />
        </slot>
      </main>
    </div>
  </div>
</template>