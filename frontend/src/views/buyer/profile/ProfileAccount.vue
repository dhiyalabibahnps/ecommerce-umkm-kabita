<script setup lang="ts">
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import RadioButton from 'primevue/radiobutton'
import Select from 'primevue/select'
import { ref } from 'vue'

// State Form
const profile = ref({
  username: 'adminkabita',
  fullname: 'Admin Kabita',
  email: 'ad***@kabita.id',
  phone: '**** **** **98',
  gender: 'Perempuan',
  day: 12,
  month: 'Agustus',
  year: 1990
})

// Options Dropdown
const days = Array.from({ length: 31 }, (_, i) => i + 1)
const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const years = Array.from({ length: 50 }, (_, i) => 2026 - i)
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

    <!-- Form + Upload Layout -->
    <div class="flex flex-col-reverse lg:flex-row gap-8">
      <!-- Area Form (Kiri) -->
      <div class="flex-1 space-y-5">
        <!-- Username -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Username</label>
          <div class="flex-1">
            <InputText v-model="profile.username" class="w-full bg-slate-100! border-none! text-sm!" readonly />
          </div>
        </div>

        <!-- Nama Lengkap -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Nama Lengkap</label>
          <div class="flex-1">
            <InputText v-model="profile.fullname" class="w-full text-sm!" />
          </div>
        </div>

        <!-- Email -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Email</label>
          <div class="flex-1 flex items-center justify-between text-sm">
            <span class="text-slate-700 font-medium">{{ profile.email }}</span>
          </div>
        </div>

        <!-- Nomor Telepon -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Nomor Telepon</label>
          <div class="flex-1 flex items-center justify-between text-sm">
            <span class="text-slate-700 font-medium">{{ profile.phone }}</span>
            <button type="button" class="text-blue-600 text-xs font-semibold hover:underline">Ubah</button>
          </div>
        </div>

        <!-- Jenis Kelamin -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Jenis Kelamin</label>
          <div class="flex-1 flex items-center gap-6">
            <div class="flex items-center gap-2">
              <RadioButton v-model="profile.gender" inputId="gender1" value="Laki-laki" />
              <label for="gender1" class="text-xs text-slate-700 cursor-pointer">Laki-laki</label>
            </div>
            <div class="flex items-center gap-2">
              <RadioButton v-model="profile.gender" inputId="gender2" value="Perempuan" />
              <label for="gender2" class="text-xs text-slate-700 cursor-pointer">Perempuan</label>
            </div>
            <div class="flex items-center gap-2">
              <RadioButton v-model="profile.gender" inputId="gender3" value="Lainnya" />
              <label for="gender3" class="text-xs text-slate-700 cursor-pointer">Lainnya</label>
            </div>
          </div>
        </div>

        <!-- Tanggal Lahir -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label class="w-full sm:w-32 text-xs font-medium text-slate-500">Tanggal Lahir</label>
          <div class="flex-1 grid grid-cols-3 gap-2">
            <Select v-model="profile.day" :options="days" class="w-full text-xs!" />
            <Select v-model="profile.month" :options="months" class="w-full text-xs!" />
            <Select v-model="profile.year" :options="years" class="w-full text-xs!" />
          </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="pt-4 flex justify-end">
          <Button label="Simpan Perubahan" class="bg-blue-600! border-blue-600! px-6! py-2.5! text-xs! rounded-xl!" />
        </div>
      </div>

      <!-- Area Upload Foto (Kanan) -->
      <div
        class="w-full lg:w-64 flex flex-col items-center justify-start border-b lg:border-b-0 lg:border-l border-slate-100 pb-6 lg:pb-0 lg:pl-8">
        <img src="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" alt="Profile"
          class="w-28 h-28 rounded-full object-cover mb-4 border border-slate-100 shadow-sm" />
        <Button label="Pilih Gambar" severity="secondary" outlined
          class="text-xs! px-4! py-2! rounded-xl! border-slate-300!" />
        <p class="text-[11px] text-slate-400 mt-3 text-center leading-relaxed">
          Ukuran gambar: maks. 1 MB<br />Format: .JPEG, .PNG
        </p>
      </div>
    </div>
  </div>
</template>