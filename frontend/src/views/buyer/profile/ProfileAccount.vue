<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { computed, onMounted, ref } from 'vue'

const authStore = useAuthStore()
const toast = useToast()

const isLoading = ref(true)
const errorMessage = ref('')
const isSaving = ref(false)
const saveError = ref('')

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
})

const photoPreview = ref<string | null>(null)
const photoFile = ref<File | null>(null)
const fileName = ref('')
const profilePhotoInput = ref<HTMLInputElement | null>(null)

const isFormValid = computed(() => {
  return (
    form.value.name.trim().length > 0 &&
    form.value.email.trim().length > 0 &&
    form.value.phone.trim().length > 0 &&
    form.value.address.trim().length > 0
  )
})

function fileToCompressedDataUrl(file: File, maxWidth = 800, quality = 0.5): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        let width = img.width
        let height = img.height

        if (width > maxWidth) {
          height = (maxWidth / width) * height
          width = maxWidth
        }

        canvas.width = width
        canvas.height = height

        const ctx = canvas.getContext('2d')
        if (!ctx) {
          reject(new Error('Gagal memproses gambar.'))
          return
        }

        ctx.drawImage(img, 0, 0, width, height)
        resolve(canvas.toDataURL('image/jpeg', quality))
      }
      img.onerror = () => reject(new Error('Gagal memuat gambar.'))
      img.src = reader.result as string
    }
    reader.onerror = () => reject(new Error('Gagal membaca file.'))
    reader.readAsDataURL(file)
  })
}

async function handlePhotoChange(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] || null
  photoFile.value = null
  photoPreview.value = null
  fileName.value = ''

  if (!file) {
    return
  }

  if (!file.type.startsWith('image/')) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: 'File harus berupa gambar.',
      life: 3000,
    })
    input.value = ''
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: 'Ukuran gambar maksimal 2 MB.',
      life: 3000,
    })
    input.value = ''
    return
  }

  try {
    const compressed = await fileToCompressedDataUrl(file)
    photoPreview.value = compressed
    photoFile.value = file
    fileName.value = file.name
  } catch (err) {
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: getApiErrorMessage(err, 'Gagal memproses gambar.'),
      life: 3000,
    })
    input.value = ''
  }
}

async function submit() {
  saveError.value = ''
  if (!isFormValid.value) {
    toast.add({
      severity: 'warn',
      summary: 'Validasi',
      detail: 'Semua field wajib diisi.',
      life: 3000,
    })
    return
  }

  isSaving.value = true
  try {
    const formData = new FormData()
    formData.append('name', form.value.name.trim())
    formData.append('phone', form.value.phone.trim())
    formData.append('address', form.value.address.trim())
    formData.append('email', form.value.email.trim())

    if (photoFile.value) {
      formData.append('photo', photoFile.value)
    }

    const response = await authStore.updateProfile({
      name: form.value.name.trim(),
      phone: form.value.phone.trim(),
      address: form.value.address.trim(),
      email: form.value.email.trim(),
      photo: photoFile.value,
    })

    if (response.success) {
      toast.add({
        severity: 'success',
        summary: 'Berhasil',
        detail: response.message || 'Profil berhasil diperbarui.',
        life: 3000,
      })
    } else {
      throw new Error(response.message || 'Gagal memperbarui profil.')
    }
  } catch (error) {
    const message = getApiErrorMessage(error, 'Gagal memperbarui profil.')
    saveError.value = message
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: message,
      life: 3000,
    })
  } finally {
    isSaving.value = false
  }
}

function getApiErrorMessage(error: unknown, fallback: string) {
  if (error && typeof error === 'object' && 'response' in error) {
    const axiosError = error as { response?: { data?: { message?: string } } }
    return axiosError.response?.data?.message || fallback
  }

  if (error instanceof Error) {
    return error.message
  }

  return fallback
}

