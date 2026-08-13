<script setup lang="ts">
import type { Product } from '@/types/entities'
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import Tag from 'primevue/tag'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoading = ref(true)
const searchQuery = ref('')
const selectedStatus = ref<string>('all')

const statusOptions = [
  { label: 'Semua Status', value: 'all' },
  { label: 'Aktif / Approved', value: 'approved' },
  { label: 'Menunggu / Pending', value: 'pending' },
  { label: 'Ditolak / Rejected', value: 'rejected' }
]

// Mock Data Produk
const products = ref<Partial<Product>[]>([])

const fetchProducts = () => {
  isLoading.value = true
  setTimeout(() => {
    products.value = [
      {
        id: 101,
        name: 'Keripik Tempe Renyah Premium 200g',
        slug: 'keripik-tempe-renyah-premium-200g',
        price: '18000',
        stock: 45,
        status: 'approved',
        category: { id: 1, name: 'Makanan & Minuman', slug: 'makanan-minuman', icon: '', product_count: null, created_at: '', updated_at: '' },
        images: [{ id: 1, url: 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=300' }]
      },
      {
        id: 102,
        name: 'Sambal Cumi Ciamis Pedas Gurih',
        slug: 'sambal-cumi-ciamis-pedas-gurih',
        price: '25000',
        stock: 12,
        status: 'approved',
        category: { id: 1, name: 'Makanan & Minuman', slug: 'makanan-minuman', icon: '', product_count: null, created_at: '', updated_at: '' },
        images: [{ id: 2, url: 'https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=300' }]
      },
      {
        id: 103,
        name: 'Kain Batik Tulis Motif Parang Murni',
        slug: 'kain-batik-tulis-motif-parang-murni',
        price: '350000',
        stock: 5,
        status: 'pending',
        category: { id: 2, name: 'Fashion & Pakaian', slug: 'fashion-pakaian', icon: '', product_count: null, created_at: '', updated_at: '' },
        images: [{ id: 3, url: 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=300' }]
      }
    ]
    isLoading.value = false
  }, 500)
}

const filteredProducts = computed(() => {
  return products.value.filter((p) => {
    const matchSearch = p.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchStatus = selectedStatus.value === 'all' || p.status === selectedStatus.value
    return matchSearch && matchStatus
  })
})

const getStatusSeverity = (status?: string) => {
  switch (status) {
    case 'approved': return 'success'
    case 'pending': return 'warn'
    case 'rejected': return 'danger'
    default: return 'info'
  }
}

const formatRupiah = (val?: string | number) => {
  const num = Number(val || 0)
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num)
}

const navigateToAdd = () => {
  router.push('/seller/produk/tambah')
}

const navigateToEdit = (id: number) => {
  router.push(`/seller/produk/edit/${id}`)
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
      <div class="flex flex-wrap items-center gap-3 flex-1">
        <div class="relative flex-1 min-w-55">
          <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <InputText v-model="searchQuery" placeholder="Cari nama produk..."
            class="w-full! pl-10! pr-4! py-2!.5 bg-slate-50! border-slate-200! rounded-xl! text-sm! focus:bg-white!" />
        </div>
        <Dropdown v-model="selectedStatus" :options="statusOptions" optionLabel="label" optionValue="value"
          placeholder="Status" class="w-48! bg-slate-50! border-slate-200! rounded-xl! text-sm!" />
      </div>

      <Button label="Tambah Produk" icon="pi pi-plus"
        class="bg-blue-600! hover:bg-blue-700! border-none! px-5! py-2!.5 rounded-xl! text-sm! font-semibold! shadow-sm! shrink-0"
        @click="navigateToAdd" />
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
      <DataTable :value="filteredProducts" :loading="isLoading" class="p-datatable-sm">
        <template #empty>
          <div class="p-8 text-center text-slate-500 text-sm">Tidak ada produk ditemukan.</div>
        </template>

        <Column header="Produk">
          <template #body="{ data }">
            <div class="flex items-center gap-3 py-1">
              <img :src="data.images?.[0]?.image_path || 'https://via.placeholder.com/60'" alt="Produk"
                class="w-12 h-12 rounded-lg object-cover border border-slate-100 shrink-0" />
              <div>
                <p class="text-sm font-semibold text-slate-800 line-clamp-1">{{ data.name }}</p>
                <p class="text-xs text-slate-400">{{ data.category?.name || 'Tanpa Kategori' }}</p>
              </div>
            </div>
          </template>
        </Column>

        <Column header="Harga" style="width: 160px">
          <template #body="{ data }">
            <span class="text-sm font-bold text-slate-700">{{ formatRupiah(data.price) }}</span>
          </template>
        </Column>

        <Column header="Stok" style="width: 100px">
          <template #body="{ data }">
            <span class="text-sm font-medium text-slate-600">{{ data.stock }} pcs</span>
          </template>
        </Column>

        <Column header="Status" style="width: 140px">
          <template #body="{ data }">
            <Tag :severity="getStatusSeverity(data.status)" class="text-xs! px-2!.5 py-1! rounded-md!">
              {{ data.status?.toUpperCase() }}
            </Tag>
          </template>
        </Column>

        <Column header="Aksi" style="width: 120px" class="text-center">
          <template #body="{ data }">
            <div class="flex items-center justify-center gap-2">
              <Button icon="pi pi-pencil" severity="secondary" text rounded class="w-8! h-8!"
                @click="navigateToEdit(data.id)" />
              <Button icon="pi pi-trash" severity="danger" text rounded class="w-8! h-8!" />
            </div>
          </template>
        </Column>
      </DataTable>

      <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <span>Menampilkan {{ filteredProducts.length }} dari {{ products.length }} produk</span>
        <Paginator :rows="10" :totalRecords="filteredProducts.length" />
      </div>
    </div>
  </div>
</template>