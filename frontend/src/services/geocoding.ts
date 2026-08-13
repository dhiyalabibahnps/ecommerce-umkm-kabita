export interface NominatimAddress {
  road?: string
  suburb?: string
  city?: string
  state?: string
  postcode?: string
  country?: string
  country_code?: string
}

export interface NominatimSearchResult {
  place_id: number
  display_name: string
  lat: string
  lon: string
  address?: NominatimAddress
}

export interface NominatimReverseResult {
  place_id: number
  display_name: string
  address?: NominatimAddress
  lat: string
  lon: string
}

const NOMINATIM_URL = 'https://nominatim.openstreetmap.org'

export async function searchAddress(query: string): Promise<NominatimSearchResult[]> {
  if (!query || query.trim().length < 3) {
    return []
  }

  const url = new URL(`${NOMINATIM_URL}/search`)

  url.searchParams.set('format', 'jsonv2')
  url.searchParams.set('q', query)
  url.searchParams.set('limit', '5')
  url.searchParams.set('addressdetails', '1')
  url.searchParams.set('countrycodes', 'id')

  const response = await fetch(url)

  if (!response.ok) {
    throw new Error('Gagal melakukan pencarian alamat')
  }

  return await response.json()
}

export async function reverseGeocode(latitude: number, longitude: number): Promise<NominatimReverseResult> {
  const url = new URL(`${NOMINATIM_URL}/reverse`)

  url.searchParams.set('format', 'jsonv2')
  url.searchParams.set('lat', String(latitude))
  url.searchParams.set('lon', String(longitude))
  url.searchParams.set('addressdetails', '1')

  const response = await fetch(url)

  if (!response.ok) {
    throw new Error('Gagal mendapatkan alamat')
  }

  return await response.json()
}