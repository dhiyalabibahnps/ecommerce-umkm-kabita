<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import ProfileAccount from './ProfileAccount.vue'
import ProfileAddress from './ProfileAddress.vue'
import ProfileOrders from './ProfileOrders.vue'

const props = withDefaults(
  defineProps<{
    slug?: string
  }>(),
  {
    slug: 'account'
  }
)

const route = useRoute()

// Data Menu Sidebar Dinamis
const sidebarMenus = ref([
  { label: 'Account', slug: 'account', icon: 'pi pi-user' },
  { label: 'Orders', slug: 'orders', icon: 'pi pi-shopping-bag' },
  { label: 'Address', slug: 'address', icon: 'pi pi-map-marker' },
])

// Slug aktif dari props/route
const activeSlug = computed(() => props.slug || (route.params.slug as string) || 'account')

// Switch komponen dinamis
const activeComponent = computed(() => {
  switch (activeSlug.value) {
    case 'orders':
      return ProfileOrders
    case 'address':
      return ProfileAddress
    default:
      return ProfileAccount
  }
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">

      <!-- Sidebar Navigasi (Kiri) -->
      <aside class="lg:col-span-3">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100">
          <!-- Profile Badge Ringkas -->
          <div class="flex items-center gap-3 pb-5 mb-4 border-b border-slate-100">
            <img src="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" alt="Avatar"
              class="w-12 h-12 rounded-full object-cover" />
            <div>
              <h2 class="font-bold text-slate-800 text-sm">Admin Kabita</h2>
              <span class="text-xs text-emerald-600 flex items-center gap-1">
                <i class="pi pi-check-circle text-xs"></i> Terverifikasi
              </span>
            </div>
          </div>

          <!-- Menu Dinamis -->
          <nav class="space-y-1">
            <router-link v-for="menu in sidebarMenus" :key="menu.slug" :to="`/profile/${menu.slug}`" :class="[
              'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors',
              activeSlug === menu.slug
                ? 'bg-blue-600 text-white shadow-sm'
                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
            ]">
              <i :class="menu.icon"></i>
              <span>{{ menu.label }}</span>
            </router-link>
          </nav>
        </div>
      </aside>

      <!-- Main Content (Kanan) -->
      <main class="lg:col-span-9">
        <component :is="activeComponent" />
      </main>

    </div>
  </div>
</template>