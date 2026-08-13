<script setup lang="ts">
import type { Category } from '@/types/entities';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref } from 'vue';
import AdminCategoryCardComponent from '../components/category-management/AdminCategoryCard.vue';
import AdminCategoryModalComponent from '../components/category-management/AdminCategoryModal.vue';
// import AdminCategoryCard from './components/AdminCategoryCard.vue';
// import AdminCategoryModal from './components/AdminCategoryModal.vue';

const toast = useToast();
const AdminCategoryModal = AdminCategoryModalComponent as any;
const AdminCategoryCard = AdminCategoryCardComponent as any;

// State Management
const isLoading = ref(true);
const categories = ref<Partial<Category>[]>([]);
const searchQuery = ref('');
const isModalOpen = ref(false);
const selectedCategory = ref<Category | null>(null);

// Mock Data persis Screenshot 2026-08-09 182531.png
const mockCategories: Partial<Category>[] = [
  { id: 1, name: 'Makanan & Minuman', slug: 'makanan-minuman', icon: '🍕', product_count: 24 },
  { id: 2, name: 'Fashion', slug: 'fashion', icon: '👕', product_count: 128 },
  { id: 3, name: 'Elektronik', slug: 'elektronik', icon: '💻', product_count: 56 },
  { id: 4, name: 'Rumah Tangga', slug: 'rumah-tangga', icon: '🏠', product_count: 92 },
  { id: 5, name: 'Mainan & Hobi', slug: 'mainan-hobi', icon: '🧸', product_count: 14 },
  { id: 6, name: 'Kecantikan', slug: 'kecantikan', icon: '💄', product_count: 43 },
  { id: 7, name: 'Olahraga', slug: 'olahraga', icon: '👟', product_count: 31 },
  { id: 8, name: 'Buku & Alat Tulis', slug: 'buku-alat-tulis', icon: '📚', product_count: 75 },
];

// Load GET Data dengan Fullscreen Circular Spinner
const fetchCategories = () => {
  isLoading.value = true;
  setTimeout(() => {
    categories.value = mockCategories as Category[];
    isLoading.value = false;
  }, 700);
};

onMounted(() => {
  fetchCategories();
});

// Computed Filtered Categories
const filteredCategories = computed(() => {
  if (searchQuery!.value.trim()) return categories.value;
  const q = searchQuery.value.toLowerCase();
  return categories.value.filter(
    (c) => c?.name?.toLowerCase().includes(q) || c?.slug?.toLowerCase().includes(q)
  );
});

// Modal Handlers
const openAddModal = () => {
  selectedCategory.value = null;
  isModalOpen.value = true;
};

const openEditModal = (category: Category) => {
  selectedCategory.value = category;
  isModalOpen.value = true;
};

const handleDeleteCategory = (category: Category) => {
  categories.value = categories.value.filter((c) => c.id !== category.id);
  toast.add({
    severity: 'success',
    summary: 'Berhasil',
    detail: `Kategori "${category.name}" telah dihapus`,
    life: 3000,
  });
};

const handleSaveCategory = (payload: Partial<Category>) => {
  if (selectedCategory.value) {
    // Edit Mode
    const index = categories.value.findIndex((c) => c.id === payload.id);
    if (index !== -1) {
      categories.value[index] = {
        ...categories.value[index],
        ...payload,
      };
      toast.add({
        severity: 'success',
        summary: 'Pembaruan Berhasil',
        detail: `Kategori "${payload.name}" berhasil diperbarui`,
        life: 3000,
      });
    }
  } else {
    // Create Mode
    const newCategory: Partial<Category> = {
      id: Date.now(),
      name: payload.name || '',
      slug: payload.slug || '',
      icon: payload.icon || '📦',
      product_count: 0,
    };
    categories.value.unshift(newCategory);
    toast.add({
      severity: 'success',
      summary: 'Kategori Ditambahkan',
      detail: `Kategori "${newCategory.name}" berhasil dibuat`,
      life: 3000,
    });
  }
};
</script>

<template>
  <div class="relative min-h-screen pb-12">

    <Transition name="fade">
      <div v-if="isLoading"
        class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
        <ProgressSpinner style="width: 56px; height: 56px" strokeWidth="4" animationDuration=".8s"
          aria-label="Loading" />
        <p class="mt-4 text-sm font-semibold text-gray-600 animate-pulse">
          Memuat Kategori...
        </p>
      </div>
    </Transition>

    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
            Manajemen Kategori
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Kelola semua kategori platform Kabita
          </p>
        </div>

        <Button label="Tambah Kategori" icon="pi pi-plus"
          class="rounded-xl! px-5! py-3! font-semibold! bg-blue-600! hover:bg-blue-700! border-blue-600! shadow-sm shadow-blue-200"
          @click="openAddModal" />
      </div>

      <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="relative w-full sm:w-80">
          <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <InputText v-model="searchQuery" placeholder="Cari kategori..."
            class="w-full rounded-xl! pl-10! pr-4! py-2!.5 text-sm! border-gray-200! focus:border-blue-500! focus:ring-2! focus:ring-blue-100!" />
        </div>
        <span class="text-xs font-semibold text-gray-400 hidden sm:inline-block">
          Total: {{ filteredCategories.length }} Kategori
        </span>
      </div>

      <div v-if="filteredCategories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <AdminCategoryCard v-for="cat in filteredCategories" :key="cat.id" :category="cat" @edit="openEditModal"
          @delete="handleDeleteCategory" />
      </div>

      <div v-else-if="isLoading!" class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <div
          class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto text-2xl text-gray-400 mb-3">
          🔍
        </div>
        <h3 class="text-base font-bold text-gray-800">Kategori Tidak Ditemukan</h3>
        <p class="text-xs text-gray-400 mt-1">
          Tidak ada kategori yang sesuai dengan kueri pencarian Anda.
        </p>
      </div>
    </div>

    <AdminCategoryModal v-model:visible="isModalOpen" :category-to-edit="selectedCategory" @save="handleSaveCategory" />
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