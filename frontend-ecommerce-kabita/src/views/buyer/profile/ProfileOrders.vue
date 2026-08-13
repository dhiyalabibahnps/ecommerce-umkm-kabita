<script setup lang="ts">
import type { OrderStatus } from '@/types'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Tag from 'primevue/tag'
import { ref } from 'vue'


// Interface Relations
export interface OrderItem {
  id: number
  order_id: number
  product_id: number
  product_name: string
  product_image?: string
  quantity: number
  price: string
}

export interface Shop {
  id: number
  name: string
  logo?: string
}

// Main Interface
export interface Order {
  id: number
  order_number: string
  buyer_id: number
  shop_id: number
  subtotal: string
  shipping_cost: string
  total_amount: string
  shipping_method: string
  payment_method: string
  status: OrderStatus
  shipping_address: string
  tracking_number: string | null
  notes: string | null
  created_at: string
  updated_at: string
  shop?: Shop
  items?: OrderItem[]
}

const searchQuery = ref('')

// Mock data berdasarkan interface Order
const orders = ref<Order[]>([
  {
    id: 1,
    order_number: 'KBT-20260801-001',
    buyer_id: 10,
    shop_id: 5,
    subtotal: '70000.00',
    shipping_cost: '10000.00',
    total_amount: '80000.00',
    shipping_method: 'JNE Reguler',
    payment_method: 'QRIS',
    status: 'completed',
    shipping_address: 'Jl. Pemuda No. 12, Jakarta',
    tracking_number: 'JNE123456789',
    notes: 'Tolong packing kayu',
    created_at: '2026-08-01T10:00:00Z',
    updated_at: '2026-08-01T15:00:00Z',
    shop: {
      id: 5,
      name: 'Toko UMKM Binaan'
    },
    items: [
      {
        id: 101,
        order_id: 1,
        product_id: 201,
        product_name: 'Keripik Tempe Renyah Khas Daerah 250g',
        product_image: 'https://primefaces.org/cdn/primevue/images/galleria/galleria1.jpg',
        quantity: 2,
        price: '35000.00'
      }
    ]
  },
  {
    id: 2,
    order_number: 'KBT-20260728-004',
    buyer_id: 10,
    shop_id: 8,
    subtotal: '85000.00',
    shipping_cost: '15000.00',
    total_amount: '100000.00',
    shipping_method: 'SiCepat BEST',
    payment_method: 'Transfer Bank (BCA)',
    status: 'processing',
    shipping_address: 'Jl. Pemuda No. 12, Jakarta',
    tracking_number: null,
    notes: null,
    created_at: '2026-07-28T09:30:00Z',
    updated_at: '2026-07-28T09:35:00Z',
    shop: {
      id: 8,
      name: 'Kopi Kenangan UMKM'
    },
    items: [
      {
        id: 102,
        order_id: 2,
        product_id: 205,
        product_name: 'Biji Kopi Arabika Robusta Blend 500g',
        product_image: 'https://primefaces.org/cdn/primevue/images/galleria/galleria2.jpg',
        quantity: 1,
        price: '85000.00'
      }
    ]
  }
])

// Helper Format Rupiah
const formatCurrency = (amount: string | number) => {
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(numericAmount)
}

// Helper Format Tanggal
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

// Helper Badge Severity
const getSeverity = (status: OrderStatus) => {
  switch (status) {
    case 'completed':
      return 'success'
    case 'processing':
    case 'shipped':
      return 'warn'
    case 'cancelled':
      return 'danger'
    default:
      return 'info'
  }
}

// Helper Label Status
const getStatusLabel = (status: OrderStatus) => {
  const labels: Record<OrderStatus, string> = {
    pending: 'Menunggu Pembayaran',
    processing: 'Diproses',
    shipped: 'Dikirim',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    delivered: "Telah Diterima"
  }
  return labels[status] || status
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8 border border-slate-100">
    <--! Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-100 gap-4">
        <div>
          <h1 class="text-lg font-bold text-slate-800">Daftar Pesanan</h1>
          <p class="text-xs text-slate-500 mt-1">Pantau status transaksi dan histori belanja Anda.</p>
        </div>
        <--! Search Input -->
          <div class="relative w-full sm:w-64">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <InputText v-model="searchQuery" placeholder="Cari No. Pesanan..."
              class="w-full pl-8! py-2! text-xs! rounded-xl!" />
          </div>
      </div>

      <--! Order List -->
        <div class="space-y-4">
          <div v-for="order in orders" :key="order.id"
            class="border border-slate-100 rounded-xl p-4 hover:border-slate-200 transition-colors space-y-3">
            <--! Card Top Bar -->
              <div
                class="flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-slate-700">{{ order.shop?.name || 'Toko UMKM' }}</span>
                  <span>•</span>
                  <span>{{ formatDate(order.created_at) }}</span>
                  <span>•</span>
                  <span class="text-slate-400">{{ order.order_number }}</span>
                </div>
                <Tag :value="getStatusLabel(order.status)" :severity="getSeverity(order.status)"
                  class="text-![10px] px-2!.5 py-0!.5" />
              </div>

              <--! Items -->
                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 py-1">
                  <img :src="item.product_image || 'https://primefaces.org/cdn/primevue/images/galleria/galleria1.jpg'"
                    alt="Product" class="w-16 h-16 rounded-lg object-cover border border-slate-100 shrink-0" />
                  <div class="flex-1 min-w-0">
                    <h3 class="text-xs font-semibold text-slate-800 truncate">{{ item.product_name }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ item.quantity }} barang x {{
                      formatCurrency(item.price) }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-bold text-slate-800">{{ formatCurrency(parseFloat(item.price) *
                      item.quantity)
                    }}</span>
                  </div>
                </div>

                <--! Card Footer -->
                  <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between pt-3 border-t border-slate-100 gap-3">
                    <div class="text-xs">
                      <span class="text-slate-500">Total Belanja: </span>
                      <span class="font-bold text-slate-900">{{ formatCurrency(order.total_amount) }}</span>
                      <span class="text-[10px] text-slate-400 block sm:inline sm:ml-2">(Ongkir: {{
                        formatCurrency(order.shipping_cost) }})</span>
                    </div>
                    <div class="flex gap-2 justify-end">
                      <Button label="Detail Pesanan" severity="secondary" outlined
                        class="text-xs! px-3! py-1!.5 rounded-lg!" />
                      <Button v-if="order.status === 'completed'" label="Beli Lagi"
                        class="bg-blue-600! border-blue-600! text-xs! px-3! py-1!.5 rounded-lg!" />
                    </div>
                  </div>
          </div>
        </div>
  </div>
</template>