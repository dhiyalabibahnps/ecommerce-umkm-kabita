<script setup lang="ts">
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import RadioButton from 'primevue/radiobutton'
import Tag from 'primevue/tag'
import Textarea from 'primevue/textarea'
import { useToast } from 'primevue/usetoast'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const productId = route.params.id || '101'

// State Full-screen Loading GET
const isLoadingGet = ref(true)
const isSubmitting = ref(false)
const showDeleteDialog = ref(false)

// Form State
const form = ref({
  id: Number(productId),
  name: '',
  category_id: null as number | null,
  price: null as number | null,
  cost_price: null as number | null,
  stock: null as number | null,
  weight: null as number | null,
  description: '',
  sku: '',
  status: 'active',
  images: [] as string[]
})

const categories = [
  { label: 'Home Decor', value: 3 },
  { label: 'Makanan & Minuman', value: 1 },
  { label: 'Fashion & Pakaian', value: 2 },
  { label: 'Elektronik', value: 4 }
]

// ----------------------------------------------------------------
// 1. SIMULASI GET DATA EDIT PRODUK (Full Screen Circular Loader)
// ----------------------------------------------------------------
const fetchProductDetail = () => {
  isLoadingGet.value = true
  setTimeout(() => {
    form.value = {
      id: Number(productId),
      name: 'Handcrafted Woven Basket',
      category_id: 3,
      price: 125000,
      cost_price: 75000,
      stock: 42,
      weight: 500,
      description: 'Beautifully handcrafted woven basket, perfect for organizing your living space or adding a touch of natural charm to your home decor. Made from sustainably sourced materials, ensuring durability and style. Dimensions: 30cm x 30cm x 25cm.',
      sku: 'HWB-2024-001',
      status: 'active',
      images: [
        'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=500',
        'https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=500',
        'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=500'
      ]
    }
    isLoadingGet.value = false
  }, 1000)
}

// Drag & Drop / Upload Image Mock Handler
const handleImageUpload = (e: Event, index: number) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    form.value.images[index] = URL.createObjectURL(file)
  }
}

const removeImage = (index: number) => {
  form.value.images.splice(index, 1)
}

const handleUpdate = () => {
  isSubmitting.value = true
  setTimeout(() => {
    isSubmitting.value = false
    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Detail produk berhasil diperbarui!',
      life: 3000
    })
    router.push('/seller/produk')
  }, 1200)
}

const confirmDelete = () => {
  showDeleteDialog.value = false
  toast.add({
    severity: 'error',
    summary: 'Produk Dihapus',
    detail: 'Produk telah berhasil dihapus dari toko Anda.',
    life: 3000
  })
  router.push('/seller/produk')
}

const goBack = () => {
  router.push('/seller/produk')
}

onMounted(() => {
  fetchProductDetail()
})
</script>

