<script setup lang="ts">
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import ProgressSpinner from 'primevue/progressspinner'
import { onMounted, ref } from 'vue'

import type { SellerTopProduct } from '@/types/entities'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoadingGet = ref(true)
const searchQuery = ref('')
const selectedCategory = ref('all')

const categoryOptions = [
  { label: 'Semua Kategori', value: 'all' },
  { label: 'Makanan & Minuman', value: 'makanan' },
  { label: 'Fashion Pria', value: 'fashion' },
  { label: 'Elektronik', value: 'elektronik' }
]

const allTopProducts = ref<SellerTopProduct[]>([])

const fetchAllTopProducts = () => {
  isLoadingGet.value = true
  setTimeout(() => {
    allTopProducts.value = [
      { id: 1, shop_id: 1, category_id: 1, name: 'Kopi Luwak Premium 200g', slug: 'kopi-luwak', price: '150000', cost_price: '120000', stock: 50, weight: 200, status: 'active', total_sold: 145, total_revenue: 'Rp 4.350.000', profit: 'Rp 1.450.000' },
      { id: 2, shop_id: 1, category_id: 2, name: 'Batik Tulis Pekalongan', slug: 'batik-tulis', price: '250000', cost_price: '180000', stock: 30, weight: 500, status: 'active', total_sold: 82, total_revenue: 'Rp 3.690.000', profit: 'Rp 1.100.000' },
      { id: 3, shop_id: 1, category_id: 1, name: 'Sambal Roa Manado Asli 200g', slug: 'sambal-roa', price: '35000', cost_price: '20000', stock: 100, weight: 200, status: 'active', total_sold: 76, total_revenue: 'Rp 1.140.000', profit: 'Rp 380.000' },
      { id: 4, shop_id: 1, category_id: 3, name: 'Rotan Chair Set Minimalis', slug: 'rotan-chair', price: '750000', cost_price: '500000', stock: 10, weight: 5000, status: 'active', total_sold: 45, total_revenue: 'Rp 11.250.000', profit: 'Rp 3.100.000' },
      { id: 5, shop_id: 1, category_id: 1, name: 'Minyak Kelapa Murni 500ml', slug: 'minyak-kelapa', price: '45000', cost_price: '30000', stock: 80, weight: 500, status: 'active', total_sold: 38, total_revenue: 'Rp 950.000', profit: 'Rp 280.000' }
    ]
    isLoadingGet.value = false
  }, 800)
}

const goBackToAnalytics = () => {
  router.push('/seller/analitik')
}

onMounted(() => {
  fetchAllTopProducts()
})
</script>

<template>
  <div class="relative min-h-[80vh]">
    <Transition name="fade">
      <div v-if="isLoadingGet"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
        <p class="mt-4 font-medium text-slate-600 text-sm">Memuat Daftar Produk Terlaris...</p>
      </div>
    </Transition>

    <div v-if="!isLoadingGet" class="max-w-6xl mx-auto space-y-6 pb-12">
      <div class="flex items-center justify-between">
        <button @click="goBackToAnalytics"
          class="flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-blue-600 transition-colors">
          <i class="pi pi-arrow-left"></i>
          <span>Kembali ke Analitik Penjualan</span>
        </button>
      </div>

      <div
        class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Daftar Produk Terlaris</h1>
          <p class="text-xs text-slate-500 mt-1">Peringkat performa produk toko berdasarkan volume penjualan & akumulasi
            pendapatan.</p>
        </div>

        <div class="flex items-center gap-3">
          <div class="relative w-64">
            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <InputText v-model="searchQuery" placeholder="Cari produk terlaris..."
              class="w-full! pl-9! pr-4! py-2! bg-slate-50! border-slate-200! rounded-xl! text-xs!" />
          </div>
          <Dropdown v-model="selectedCategory" :options="categoryOptions" optionLabel="label" optionValue="value"
            class="bg-slate-50! border-slate-200! rounded-xl! text-xs!" />
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead
              class="bg-slate-50/70 text-slate-400 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-100">
              <tr>
                <th class="py-3.5 px-6">Peringkat / Produk</th>
                <th class="py-3.5 px-4 text-center">Unit Terjual</th>
                <th class="py-3.5 px-4">Total Pendapatan</th>
                <th class="py-3.5 px-4">Estimasi Profit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(item, idx) in allTopProducts" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="py-4 px-6">
                  <div class="flex items-center gap-3">
                    <span :class="[
                      'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0',
                      idx === 0 ? 'bg-amber-100 text-amber-700' : idx === 1 ? 'bg-slate-200 text-slate-700' : idx === 2 ? 'bg-amber-700/10 text-amber-800' : 'bg-slate-100 text-slate-500'
                    ]">
                      {{ idx + 1 }}
                    </span>
                    <div>
                      <p class="text-xs font-bold text-slate-800">{{ item.name }}</p>
                      <p class="text-[10px] text-slate-400">Slug: {{ item.slug }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-4 px-4 text-center font-extrabold text-slate-800">{{ item.total_sold }} Unit</td>
                <td class="py-4 px-4 font-extrabold text-blue-600">{{ item.total_revenue }}</td>
                <td class="py-4 px-4 font-bold text-emerald-600">{{ item.profit }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <span>Menampilkan {{ allTopProducts.length }} Produk Terlaris</span>
          <Paginator :rows="10" :totalRecords="allTopProducts.length" />
        </div>
      </div>
    </div>
  </div>
</template>