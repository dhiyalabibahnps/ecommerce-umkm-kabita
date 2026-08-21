<script setup lang="ts">
import AppFooter from '@/components/layout/AppFooter.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import GlobalChatDialog from '@/components/chat/GlobalChatDialog.vue'
import Toast from 'primevue/toast'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Cek apakah halaman seller atau admin
const isSellerOrAdminPage = computed(() => {
  return route.path.startsWith('/seller') || route.path.startsWith('/admin') || Boolean(route.meta.hideHeaderFooter)
})
</script>

<template>
  <div :class="[isSellerOrAdminPage ? 'h-screen w-screen overflow-hidden' : 'min-h-screen flex flex-col bg-slate-50']">
    <AppHeader v-if="!isSellerOrAdminPage" />

    <main :class="[isSellerOrAdminPage ? 'h-full w-full overflow-hidden' : 'flex-1 pb-6']">
      <RouterView />
    </main>

    <AppFooter v-if="!isSellerOrAdminPage" />

    <GlobalChatDialog />

    <Toast position="bottom-right" />
  </div>
</template>