<template>
  <div class="relative min-h-[80vh]">
    <Transition name="fade">
      <div v-if="isLoadingGet"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
        <ProgressSpinner style="width: 56px; height: 56px" strokeWidth="4" fill="transparent" animationDuration=".8s" />
        <p class="mt-4 font-medium text-slate-600 text-sm animate-pulse">
          Memuat Detail Produk...
        </p>
      </div>
    </Transition>

    <div v-if="!isLoadingGet" class="max-w-6xl mx-auto space-y-6 pb-12">
      <div class="flex items-center gap-2 text-xs text-slate-500">
        <span class="hover:underline cursor-pointer" @click="goBack">Produk</span>
        <i class="pi pi-chevron-right text-[10px]"></i>
        <span class="font-semibold text-slate-800">Edit Produk</span>
      </div>

      <div>
        <h1 class="text-xl font-bold text-slate-900">Edit Produk</h1>
        <p class="text-xs text-slate-500 mt-0.5">Perbarui detail produk untuk toko Anda</p>
      </div>

      <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-100/80 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img :src="form.images[0] || 'https://via.placeholder.com/60'"
            class="w-12 h-12 rounded-xl object-cover border border-white shadow-sm" />
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-slate-900 text-sm">{{ form.name }}</h3>
              <Tag severity="success" value="AKTIF" class="text-[10px]! px-2! py-0.5!" />
            </div>
            <p class="text-xs text-slate-500 mt-0.5">SKU: {{ form.sku }}</p>
          </div>
        </div>

        <a href="#" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1">
          <span>Lihat di Toko</span>
          <i class="pi pi-external-link text-[10px]"></i>
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 space-y-5">
          <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Informasi Dasar</h2>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                Nama Produk <span class="text-red-500">*</span>
              </label>
              <InputText v-model="form.name" class="w-full! rounded-xl! text-xs! py-2.5!" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                Kategori <span class="text-red-500">*</span>
              </label>
              <Dropdown v-model="form.category_id" :options="categories" optionLabel="label" optionValue="value"
                class="w-full! rounded-xl! text-xs! bg-slate-50/50!" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Harga Jual (Rp) <span class="text-red-500">*</span>
                </label>
                <InputNumber v-model="form.price" class="w-full!" inputClass="!w-full !rounded-xl !text-xs !py-2.5" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Modal (Rp)</label>
                <InputNumber v-model="form.cost_price" class="w-full!"
                  inputClass="!w-full !rounded-xl !text-xs !py-2.5" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Stok <span class="text-red-500">*</span>
                </label>
                <InputNumber v-model="form.stock" :min="0" class="w-full!"
                  inputClass="!w-full !rounded-xl !text-xs !py-2.5" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Berat (gram) <span class="text-red-500">*</span>
                </label>
                <InputNumber v-model="form.weight" :min="0" class="w-full!"
                  inputClass="!w-full !rounded-xl !text-xs !py-2.5" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                Deskripsi Produk <span class="text-red-500">*</span>
              </label>
              <Textarea v-model="form.description" rows="5" class="w-full! rounded-xl! text-xs! p-3!" />
            </div>
          </div>
        </div>

        <div class="lg:col-span-5 space-y-5">
          <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">
              Media & Status (Foto Maks. 5)
            </h2>

            <div class="grid grid-cols-2 gap-3">
              <div v-for="i in 4" :key="i - 1"
                class="relative aspect-square border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center overflow-hidden bg-slate-50/50 group">
                <template v-if="form.images[i - 1]">
                  <img :src="form.images[i - 1]" class="w-full h-full object-cover" />
                  <button type="button" @click="removeImage(i - 1)"
                    class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-md">
                    <i class="pi pi-times"></i>
                  </button>
                </template>
                <template v-else>
                  <div class="text-center p-2">
                    <i class="pi pi-camera text-slate-400 text-lg"></i>
                    <span class="block text-[10px] text-slate-400 mt-1 font-medium">+ Tambah Foto</span>
                  </div>
                </template>
                <input type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer"
                  @change="handleImageUpload($event, i - 1)" />
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
            <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Status Produk</h2>

            <div class="space-y-3">
              <label :class="[
                'flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-colors',
                form.status === 'active' ? 'bg-blue-50/60 border-blue-300' : 'border-slate-200'
              ]">
                <RadioButton v-model="form.status" value="active" class="mt-0.5" />
                <div>
                  <span class="block text-xs font-bold text-slate-800">Aktif</span>
                  <span class="text-[11px] text-slate-500">Produk dapat dilihat dan dibeli oleh pelanggan.</span>
                </div>
              </label>

              <label :class="[
                'flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-colors',
                form.status === 'inactive' ? 'bg-blue-50/60 border-blue-300' : 'border-slate-200'
              ]">
                <RadioButton v-model="form.status" value="inactive" class="mt-0.5" />
                <div>
                  <span class="block text-xs font-bold text-slate-800">Nonaktif</span>
                  <span class="text-[11px] text-slate-500">Produk disembunyikan dari toko Anda.</span>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="pt-4 border-t border-slate-200/80 flex items-center justify-between">
        <Button label="Hapus Produk" icon="pi pi-trash" severity="danger" outlined
          class="rounded-xl! text-xs! px-5! py-2.5! border-red-300! hover:bg-red-50!"
          @click="showDeleteDialog = true" />

        <div class="flex items-center gap-3">
          <Button label="Batal" severity="secondary" outlined class="rounded-xl! text-xs! px-6! py-2.5!"
            @click="goBack" />
          <Button label="Simpan Perubahan" :loading="isSubmitting"
            class="bg-blue-600! hover:bg-blue-700! border-blue-600! rounded-xl! text-xs! font-semibold! px-6! py-2.5!"
            @click="handleUpdate" />
        </div>
      </div>
    </div>

    <Dialog v-model:visible="showDeleteDialog" modal header="Hapus Produk" :style="{ width: '380px' }"
      class="rounded-2xl!">
      <p class="text-xs text-slate-600 py-2">
        Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.
      </p>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Batal" severity="secondary" text class="text-xs! rounded-xl!"
            @click="showDeleteDialog = false" />
          <Button label="Ya, Hapus" severity="danger" class="text-xs! rounded-xl! bg-red-600!" @click="confirmDelete" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>