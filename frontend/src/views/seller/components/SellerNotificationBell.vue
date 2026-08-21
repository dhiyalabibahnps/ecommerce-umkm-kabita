<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import { notificationService } from '@/services/notificationService';
import { useChatStore } from '@/stores/chat';
import type { AppNotification } from '@/types';

const router = useRouter();
const chatStore = useChatStore();

const isOpen = ref(false);
const isLoading = ref(false);
const notifications = ref<AppNotification[]>([]);
const unreadCount = ref<number>(0);
const activeTab = ref<'all' | 'order' | 'chat'>('all');

let pollInterval: ReturnType<typeof setInterval> | null = null;

const filteredNotifications = computed(() => {
  if (activeTab.value === 'all') return notifications.value;
  return notifications.value.filter((n) => n.type === activeTab.value);
});

const fetchNotifications = async (silent = false) => {
  if (!silent) isLoading.value = true;
  try {
    const res = await notificationService.getNotifications({ limit: 30 });
    notifications.value = res.data;
    unreadCount.value = res.meta.unread_count ?? 0;
  } catch (err) {
    // Silent catch on poll failure
  } finally {
    if (!silent) isLoading.value = false;
  }
};

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    fetchNotifications();
  }
};

const closeDropdown = () => {
  isOpen.value = false;
};

const handleMarkAllRead = async () => {
  try {
    await notificationService.markAllAsRead();
    notifications.value.forEach((n) => (n.is_read = true));
    unreadCount.value = 0;
  } catch (err) {
    // ignore
  }
};

const handleNotificationClick = async (notif: AppNotification) => {
  if (!notif.is_read) {
    notif.is_read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    notificationService.markAsRead(notif.id).catch(() => {});
  }

  isOpen.value = false;

  // Direct chat modal activation for chat/message notifications
  if (notif.type === 'chat') {
    if (notif.data?.conversation_id) {
      chatStore.openConversation(
        Number(notif.data.conversation_id),
        notif.data.sender_name || null,
        notif.data.order_number || null,
        notif.data.order_id ? Number(notif.data.order_id) : null,
        notif.data.shop_id ? Number(notif.data.shop_id) : null,
        notif.data.product_name || null,
        'seller'
      );
    } else if (notif.data?.order_id) {
      chatStore.openOrderChat(
        Number(notif.data.order_id),
        notif.data.order_number || null,
        notif.data.sender_name || null,
        'seller'
      );
    } else if (notif.data?.shop_id) {
      chatStore.openShopChat(
        Number(notif.data.shop_id),
        notif.data.sender_name || null,
        notif.data.product_name || null
      );
    }
    return;
  }

  // Non-chat notifications route to respective detail pages
  const targetUrl = notif.data?.url;
  if (targetUrl) {
    router.push(targetUrl);
  } else if (notif.type === 'order' && notif.data?.order_id) {
    router.push(`/seller/pesanan/${notif.data.order_id}`);
  }
};

const getIcon = (type: string) => {
  switch (type) {
    case 'chat':
      return 'pi pi-comments text-blue-600 bg-blue-100';
    case 'order':
      return 'pi pi-shopping-bag text-emerald-600 bg-emerald-100';
    case 'product':
      return 'pi pi-box text-purple-600 bg-purple-100';
    default:
      return 'pi pi-bell text-amber-600 bg-amber-100';
  }
};

onMounted(() => {
  fetchNotifications(true);
  pollInterval = setInterval(() => {
    fetchNotifications(true);
  }, 10000);
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
});
</script>

<template>
  <div class="relative inline-block text-left">
    <!-- Bell Button -->
    <button
      type="button"
      @click="toggleDropdown"
      class="relative flex items-center justify-center p-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20"
      aria-label="Lihat Notifikasi"
    >
      <i class="pi pi-bell text-lg"></i>
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white shadow-sm ring-2 ring-white animate-pulse"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Overlay backdrop for click-outside -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="closeDropdown"
    ></div>

    <!-- Notification Dropdown Panel -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl border border-slate-200/80 z-50 overflow-hidden transform transition-all duration-200"
    >
      <!-- Header -->
      <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2">
          <h3 class="font-bold text-slate-900 text-base">Notifikasi</h3>
          <span
            v-if="unreadCount > 0"
            class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
          >
            {{ unreadCount }} Baru
          </span>
        </div>
        <button
          v-if="unreadCount > 0"
          type="button"
          @click="handleMarkAllRead"
          class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors"
        >
          Tandai semua dibaca
        </button>
      </div>

      <!-- Filter Tabs -->
      <div class="flex border-b border-slate-100 bg-white px-2 pt-1 gap-1 text-xs font-medium text-slate-600">
        <button
          type="button"
          @click="activeTab = 'all'"
          :class="[
            'px-3 py-2 border-b-2 font-semibold transition-colors',
            activeTab === 'all'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          Semua
        </button>
        <button
          type="button"
          @click="activeTab = 'order'"
          :class="[
            'px-3 py-2 border-b-2 font-semibold transition-colors',
            activeTab === 'order'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          Pesanan
        </button>
        <button
          type="button"
          @click="activeTab = 'chat'"
          :class="[
            'px-3 py-2 border-b-2 font-semibold transition-colors',
            activeTab === 'chat'
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-slate-500 hover:text-slate-800'
          ]"
        >
          Pesan
        </button>
      </div>

      <!-- Notification List -->
      <div class="max-h-96 overflow-y-auto divide-y divide-slate-100 overscroll-contain">
        <div v-if="isLoading && notifications.length === 0" class="py-12 text-center text-slate-400">
          <ProgressSpinner style="width: 32px; height: 32px" strokeWidth="4" />
          <p class="text-xs mt-2">Memuat notifikasi...</p>
        </div>

        <div
          v-else-if="filteredNotifications.length === 0"
          class="py-12 px-4 text-center text-slate-500"
        >
          <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
            <i class="pi pi-bell-slash text-xl"></i>
          </div>
          <p class="text-sm font-semibold text-slate-700">Belum ada notifikasi</p>
          <p class="text-xs text-slate-400 mt-0.5">Notifikasi baru akan muncul di sini.</p>
        </div>

        <div
          v-for="notif in filteredNotifications"
          :key="notif.id"
          @click="handleNotificationClick(notif)"
          :class="[
            'flex items-start gap-3.5 p-3.5 cursor-pointer transition-colors',
            notif.is_read ? 'bg-white hover:bg-slate-50' : 'bg-blue-50/50 hover:bg-blue-50/80'
          ]"
        >
          <!-- Icon -->
          <div
            :class="[
              'w-9 h-9 rounded-full flex items-center justify-center shrink-0 text-sm mt-0.5',
              getIcon(notif.type)
            ]"
          >
            <i :class="notif.type === 'chat' ? 'pi pi-comment' : notif.type === 'order' ? 'pi pi-shopping-bag' : 'pi pi-bell'"></i>
          </div>

          <!-- Content -->
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-1">
              <h4
                :class="[
                  'text-xs leading-snug truncate',
                  notif.is_read ? 'font-medium text-slate-800' : 'font-bold text-slate-900'
                ]"
              >
                {{ notif.title }}
              </h4>
              <span class="text-[10px] text-slate-400 shrink-0">{{ notif.time_ago || 'Baru saja' }}</span>
            </div>
            <p class="text-xs text-slate-600 line-clamp-2 mt-1 leading-relaxed">
              {{ notif.message }}
            </p>
          </div>

          <!-- Unread Dot -->
          <div v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-blue-600 shrink-0 self-center"></div>
        </div>
      </div>
    </div>
  </div>
</template>
