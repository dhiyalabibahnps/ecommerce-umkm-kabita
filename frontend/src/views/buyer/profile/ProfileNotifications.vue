<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'

import { getApiErrorMessage } from '@/services/apiError'
import { notificationService } from '@/services/notificationService'
import { useChatStore } from '@/stores/chat'
import type { AppNotification } from '@/types'

const router = useRouter()
const toast = useToast()
const chatStore = useChatStore()

const notifications = ref<AppNotification[]>([])
const isLoading = ref(true)
const activeType = ref<'all' | 'order' | 'chat' | 'system'>('all')
const isMarkingAll = ref(false)

const filteredNotifications = computed(() => {
  if (activeType.value === 'all') return notifications.value
  return notifications.value.filter((n) => n.type === activeType.value)
})

const unreadCount = computed(() => {
  return notifications.value.filter((n) => !n.is_read).length
})

const loadNotifications = async () => {
  isLoading.value = true
  try {
    const res = await notificationService.list({ limit: 50 })
    notifications.value = res.data
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal memuat notifikasi.'),
      life: 3000,
    })
  } finally {
    isLoading.value = false
  }
}

const handleMarkAllAsRead = async () => {
  if (unreadCount.value === 0) return
  isMarkingAll.value = true
  try {
    await notificationService.markAllAsRead()
    notifications.value = notifications.value.map((n) => ({ ...n, is_read: true }))
    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Semua notifikasi telah ditandai dibaca.',
      life: 2500,
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(error, 'Gagal menandai notifikasi.'),
      life: 3000,
    })
  } finally {
    isMarkingAll.value = false
  }
}

const handleNotificationClick = async (notif: AppNotification) => {
  if (!notif.is_read) {
    try {
      await notificationService.markAsRead(notif.id)
      notif.is_read = true
    } catch {
      // Continue navigating even if marking read fails
    }
  }

  // Direct chat modal activation for message/chat notifications
  if (notif.type === 'chat') {
    if (notif.data?.conversation_id) {
      chatStore.openConversation(
        Number(notif.data.conversation_id),
        notif.data.sender_name || null,
        notif.data.order_number || null,
        notif.data.order_id ? Number(notif.data.order_id) : null,
        notif.data.shop_id ? Number(notif.data.shop_id) : null,
        notif.data.product_name || null,
        'buyer'
      )
    } else if (notif.data?.order_id) {
      chatStore.openOrderChat(
        Number(notif.data.order_id),
        notif.data.order_number || null,
        notif.data.sender_name || null,
        'buyer'
      )
    } else if (notif.data?.shop_id) {
      chatStore.openShopChat(
        Number(notif.data.shop_id),
        notif.data.sender_name || null,
        notif.data.product_name || null
      )
    }
    return
  }

  const url = notif.data?.url
  if (url && typeof url === 'string') {
    router.push(url)
  } else if (notif.data?.order_id) {
    router.push(`/order-detail?id=${notif.data.order_id}`)
  }
}

const getIcon = (type: string) => {
  switch (type) {
    case 'order':
      return 'pi pi-box text-blue-600 bg-blue-50 border border-blue-100'
    case 'chat':
      return 'pi pi-comments text-indigo-600 bg-indigo-50 border border-indigo-100'
    case 'product':
      return 'pi pi-tag text-emerald-600 bg-emerald-50 border border-emerald-100'
    default:
      return 'pi pi-bell text-amber-600 bg-amber-50 border border-amber-100'
  }
}

onMounted(loadNotifications)
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs sm:p-6 lg:p-7">
    <!-- Header -->
    <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Notifikasi</h1>
        <p class="mt-0.5 text-xs text-slate-500">Pembaruan status pesanan, pembayaran, dan pesan toko Anda.</p>
      </div>

      <div class="flex items-center gap-2">
        <Button
          v-if="unreadCount > 0"
          label="Tandai Semua Dibaca"
          severity="secondary"
          size="small"
          text
          class="text-xs! text-blue-600! hover:bg-blue-50!"
          :loading="isMarkingAll"
          @click="handleMarkAllAsRead"
        />
        <Button
          icon="pi pi-refresh"
          size="small"
          severity="secondary"
          outlined
          class="rounded-xl! text-xs!"
          :loading="isLoading"
          @click="loadNotifications"
        />
      </div>
    </div>

    <!-- Filter Type Tabs -->
    <div class="mt-4 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
      <button
        v-for="tab in [
          { label: 'Semua', value: 'all' },
          { label: 'Pesanan', value: 'order' },
          { label: 'Pesan / Chat', value: 'chat' },
          { label: 'Sistem', value: 'system' }
        ]"
        :key="tab.value"
        type="button"
        class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold transition cursor-pointer"
        :class="[
          activeType === tab.value
            ? 'bg-blue-600 text-white shadow-2xs'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'
        ]"
        @click="activeType = tab.value as any"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center gap-3 py-20 text-slate-400">
      <ProgressSpinner style="width: 36px; height: 36px" />
      <span class="text-xs">Memuat notifikasi...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredNotifications.length === 0" class="py-16 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 text-slate-300">
        <i class="pi pi-bell-slash text-2xl"></i>
      </div>
      <h3 class="mt-4 text-sm font-bold text-slate-800">Belum Ada Notifikasi</h3>
      <p class="mt-1 text-xs text-slate-400">Notifikasi terkait pesanan dan pesan akan muncul di sini.</p>
    </div>

    <!-- Notification List -->
    <div v-else class="mt-4 divide-y divide-slate-100">
      <div
        v-for="notif in filteredNotifications"
        :key="notif.id"
        class="flex items-start gap-3.5 rounded-xl p-3.5 transition cursor-pointer"
        :class="[
          notif.is_read
            ? 'bg-white hover:bg-slate-50/70'
            : 'bg-blue-50/40 hover:bg-blue-50/70 border border-blue-100/60 shadow-2xs'
        ]"
        @click="handleNotificationClick(notif)"
      >
        <!-- Icon -->
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm" :class="getIcon(notif.type)">
          <i :class="notif.type === 'chat' ? 'pi pi-comments' : notif.type === 'order' ? 'pi pi-box' : 'pi pi-bell'"></i>
        </div>

        <!-- Content -->
        <div class="min-w-0 flex-1">
          <div class="flex items-center justify-between gap-2">
            <h4 class="text-xs font-bold" :class="notif.is_read ? 'text-slate-800' : 'text-slate-900'">
              {{ notif.title }}
            </h4>
            <span class="text-[10px] text-slate-400 shrink-0 font-mono">
              {{ notif.time_ago || '' }}
            </span>
          </div>
          <p class="mt-0.5 text-xs text-slate-600 leading-relaxed">
            {{ notif.message }}
          </p>
          <div v-if="notif.data?.url || notif.data?.order_id || notif.type === 'chat'" class="mt-1.5 flex items-center gap-1 text-[11px] font-semibold text-blue-600">
            <span>{{ notif.type === 'chat' ? 'Buka pesan' : 'Lihat rincian' }}</span>
            <i class="pi pi-arrow-right text-[9px]"></i>
          </div>
        </div>

        <!-- Unread Indicator Dot -->
        <div v-if="!notif.is_read" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-600"></div>
      </div>
    </div>
  </div>
</template>
