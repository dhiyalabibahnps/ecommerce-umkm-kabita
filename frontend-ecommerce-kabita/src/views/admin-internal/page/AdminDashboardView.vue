<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { onMounted, ref } from 'vue'

import AdminInternalLayout from '../AdminInternalLayout.vue'
import AdminPendingVerifications from '../components/dashboard/AdminDashboardPendingVerifications.vue'
import AdminRecentTransactions from '../components/dashboard/AdminDashboardRecentTransactions.vue'
import AdminRevenueChart from '../components/dashboard/AdminDashboardRevenueChart.vue'
import AdminStatCards from '../components/dashboard/AdminDashboardStatCards.vue'
import AdminTopPerformers from '../components/dashboard/AdminDashboardTopPerformers.vue'

import type { Order, PlatformStats, Shop, TopProduct, TopSeller } from '@/types/entities'

const toast = useToast()
const isLoading = ref(true)

// Mock Data Berdasarkan Entity Front-End
const platformStats = ref<PlatformStats>({
  total_users: 1450,
  users_by_role: { buyer: 1320, seller: 128, admin: 2 },
  total_shops: 128,
  shops_by_status: { verified: 118, pending: 8, rejected: 2 },
  verified_shops: 118,
  pending_shops: 8,
  total_products: 3420,
  monthly_transactions: [
    { month: 1, transactions: 120, revenue: '45000000' },
    { month: 2, transactions: 150, revenue: '58000000' },
    { month: 3, transactions: 180, revenue: '72000000' },
    { month: 4, transactions: 210, revenue: '89000000' },
    { month: 5, transactions: 195, revenue: '82000000' },
    { month: 6, transactions: 240, revenue: '105000000' },
    { month: 7, transactions: 290, revenue: '128500000' }
  ],
  platform_revenue: '579500000'
})

const pendingShops = ref<Partial<Shop>[]>([
  { id: 101, name: 'Dapur Snack Nusantara', seller: { id: 12, name: 'Budi Santoso', email: 'budi@gmail.com' } as any, logo: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100' },
  { id: 102, name: 'Kerajinan Batik Garut', seller: { id: 15, name: 'Siti Aminah', email: 'siti@gmail.com' } as any, logo: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=100' }
])

const recentOrders = ref<Partial<Order>[]>([
  {
    id: 8901,
    order_number: '#ORD-2026-001',
    total_amount: '350000',
    status: 'delivered',
    payment_method: 'transfer',
    shop: { name: 'Kopi Kenangan Asli' } as any,
    buyer: { name: 'Rian Pratama' } as any,
    created_at: '2026-08-08'
  },
  {
    id: 8902,
    order_number: '#ORD-2026-002',
    total_amount: '1200000',
    status: 'processing',
    payment_method: 'cod',
    shop: { name: 'Dapur Snack Nusantara' } as any,
    buyer: { name: 'Dewi Lestari' } as any,
    created_at: '2026-08-09'
  },
  {
    id: 8903,
    order_number: '#ORD-2026-003',
    total_amount: '750000',
    status: 'pending',
    payment_method: 'transfer',
    shop: { name: 'Batik Solo Keraton' } as any,
    buyer: { name: 'Agus Wijaya' } as any, // 
    created_at: '2026-08-09'
  }
])

const topSellers = ref<TopSeller[]>([
  { id: '1', name: 'Kopi Kenangan Asli', total_orders: 430, total_revenue: '125000000' },
  { id: '2', name: 'Batik Solo Keraton', total_orders: 310, total_revenue: '98000000' },
  { id: '3', name: 'Gudeg Bu Tjitro', total_orders: 280, total_revenue: '74000000' }
])

const topProducts = ref<TopProduct[]>([
  { id: '1', name: 'Kopi Arabika Toraja 500g', slug: 'kopi-arabika', price: '85000', total_qty_sold: 1200, total_revenue: '102000000' },
  { id: '2', name: 'Kain Batik Tulis Mega Mendung', slug: 'batik-mega-mendung', price: '350000', total_qty_sold: 280, total_revenue: '98000000' }
])

// Handler Verifikasi Toko
const handleVerifyShop = (shopId: number) => {
  pendingShops.value = pendingShops.value.filter((s) => s.id !== shopId)
  platformStats.value.pending_shops -= 1
  platformStats.value.verified_shops += 1
  toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Toko telah diverifikasi', life: 3000 })
}

const handleRejectShop = (shopId: number) => {
  pendingShops.value = pendingShops.value.filter((s) => s.id !== shopId)
  platformStats.value.pending_shops -= 1
  toast.add({ severity: 'warn', summary: 'Ditolak', detail: 'Pengajuan toko telah ditolak', life: 3000 })
}

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false
  }, 500)
})
</script>

<template>
  <AdminInternalLayout slug="dashboard">

    <div v-if="isLoading" class="min-h-[60vh] flex flex-col items-center justify-center gap-3">
      <ProgressSpinner style="width: 48px; height: 48px" strokeWidth="4" />
      <span class="text-xs font-semibold text-slate-500">Memuat data Dashboard Admin...</span>
    </div>

    <div v-else class="space-y-8">
      <AdminStatCards :stats="platformStats" />

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <AdminRevenueChart :monthlyTransactions="platformStats.monthly_transactions" />
        </div>
        <div>
          <AdminPendingVerifications :pendingShops="pendingShops" @verify="handleVerifyShop"
            @reject="handleRejectShop" />
        </div>
      </div>

      <AdminRecentTransactions :orders="recentOrders" />

      <AdminTopPerformers :topSellers="topSellers" :topProducts="topProducts" />
    </div>
  </AdminInternalLayout>
</template>