<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner'
import { computed, onMounted, ref } from 'vue'

import type { Order } from '@/types/entities'
import AdminOrderDetailModal from '../components/order/AdminOrderDetailModal.vue'
import AdminOrderFilter from '../components/order/AdminOrderFilter.vue'
import AdminOrderTable from '../components/order/AdminOrderTable.vue'

// States
const isLoading = ref(true)
const activeTab = ref('all')
const selectedOrder = ref<Partial<Order> | null>(null)
const showDetailModal = ref(false)

// Mock Data Orders
const mockOrders = ref<Partial<Order>[]>([
  {
    id: 9001,
    order_number: '#ORD-2026-001',
    total_amount: '385000',
    shipping_cost: '35000',
    status: 'delivered',
    payment_method: 'transfer',
    shipping_method: 'kurir',
    shipping_address: 'Jl. Pemuda No. 88, Kel. Rawamangun, Kec. Pulo Gadung, Jakarta Timur, 13220',
    created_at: '2026-08-08T10:30:00Z',
    buyer: { name: 'Rian Pratama', email: 'rian.pratama@gmail.com', phone: '+62 812 9900 1122' } as any,
    shop: { name: 'Kopi Kenangan Asli', logo: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100', seller: { name: 'Budi Santoso' } } as any,
    items: [
      // 👇 Menggunakan price_snapshot
      { id: 1, order_id: 9001, product_id: 1, quantity: 2, price_snapshot: '175000', cost_snapshot: '175000', created_at: '2026-08-08T10:30:00Z', updated_at: '2026-08-08T10:30:00Z', product: { name: 'Kopi Arabika Toraja 500g', images: [{ image_url: 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=100' }] } as any }
    ]
  },
  {
    id: 9002,
    order_number: '#ORD-2026-002',
    total_amount: '1250000',
    shipping_cost: '50000',
    status: 'processing',
    payment_method: 'cod',
    shipping_method: 'cod',
    shipping_address: 'Komplek Ruko Sentra Niaga B-12, Garut, Jawa Barat',
    created_at: '2026-08-09T08:15:00Z',
    buyer: { name: 'Dewi Lestari', email: 'dewi.lestari@gmail.com', phone: '+62 856 4433 2211' } as any,
    shop: { name: 'Dapur Snack Nusantara', logo: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=100', seller: { name: 'Siti Aminah' } } as any,
    items: [
      // 👇 Menggunakan price_snapshot
      { id: 2, order_id: 9002, product_id: 2, quantity: 1, price_snapshot: '1200000', cost_snapshot: '1200000', created_at: '2026-08-09T08:15:00Z', updated_at: '2026-08-09T08:15:00Z', product: { name: 'Kain Batik Tulis Garut Premium', images: [{ image_url: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=100' }] } as any }
    ]
  },
  {
    id: 9003,
    order_number: '#ORD-2026-003',
    total_amount: '750000',
    shipping_cost: '25000',
    status: 'pending',
    payment_method: 'transfer',
    shipping_method: 'kurir',
    shipping_address: 'Jl. Slamet Riyadi No. 45, Solo, Jawa Tengah',
    created_at: '2026-08-09T14:20:00Z',
    buyer: { name: 'Agus Wijaya', email: 'agus.wijaya@gmail.com', phone: '+62 811 5566 7788' } as any,
    shop: { name: 'Batik Solo Keraton', logo: 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=100', seller: { name: 'Agus' } } as any,
    items: [
      // 👇 Menggunakan price_snapshot
      { id: 3, order_id: 9003, product_id: 3, quantity: 1, price_snapshot: '725000', cost_snapshot: '725000', created_at: '2026-08-09T14:20:00Z', updated_at: '2026-08-09T14:20:00Z', product: { name: 'Set Dress Batik Solo Modern', images: [{ image_url: 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=100' }] } as any }
    ]
  }
])

// Computed Counts per Status
const statusCounts = computed(() => {
  const counts: Record<string, number> = { all: mockOrders.value.length }
  mockOrders.value.forEach((o) => {
    if (o.status) {
      counts[o.status] = (counts[o.status] || 0) + 1
    }
  })
  return counts
})

// Filtered Orders
const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return mockOrders.value
  return mockOrders.value.filter((o) => o.status === activeTab.value)
})

// Handlers
const openDetail = (order: Partial<Order>) => {
  selectedOrder.value = order
  showDetailModal.value = true
}

onMounted(() => {
  // Simulasi GET API dengan Fullscreen Circular Loader
  setTimeout(() => {
    isLoading.value = false
  }, 1000)
})
</script>

<template>
  <div class="relative min-h-[80vh]">

    <div v-if="isLoading"
      class="fixed inset-0 z-100 flex flex-col items-center justify-center bg-white/90 backdrop-blur-xs transition-all duration-300">
      <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
      <span class="mt-4 text-sm font-semibold text-slate-600 animate-pulse">Memuat data transaksi pesanan...</span>
    </div>

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Manajemen Pesanan</h1>
      <p class="text-sm text-slate-500 mt-1">Kelola dan pantau seluruh transaksi pesanan di platform Kabita</p>
    </div>

    <AdminOrderFilter v-model:activeTab="activeTab" :counts="statusCounts" />

    <AdminOrderTable :orders="filteredOrders" @viewDetail="openDetail" />

    <AdminOrderDetailModal v-model:visible="showDetailModal" :order="selectedOrder" />
  </div>
</template>