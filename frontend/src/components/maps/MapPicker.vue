<script setup>
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import ProgressSpinner from 'primevue/progressspinner'

import {
  reverseGeocode,
  searchAddress
} from '@/services/geocoding'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  },

  latitude: {
    type: Number,
    default: null
  },

  longitude: {
    type: Number,
    default: null
  },

  zoom: {
    type: Number,
    default: 15
  },

  height: {
    type: String,
    default: '400px'
  },

  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'update:modelValue',
  'change',
  'search'
])

const mapContainer = ref(null)
const searchQuery = ref('')
const searchResults = ref([])
const loadingSearch = ref(false)
const loadingReverse = ref(false)
const errorMessage = ref('')

let map = null
let marker = null
let searchTimeout = null

const DEFAULT_LOCATION = [-6.200000, 106.816666]

const currentLocation = computed(() => {
  if (
    props.modelValue?.latitude != null &&
    props.modelValue?.longitude != null
  ) {
    return [
      props.modelValue.latitude,
      props.modelValue.longitude
    ]
  }

  if (
    props.latitude != null &&
    props.longitude != null
  ) {
    return [
      props.latitude,
      props.longitude
    ]
  }

  return DEFAULT_LOCATION
})

function createMarker(lat, lng) {
  if (!map) return

  if (marker) {
    marker.remove()
  }

  marker = L.marker([lat, lng], {
    draggable: !props.disabled
  }).addTo(map)

  marker.on('dragend', async (event) => {
    const position = event.target.getLatLng()

    await selectLocation(
      position.lat,
      position.lng,
      true
    )
  })
}

async function selectLocation(
  latitude,
  longitude,
  shouldReverseGeocode = true
) {
  errorMessage.value = ''

  createMarker(latitude, longitude)

  map?.setView(
    [latitude, longitude],
    Math.max(map.getZoom(), props.zoom)
  )

  let address = ''

  if (shouldReverseGeocode) {
    loadingReverse.value = true

    try {
      const result = await reverseGeocode(
        latitude,
        longitude
      )

      address = result.display_name || ''
    } catch (error) {
      errorMessage.value =
        'Lokasi berhasil dipilih, tetapi alamat tidak dapat ditemukan.'
    } finally {
      loadingReverse.value = false
    }
  }

  const location = {
    latitude,
    longitude,
    address
  }

  emit('update:modelValue', location)
  emit('change', location)
}

function handleMapClick(event) {
  if (props.disabled) return

  selectLocation(
    event.latlng.lat,
    event.latlng.lng
  )
}

function handleSearchInput() {
  clearTimeout(searchTimeout)

  if (searchQuery.value.trim().length < 3) {
    searchResults.value = []
    return
  }

  searchTimeout = setTimeout(
    performSearch,
    500
  )
}

async function performSearch() {
  loadingSearch.value = true
  errorMessage.value = ''

  try {
    const results = await searchAddress(
      searchQuery.value
    )

    searchResults.value = results

    emit('search', results)
  } catch (error) {
    errorMessage.value =
      'Gagal mencari alamat.'
  } finally {
    loadingSearch.value = false
  }
}

async function selectSearchResult(result) {
  const latitude = Number(result.lat)
  const longitude = Number(result.lon)

  searchQuery.value = result.display_name
  searchResults.value = []

  await selectLocation(
    latitude,
    longitude,
    false
  )

  await nextTick()

  const location = {
    latitude,
    longitude,
    address: result.display_name
  }

  emit('update:modelValue', location)
  emit('change', location)
}

function clearLocation() {
  if (marker) {
    marker.remove()
    marker = null
  }

  searchQuery.value = ''
  searchResults.value = []

  emit('update:modelValue', null)
  emit('change', null)
}

function initializeMap() {
  if (!mapContainer.value) return

  const [latitude, longitude] = currentLocation.value

  map = L.map(mapContainer.value).setView(
    [latitude, longitude],
    props.zoom
  )

  L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19
    }
  ).addTo(map)

  map.on('click', handleMapClick)

  if (
    props.modelValue?.latitude != null &&
    props.modelValue?.longitude != null
  ) {
    createMarker(
      props.modelValue.latitude,
      props.modelValue.longitude
    )
  } else if (
    props.latitude != null &&
    props.longitude != null
  ) {
    createMarker(
      props.latitude,
      props.longitude
    )
  }

  setTimeout(() => {
    map?.invalidateSize()
  }, 100)
}

watch(
  () => props.disabled,
  (disabled) => {
    if (marker) {
      marker.dragging[disabled ? 'disable' : 'enable']()
    }
  }
)

onMounted(() => {
  initializeMap()
})

onBeforeUnmount(() => {
  clearTimeout(searchTimeout)

  if (map) {
    map.remove()
    map = null
  }
})
</script>

<template>
  <div class="map-picker">

    <!-- Search -->
    <div class="flex gap-2 mb-3">
      <InputText v-model="searchQuery" class="w-full" placeholder="Cari alamat..." :disabled="disabled"
        @input="handleSearchInput" @keyup.enter="performSearch" />

      <Button icon="pi pi-search" :loading="loadingSearch" :disabled="disabled ||
        searchQuery.trim().length < 3
        " @click="performSearch" />

      <Button v-if="modelValue" icon="pi pi-times" severity="secondary" outlined :disabled="disabled"
        @click="clearLocation" />
    </div>

    <!-- Search result -->
    <div v-if="searchResults.length" class="border border-surface-200 rounded-lg mb-3 overflow-hidden">
      <button v-for="result in searchResults" :key="result.place_id" type="button"
        class="w-full text-left p-3 hover:bg-surface-100 border-b last:border-b-0 border-surface-200"
        @click="selectSearchResult(result)">
        <div class="flex gap-2">
          <i class="pi pi-map-marker mt-1" />

          <span class="text-sm">
            {{ result.display_name }}
          </span>
        </div>
      </button>
    </div>

    <!-- Error -->
    <Message v-if="errorMessage" severity="error" class="mb-3">
      {{ errorMessage }}
    </Message>

    <!-- Map -->
    <div ref="mapContainer" class="map-container rounded-lg overflow-hidden border border-surface-200"
      :style="{ height }" />

    <!-- Loading reverse geocode -->
    <div v-if="loadingReverse" class="flex items-center gap-2 mt-3 text-sm text-surface-500">
      <ProgressSpinner style="width: 18px; height: 18px" strokeWidth="4" />

      Mencari alamat...
    </div>

    <!-- Selected location -->
    <div v-if="modelValue" class="mt-3 p-3 rounded-lg bg-surface-50 border border-surface-200">
      <div class="flex gap-2">
        <i class="pi pi-map-marker mt-1" />

        <div class="text-sm">
          <div class="font-medium mb-1">
            Lokasi terpilih
          </div>

          <div class="text-surface-600">
            {{ modelValue.address || 'Alamat belum tersedia' }}
          </div>

          <div class="text-surface-500 mt-1">
            {{ modelValue.latitude }},
            {{ modelValue.longitude }}
          </div>
        </div>
      </div>
    </div>

  </div>
</template>