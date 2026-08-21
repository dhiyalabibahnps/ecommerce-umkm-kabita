<script setup lang="ts">
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import { chatService } from '@/services/chatService';
import { useAuthStore } from '@/stores/auth';
import type { Conversation, Product, Shop } from '@/types';
import { formatRupiah } from '@/utils/format';

const props = defineProps<{
  visible: boolean;
  product?: Product | null;
  shop?: Shop | null;
}>();

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void;
}>();

const authStore = useAuthStore();
const conversation = ref<Conversation | null>(null);
const messageText = ref('');
const isLoading = ref(true);
const isSending = ref(false);
const errorMessage = ref('');
const messagesContainer = ref<HTMLElement | null>(null);

let pollInterval: ReturnType<typeof setInterval> | null = null;

const currentUserId = computed(() => authStore.user?.id);
const targetShop = computed(() => props.product?.shop || props.shop);

const dialogTitle = computed(() => {
  return `Chat dengan Penjual (${targetShop.value?.name || 'Toko'})`;
});

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const loadMessages = async (silent = false) => {
  if (!targetShop.value?.id) return;
  if (!silent) isLoading.value = true;
  errorMessage.value = '';

  try {
    const data = await chatService.getShopConversation(targetShop.value.id);
    const prevCount = conversation.value?.messages.length ?? 0;
    conversation.value = data;

    if (!silent || (data.messages && data.messages.length > prevCount)) {
      scrollToBottom();
    }
  } catch (err: any) {
    if (!silent) {
      errorMessage.value = err?.response?.data?.message || 'Gagal memuat percakapan.';
    }
  } finally {
    if (!silent) isLoading.value = false;
  }
};

const handleSendMessage = async () => {
  const text = messageText.value.trim();
  if (!text || isSending.value || !targetShop.value?.id) return;

  isSending.value = true;
  try {
    const newMsg = await chatService.sendShopMessage(targetShop.value.id, text, props.product?.name);
    messageText.value = '';
    if (conversation.value) {
      conversation.value.messages.push(newMsg);
    } else {
      await loadMessages(true);
    }
    scrollToBottom();
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'Gagal mengirim pesan.';
  } finally {
    isSending.value = false;
  }
};

const sendProductInquiry = () => {
  if (!props.product) return;
  messageText.value = `Halo, apakah produk "${props.product.name}" ini masih tersedia?`;
  handleSendMessage();
};

const formatTime = (isoString?: string) => {
  if (!isoString) return '';
  const date = new Date(isoString);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

watch(
  () => props.visible,
  (newVal) => {
    if (newVal) {
      loadMessages();
      pollInterval = setInterval(() => {
        loadMessages(true);
      }, 3500);
    } else {
      if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
      }
    }
  }
);

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
});
</script>

<template>
  <Dialog
    :visible="visible"
    @update:visible="emit('update:visible', $event)"
    modal
    :header="dialogTitle"
    :style="{ width: '95vw', maxWidth: '580px' }"
    :closable="true"
    :dismissableMask="true"
    class="chat-dialog-modal"
  >
    <div class="flex flex-col h-[480px] -mx-4 -my-2">
      <!-- Product Snapshot Header -->
      <div v-if="product" class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <img
            :src="product.images?.[0]?.url || 'https://placehold.co/100x100?text=Produk'"
            alt="Product Thumbnail"
            class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0"
          />
          <div class="min-w-0">
            <h4 class="text-xs font-semibold text-slate-900 truncate">{{ product.name }}</h4>
            <p class="text-xs font-bold text-blue-600 mt-0.5">{{ formatRupiah(product.price) }}</p>
          </div>
        </div>
        <button
          type="button"
          @click="sendProductInquiry"
          class="shrink-0 text-xs px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition"
        >
          Tanya Stok
        </button>
      </div>

      <!-- Messages Area -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#f8fafc]"
      >
        <div v-if="isLoading" class="h-full flex flex-col items-center justify-center gap-2 text-slate-400">
          <ProgressSpinner style="width: 32px; height: 32px" strokeWidth="4" />
          <span class="text-xs">Memuat pesan...</span>
        </div>

        <div v-else-if="errorMessage" class="p-3 text-center text-xs text-red-600 bg-red-50 rounded-lg">
          {{ errorMessage }}
        </div>

        <div
          v-else-if="!conversation?.messages || conversation.messages.length === 0"
          class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400"
        >
          <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
            <i class="pi pi-comments text-2xl"></i>
          </div>
          <p class="text-sm font-semibold text-slate-700">Mulai Percakapan</p>
          <p class="text-xs text-slate-400 mt-1 max-w-xs">
            Kirim pesan atau tanyakan detail produk kepada penjual di sini.
          </p>
        </div>

        <template v-else>
          <div
            v-for="msg in conversation.messages"
            :key="msg.id"
            :class="[
              'flex flex-col max-w-[80%]',
              msg.sender_id === currentUserId ? 'ml-auto items-end' : 'mr-auto items-start'
            ]"
          >
            <div
              :class="[
                'px-3.5 py-2.5 rounded-2xl text-sm leading-relaxed shadow-xs',
                msg.sender_id === currentUserId
                  ? 'bg-blue-600 text-white rounded-br-xs'
                  : 'bg-white text-slate-800 border border-slate-200/80 rounded-bl-xs'
              ]"
            >
              <p class="whitespace-pre-wrap break-words">{{ msg.message }}</p>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 px-1">
              {{ formatTime(msg.created_at) }}
            </span>
          </div>
        </template>
      </div>

      <!-- Footer / Input Form -->
      <form
        @submit.prevent="handleSendMessage"
        class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0"
      >
        <InputText
          v-model="messageText"
          placeholder="Ketik pesan Anda..."
          class="flex-1 text-sm py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500/20"
          :disabled="isSending"
        />
        <Button
          type="submit"
          icon="pi pi-send"
          label="Kirim"
          :loading="isSending"
          :disabled="!messageText.trim() || isSending"
          class="!bg-blue-600 hover:!bg-blue-700 !text-white !text-sm !py-2 !px-4 !rounded-xl !border-0"
        />
      </form>
    </div>
  </Dialog>
</template>
