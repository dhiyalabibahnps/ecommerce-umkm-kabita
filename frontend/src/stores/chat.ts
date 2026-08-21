import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface ChatOpenOptions {
  conversationId?: number | null;
  orderId?: number | null;
  orderNumber?: string | null;
  shopId?: number | null;
  targetName?: string | null;
  productName?: string | null;
  role?: 'buyer' | 'seller' | null;
}

export const useChatStore = defineStore('chat', () => {
  const isOpen = ref(false);
  const targetType = ref<'order' | 'shop' | 'conversation' | null>(null);
  const conversationId = ref<number | null>(null);
  const orderId = ref<number | null>(null);
  const orderNumber = ref<string | null>(null);
  const shopId = ref<number | null>(null);
  const targetName = ref<string | null>(null);
  const productName = ref<string | null>(null);
  const role = ref<'buyer' | 'seller' | null>(null);

  function openOrderChat(
    newOrderId: number,
    newOrderNumber?: string | null,
    newTargetName?: string | null,
    newRole?: 'buyer' | 'seller' | null
  ) {
    targetType.value = 'order';
    orderId.value = newOrderId;
    orderNumber.value = newOrderNumber || null;
    targetName.value = newTargetName || null;
    role.value = newRole || null;
    conversationId.value = null;
    shopId.value = null;
    productName.value = null;
    isOpen.value = true;
  }

  function openShopChat(
    newShopId: number,
    newTargetName?: string | null,
    newProductName?: string | null
  ) {
    targetType.value = 'shop';
    shopId.value = newShopId;
    targetName.value = newTargetName || null;
    productName.value = newProductName || null;
    orderId.value = null;
    orderNumber.value = null;
    conversationId.value = null;
    role.value = 'buyer';
    isOpen.value = true;
  }

  function openConversation(
    newConversationId: number,
    newTargetName?: string | null,
    newOrderNumber?: string | null,
    newOrderId?: number | null,
    newShopId?: number | null,
    newProductName?: string | null,
    newRole?: 'buyer' | 'seller' | null
  ) {
    targetType.value = 'conversation';
    conversationId.value = newConversationId;
    targetName.value = newTargetName || null;
    orderNumber.value = newOrderNumber || null;
    orderId.value = newOrderId || null;
    shopId.value = newShopId || null;
    productName.value = newProductName || null;
    role.value = newRole || null;
    isOpen.value = true;
  }

  function openFromNotification(data?: Record<string, any> | null) {
    if (!data) return;

    if (data.conversation_id) {
      openConversation(
        Number(data.conversation_id),
        data.sender_name || null,
        data.order_number || null,
        data.order_id ? Number(data.order_id) : null,
        data.shop_id ? Number(data.shop_id) : null,
        data.product_name || null
      );
    } else if (data.order_id) {
      openOrderChat(
        Number(data.order_id),
        data.order_number || null,
        data.sender_name || null
      );
    } else if (data.shop_id) {
      openShopChat(
        Number(data.shop_id),
        data.sender_name || null,
        data.product_name || null
      );
    }
  }

  function closeChat() {
    isOpen.value = false;
  }

  return {
    isOpen,
    targetType,
    conversationId,
    orderId,
    orderNumber,
    shopId,
    targetName,
    productName,
    role,
    openOrderChat,
    openShopChat,
    openConversation,
    openFromNotification,
    closeChat,
  };
});