onMounted(async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    await authStore.fetchUser()
    const user = authStore.user
    if (user) {
      form.value.name = user.name
      form.value.email = user.email
      form.value.phone = user.phone || ''
      form.value.address = user.address || ''
      photoPreview.value = user.photo || null
    }
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Profil belum dapat dimuat.')
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8 border border-slate-100">
    <!-- Header Konten -->
    <div class="pb-6 mb-6 border-b border-slate-100">
      <h1 class="text-lg font-bold text-slate-800">Profil Saya</h1>
      <p class="text-xs text-slate-500 mt-1">
        Kelola informasi profil Anda untuk mengontrol, melindungi dan mengamankan akun.
      </p>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center gap-3 py-20 text-slate-500">
      <ProgressSpinner style="width: 42px; height: 42px" />
      <span class="text-sm">Memuat profil...</span>
    </div>

    <div v-else-if="errorMessage" class="py-16 text-center">
      <p class="text-sm text-rose-600">{{ errorMessage }}</p>
      <Button label="Coba lagi" icon="pi pi-refresh" outlined class="mt-4 rounded-xl!" @click="authStore.fetchUser()" />
    </div>

    <div v-else class="flex flex-col-reverse lg:flex-row gap-8">
      <!-- Area Form (Kiri) -->
      <div class="flex-1 space-y-5">
        <!-- Nama Lengkap -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Nama Lengkap <span
              class="text-rose-500">*</span></label>
          <div class="flex-1">
            <InputText v-model="form.name" class="w-full text-sm!" placeholder="Nama lengkap" />
          </div>
        </div>

        <!-- Email -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Email</label>
          <div class="flex-1">
            <InputText :modelValue="form.email" class="w-full bg-slate-100! border-none! text-sm!" readonly />
          </div>
        </div>

        <!-- Nomor Telepon -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Nomor Telepon <span
              class="text-rose-500">*</span></label>
          <div class="flex-1">
            <InputText v-model="form.phone" class="w-full text-sm!" placeholder="Nomor telepon" />
          </div>
        </div>

        <!-- Alamat -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Alamat <span
              class="text-rose-500">*</span></label>
          <div class="flex-1">
            <InputText v-model="form.address" class="w-full text-sm!" placeholder="Alamat" />
          </div>
        </div>

        <div v-if="saveError" class="text-xs text-rose-600">{{ saveError }}</div>

        <!-- Tombol Simpan -->
        <div class="pt-4 flex justify-end">
          <Button label="Simpan Perubahan" class="bg-blue-600! border-blue-600! px-6! py-2.5! text-xs! rounded-xl!"
            :disabled="!isFormValid || isSaving" @click="submit" />
        </div>
      </div>

      <!-- Area Upload Foto (Kanan) -->
      <div
        class="w-full lg:w-64 flex flex-col items-center justify-start border-b lg:border-b-0 lg:border-l border-slate-100 pb-6 lg:pb-0 lg:pl-8">

        <div class="mb-2">
          <div v-if="photoPreview">
            <img :src="photoPreview || 'https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png'" alt="Profile"
              class="w-28 h-28 rounded-full object-cover mb-4 border border-slate-100 shadow-sm" />
          </div>
          <div v-else>
            <Avatar icon="pi pi-user" size="xlarge" style="background-color: #dee9fc; color: #1a2551" shape="circle" />
          </div>
        </div>

        <!-- <img :src="photoPreview || 'https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png'" alt="Profile"
          class="w-28 h-28 rounded-full object-cover mb-4 border border-slate-100 shadow-sm" /> -->
        <input id="profile-photo" ref="profilePhotoInput" type="file" accept="image/*" class="hidden"
          @change="handlePhotoChange" />
        <Button label="Pilih Gambar" severity="secondary" outlined
          class="text-xs! px-4! py-2! rounded-xl! border-slate-300!" @click="profilePhotoInput?.click()" />
        <p v-if="fileName" class="text-[11px] text-slate-500 mt-2 text-center">{{ fileName }}</p>
        <p class="text-[11px] text-slate-400 mt-3 text-center leading-relaxed">
          Ukuran gambar: maks. 2 MB<br />Format: .JPEG, .PNG
        </p>
      </div>
    </div>
  </div>
</template>