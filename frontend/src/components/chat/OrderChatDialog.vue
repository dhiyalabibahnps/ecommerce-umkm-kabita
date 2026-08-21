<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import { chatService } from '@/services/chatService';
import { useAuthStore } from '@/stores/auth';
import type { Conversation, Order } from '@/types';

const props = defineProps<{
  visible: boolean;
  order: Order;
  role?: 'buyer' | 'seller';
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

const dialogTitle = computed(() => {
  if (props.role === 'seller') {
    return `Chat dengan Pembeli (${props.order.buyer?.name || 'Pembeli'}) - ${props.order.order_number}`;
  }
  return `Chat dengan Penjual (${props.order.shop?.name || 'Toko'}) - ${props.order.order_number}`;
});

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const loadMessages = async (silent = false) => {
  if (!props.order?.id) return;
  if (!silent) isLoading.value = true;
  errorMessage.value = '';

  try {
    const data = await chatService.getOrderConversation(props.order.id);
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
  if (!text || isSending.value || !props.order?.id) return;

  isSending.value = true;
  try {
    const newMsg = await chatService.sendOrderMessage(props.order.id, text);
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
  }
});
</script>

<template>
  <Dialog
    :visible="visible"
    @update:visible="(val) => emit('update:visible', val)"
    modal
    :header="dialogTitle"
    :style="{ width: 'min(560px, 95vw)' }"
    class="chat-dialog"
  >
    <div class="flex flex-col h-[480px] -mx-4 -my-2">
      <!-- Order Quick Info Header -->
      <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs text-slate-600">
        <div class="flex items-center gap-2">
          <i class="pi pi-box text-blue-600"></i>
          <span>Status Pesanan: <strong class="capitalize text-slate-800">{{ order.status.replace('_', ' ') }}</strong></span>
        </div>
        <Button icon="pi pi-refresh" text rounded size="small" class="w-6! h-6! p-0!" @click="() => loadMessages(false)" title="Segarkan Pesan" />
      </div>

      <!-- Messages Area -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/50"
      >
        <div v-if="isLoading" class="flex flex-col items-center justify-center h-full gap-2 text-slate-400">
          <ProgressSpinner style="width: 32px; height: 32px" strokeWidth="4" />
          <span class="text-xs">Memuat percakapan...</span>
        </div>

        <div v-else-if="errorMessage" class="flex flex-col items-center justify-center h-full text-rose-500 text-xs gap-2 p-4 text-center">
          <i class="pi pi-exclamation-circle text-2xl"></i>
          <span>{{ errorMessage }}</span>
          <Button label="Coba Lagi" size="small" text @click="() => loadMessages(false)" />
        </div>

        <div v-else-if="!conversation?.messages || conversation.messages.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 text-xs gap-2">
          <i class="pi pi-comments text-3xl text-slate-300"></i>
          <span>Belum ada pesan. Mulai obrolan dengan mengirim pesan di bawah.</span>
        </div>

        <div
          v-else
          v-for="msg in conversation.messages"
          :key="msg.id"
          class="flex flex-col"
          :class="msg.sender_id === currentUserId ? 'items-end' : 'items-start'"
        >
          <div class="text-[10px] text-slate-400 mb-0.5 px-1 font-medium">
            {{ msg.sender_id === currentUserId ? 'Anda' : (msg.sender_name || (role === 'seller' ? 'Pembeli' : 'Penjual')) }}
          </div>
          <div
            class="max-w-[80%] rounded-2xl px-3.5 py-2 text-xs shadow-xs"
            :class="
              msg.sender_id === currentUserId
                ? 'bg-blue-600 text-white rounded-tr-none'
                : 'bg-white border border-slate-200 text-slate-800 rounded-tl-none'
            "
          >
            <p class="whitespace-pre-wrap break-words leading-relaxed">{{ msg.message }}</p>
            <div
              class="text-[9px] mt-1 text-right"
              :class="msg.sender_id === currentUserId ? 'text-blue-100' : 'text-slate-400'"
            >
              {{ formatTime(msg.created_at) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Message Input Footer -->
      <form
        @submit.prevent="handleSendMessage"
        class="p-3 bg-white border-t border-slate-200 flex items-center gap-2"
      >
        <InputText
          v-model="messageText"
          placeholder="Ketik pesan..."
          class="flex-1 text-xs py-2 px-3 rounded-lg border-slate-200 focus:border-blue-500"
          :disabled="isSending"
          @keydown.enter.prevent="handleSendMessage"
        />
        <Button
          type="submit"
          icon="pi pi-send"
          :loading="isSending"
          :disabled="!messageText.trim() || isSending"
          class="bg-blue-600! border-blue-600! text-xs! py-2! px-3.5! rounded-lg! shadow-xs hover:bg-blue-700!"
        />
      </form>
    </div>
  </Dialog>
</template>

<style scoped>
.chat-dialog :deep(.p-dialog-content) {
  padding: 0;
  overflow: hidden;
}
</style>
