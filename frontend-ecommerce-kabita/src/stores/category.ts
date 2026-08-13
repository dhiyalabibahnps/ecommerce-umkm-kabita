import type { Category } from '@/types'
import { defineStore } from 'pinia'
import { ref } from 'vue'

function mockCategory(id: number, name: string, slug: string, icon: string): Category {
  return {
    id,
    name,
    slug,
    icon,
    product_count: null,
    created_at: '2026-06-01T00:00:00Z',
  }
}

export const useCategoryStore = defineStore('category', () => {
  const categories = ref<Category[]>([
    mockCategory(1, 'Makanan', 'makanan', 'pi pi-utensils'),
    mockCategory(2, 'Pakaian', 'pakaian', 'pi pi-shopping-bag'),
    mockCategory(3, 'Elektronik', 'elektronik', 'pi pi-tablet'),
    mockCategory(4, 'Perabot', 'perabot', 'pi pi-home'),
    mockCategory(5, 'Kecantikan', 'kecantikan', 'pi pi-sparkles'),
    mockCategory(6, 'Hobi', 'hobi', 'pi pi-gift'),
    mockCategory(7, 'Lainnya', 'lainnya', 'pi pi-ellipsis-h'),
  ])

  return { categories }
})
