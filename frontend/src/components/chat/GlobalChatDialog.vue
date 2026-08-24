<script setup lang="ts">
import { chatService } from '@/services/chatService';
import { useAuthStore } from '@/stores/auth';
import { useChatStore } from '@/stores/chat';
import type { Conversation } from '@/types';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const authStore = useAuthStore();
const chatStore = useChatStore();

const conversation = ref<Conversation | null>(null);
const messageText = ref('');
const isLoading = ref(false);
const isSending = ref(false);
const errorMessage = ref('');
const messagesContainer = ref<HTMLElement | null>(null);

let pollInterval: ReturnType<typeof setInterval> | null = null;

const currentUserId = computed(() => authStore.user?.id);

const isSeller = computed(() => {
  if (chatStore.role === 'seller') return true;
  if (chatStore.role === 'buyer') return false;
  if (authStore.user?.role === 'seller') return true;
  if (conversation.value && conversation.value.seller_id === currentUserId.value) return true;
  return false;
});

// Display titles & subtitles
const displayTitle = computed(() => {
  if (chatStore.targetName) {
    return chatStore.targetName;
  }
  if (conversation.value) {
    if (conversation.value.seller_id === currentUserId.value) {
      return conversation.value.buyer?.name || 'Pembeli';
    } else {
      return conversation.value.shop?.name || conversation.value.seller?.name || 'Penjual';
    }
  }
  return 'Percakapan';
});

const displaySubtitle = computed(() => {
  const orderNum = chatStore.orderNumber || conversation.value?.order?.order_number;
  if (orderNum) {
    return `Pesanan ${orderNum}`;
  }
  if (chatStore.productName) {
    return `Tanya Produk: ${chatStore.productName}`;
  }
  return 'Chat Langsung';
});

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const loadMessages = async (silent = false) => {
  if (!chatStore.isOpen) return;

  if (!silent) isLoading.value = true;
  errorMessage.value = '';

  try {
    let data: Conversation | null = null;

    if (chatStore.conversationId) {
      data = await chatService.getConversationById(chatStore.conversationId);
    } else if (chatStore.orderId) {
      data = await chatService.getOrderConversation(chatStore.orderId);
    } else if (chatStore.shopId) {
      data = await chatService.getShopConversation(chatStore.shopId);
    }

    if (data) {
      const prevCount = conversation.value?.messages?.length ?? 0;
      conversation.value = data;

      if (!silent || (data.messages && data.messages.length > prevCount)) {
        scrollToBottom();
      }
    }
  } catch (err: any) {
    if (!silent) {
      errorMessage.value = err.response?.data?.message || 'Gagal memuat pesan percakapan.';
    }
  } finally {
    if (!silent) isLoading.value = false;
  }
};

const startPolling = () => {
  stopPolling();
  pollInterval = setInterval(() => {
    loadMessages(true);
  }, 4000);
};

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
};

const handleSendMessage = async () => {
  const text = messageText.value.trim();
  if (!text || isSending.value) return;

  isSending.value = true;
  try {
    if (conversation.value?.id) {
      const newMsg = await chatService.sendMessageByConversationId(conversation.value.id, text);
      if (!conversation.value.messages) {
        conversation.value.messages = [];
      }
      conversation.value.messages.push(newMsg);
    } else if (chatStore.conversationId) {
      const newMsg = await chatService.sendMessageByConversationId(chatStore.conversationId, text);
      if (conversation.value) {
        conversation.value.messages.push(newMsg);
      } else {
        await loadMessages(false);
      }
    } else if (chatStore.orderId) {
      const newMsg = await chatService.sendOrderMessage(chatStore.orderId, text);
      if (conversation.value) {
        conversation.value.messages.push(newMsg);
      } else {
        await loadMessages(false);
      }
    } else if (chatStore.shopId) {
      const newMsg = await chatService.sendShopMessage(chatStore.shopId, text, chatStore.productName || undefined);
      if (conversation.value) {
        conversation.value.messages.push(newMsg);
      } else {
        await loadMessages(false);
      }
    }

    messageText.value = '';
    scrollToBottom();
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal mengirim pesan.';
  } finally {
    isSending.value = false;
  }
};

