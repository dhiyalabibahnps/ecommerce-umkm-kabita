<script setup lang="ts">
import { searchAddress } from '@/services/geocoding'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

// Fix default Leaflet marker icon in Vite/Vue bundler.
const leafletIcon = L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
})
L.Marker.prototype.options.icon = leafletIcon

const props = withDefaults(
  defineProps<{
    address?: string | null
    latitude?: number | null
    longitude?: number | null
    height?: string
  }>(),
  {
    address: null,
    latitude: null,
    longitude: null,
    height: '220px'
  }
)

const mapContainer = ref<HTMLDivElement | null>(null)

let map: L.Map | null = null
let marker: L.Marker | null = null

const DEFAULT_LOCATION: [number, number] = [-6.200000, 106.816666]

function createMarker(lat: number, lng: number) {
  if (!map) return
  if (marker) marker.remove()
  marker = L.marker([lat, lng]).addTo(map)
}

async function markPosition() {
  if (!map) return

  // Prefer backend-provided coordinates (from CodLocation)
  if (props.latitude != null && props.longitude != null) {
    const lat = Number(props.latitude)
    const lng = Number(props.longitude)
    createMarker(lat, lng)
    map.setView([lat, lng], 15)
    return
  }

  // Fallback: geocode from address text via Nominatim
  if (!props.address || props.address.trim().length < 5) return

  try {
    const results = await searchAddress(props.address)
    if (results.length > 0 && results[0]) {
      const lat = Number(results[0].lat)
      const lng = Number(results[0].lon)
      createMarker(lat, lng)
      map.setView([lat, lng], 15)
    }
  } catch {
    // Geocode gagal — biarkan peta tanpa marker
  }
}

function initializeMap() {
  if (!mapContainer.value) return

  map = L.map(mapContainer.value).setView(DEFAULT_LOCATION, 12)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map)

  markPosition()

  setTimeout(() => {
    map?.invalidateSize()
  }, 100)
}

watch(() => [props.latitude, props.longitude, props.address] as const, () => markPosition())

onMounted(() => {
  initializeMap()
})

onBeforeUnmount(() => {
  if (map) {
    map.remove()
    map = null
  }
})
</script>

<template>
  <div class="order-map-preview">
    <div ref="mapContainer" class="map-container rounded-lg overflow-hidden border border-slate-200"
      :style="{ height }" />

    <div v-if="address" class="mt-2 text-[11px] text-slate-500 leading-relaxed">
      <div class="flex items-start gap-1.5">
        <i class="pi pi-map-marker text-blue-500 mt-0.5" />
        <span class="wrap-break-word whitespace-normal">{{ address }}</span>
      </div>

      <a v-if="address"
        :href="address ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(latitude != null && longitude != null ? `${latitude},${longitude}` : address)}` : '#'"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center gap-1 mt-1 text-blue-600 hover:text-blue-700 font-medium">
        <i class="pi pi-external-link text-[10px]" />
        <span>Buka di Google Maps</span>
      </a>
    </div>
  </div>
</template>

<style scoped>
.map-container {
  background: #e5e7eb;
}
</style>