const quickReplies = computed(() => {
  if (isSeller.value) {
    const list = [
      'Pesanan sedang disiapkan',
      'Pesanan sudah dikirim',
      'Ada yang bisa dibantu?',
    ];
    if (chatStore.productName) {
      list.push(`Info produk ${chatStore.productName} ready`);
    } else {
      list.push('Halo kak, terima kasih sudah berbelanja');
    }
    return list;
  } else {
    const list = [
      'Halo kak, apakah produk ready stok?',
      'Halo kak, bagaimana status pesanan saya?',
      'Ada yang bisa dibantu?',
    ];
    if (chatStore.orderNumber || conversation.value?.order?.order_number) {
      list.push('Mohon segera diproses ya kak');
    } else {
      list.push('Halo kak, salam kenal!');
    }
    return list;
  }
});

const sendQuickReply = (text: string) => {
  messageText.value = text;
  handleSendMessage();
};

const formatTime = (dateStr?: string) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return '';
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

// Watchers
watch(
  () => chatStore.isOpen,
  (open) => {
    if (open) {
      conversation.value = null;
      messageText.value = '';
      loadMessages(false);
      startPolling();
    } else {
      stopPolling();
    }
  }
);

watch(
  () => [chatStore.conversationId, chatStore.orderId, chatStore.shopId],
  () => {
    if (chatStore.isOpen) {
      conversation.value = null;
      loadMessages(false);
    }
  }
);

onMounted(() => {
  if (chatStore.isOpen) {
    loadMessages(false);
    startPolling();
  }
});

onUnmounted(() => {
  stopPolling();
});
</script>

<template>
  <Dialog :visible="chatStore.isOpen" @update:visible="(val) => !val && chatStore.closeChat()" modal :draggable="false"
    :dismissableMask="true" :showHeader="false" :style="{ width: 'min(540px, 95vw)', maxWidth: '540px' }"
    :breakpoints="{ '640px': '95vw' }" class="chat-dialog-modal shadow-2xl" :pt="{
      root: { class: 'border-0 rounded-2xl overflow-hidden' },
      content: { class: 'p-0! overflow-hidden rounded-2xl' },
    }">
    <!-- Dialog Body Container -->
    <div class="flex flex-col h-130 max-h-[85vh] bg-slate-50 w-full overflow-hidden">
      <!-- Custom Header -->
      <div
        class="w-full bg-linear-to-r from-blue-700 to-indigo-800 text-white px-5 py-3.5 flex items-center justify-between shadow-xs shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <div
            class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-xs flex items-center justify-center text-white shrink-0 font-bold border border-white/20">
            <i class="pi pi-comments text-lg"></i>
          </div>
          <div class="min-w-0">
            <h3 class="font-semibold text-sm sm:text-base text-white truncate leading-tight flex items-center gap-2">
              <span>{{ displayTitle }}</span>
            </h3>
            <div class="flex items-center gap-2 mt-0.5">
              <span class="inline-flex items-center gap-1 text-[11px] text-blue-100 font-medium truncate">
                <i class="pi pi-tag text-[9px]"></i>
                {{ displaySubtitle }}
              </span>
            </div>
          </div>
        </div>
        <button @click="chatStore.closeChat()"
          class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer shrink-0 ml-2"
          aria-label="Tutup">
          <i class="pi pi-times text-xs"></i>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex-1 flex flex-col items-center justify-center p-6 text-slate-400 gap-2">
        <ProgressSpinner style="width: 38px; height: 38px" strokeWidth="4" />
        <span class="text-xs font-medium">Memuat pesan...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage && !conversation?.messages?.length"
        class="flex-1 flex flex-col items-center justify-center p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-2">
          <i class="pi pi-exclamation-triangle text-xl"></i>
        </div>
        <p class="text-xs font-semibold text-slate-700">{{ errorMessage }}</p>
        <Button label="Coba Lagi" icon="pi pi-refresh" size="small" class="mt-3 text-xs! py-1.5! px-3!"
          @click="loadMessages(false)" />
      </div>

      <!-- Messages Timeline -->
      <div v-else ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3">
        <!-- Empty State -->
        <div v-if="!conversation?.messages?.length"
          class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400">
          <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-2">
            <i class="pi pi-comments text-xl"></i>
          </div>
          <p class="text-xs font-semibold text-slate-700">Belum Ada Pesan</p>
          <p class="text-[11px] text-slate-400 mt-1 w-full">
            Mulai percakapan dengan mengetik pesan atau memilih saran cepat di bawah ini.
          </p>
        </div>

        <!-- Messages Bubble List -->
        <div v-else v-for="msg in conversation.messages" :key="msg.id" class="flex flex-col"
          :class="msg.sender_id === currentUserId ? 'items-end' : 'items-start'">
          <div class="text-[10px] text-slate-400 mb-0.5 px-1 font-medium">
            {{ msg.sender_id === currentUserId ? 'Anda' : (msg.sender_name || (chatStore.role === 'seller' ? 'Pembeli' :
              'Penjual')) }}
          </div>
          <div class="max-w-[82%] rounded-2xl px-3.5 py-2 text-xs shadow-xs" :class="msg.sender_id === currentUserId
            ? 'bg-blue-600 text-white rounded-tr-none'
            : 'bg-white border border-slate-200 text-slate-800 rounded-tl-none'
            ">
            <p class="whitespace-pre-wrap break-words leading-relaxed">{{ msg.message }}</p>
            <div class="text-[9px] mt-1 text-right"
              :class="msg.sender_id === currentUserId ? 'text-blue-100' : 'text-slate-400'">
              {{ formatTime(msg.created_at) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Reply Suggestions Strip (Separated from Message History) -->
      <div v-if="!isLoading && !errorMessage"
        class="px-3 pt-2 pb-1.5 bg-slate-50/90 border-t border-slate-200/70 flex flex-wrap gap-1.5 items-center shrink-0 max-w-full">
        <span
          class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1 flex items-center gap-1">
          <i class="pi pi-bolt text-[10px] text-amber-500"></i>
          Saran:
        </span>
        <button v-for="(reply, idx) in quickReplies" :key="idx" type="button" @click="sendQuickReply(reply)"
          :disabled="isSending"
          class="text-[11px] font-medium leading-tight whitespace-nowrap px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-700 transition-colors shadow-2xs cursor-pointer disabled:opacity-50 inline-flex items-center">
          {{ reply }}
        </button>
      </div>

      <!-- Message Input Form Footer -->
      <form @submit.prevent="handleSendMessage"
        class="p-2.5 sm:p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
        <InputText v-model="messageText" placeholder="Tulis pesan Anda..."
          class="flex-1 text-xs sm:text-sm py-2 px-3 rounded-xl border-slate-200 focus:border-blue-500 shadow-2xs"
          :disabled="isSending" @keydown.enter.prevent="handleSendMessage" />
        <Button type="submit" icon="pi pi-send" label="Kirim" :loading="isSending"
          :disabled="!messageText.trim() || isSending"
          class="bg-blue-600! border-blue-600! text-xs! sm:text-sm! py-2! px-3.5! rounded-xl! shadow-xs hover:bg-blue-700! font-medium!" />
      </form>
    </div>
  </Dialog>
</template>

<style scoped>
:deep(.p-dialog.chat-dialog-modal) {
  width: min(540px, 95vw) !important;
  max-width: 540px !important;
  border-radius: 1rem !important;
  border: 0 !important;
  overflow: hidden !important;
}

:deep(.p-dialog.chat-dialog-modal .p-dialog-content) {
  padding: 0 !important;
  overflow: hidden !important;
  border-radius: 1rem !important;
}
</style>
