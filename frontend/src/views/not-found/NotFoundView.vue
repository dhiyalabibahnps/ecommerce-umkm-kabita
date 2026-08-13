<script setup lang="ts">
import Button from 'primevue/button';
import { useRouter } from 'vue-router';

const router = useRouter()

function goHome() {
  router.push('/')
}

</script>

<template>
  <main class="flex min-h-screen flex-col bg-slate-50 py-16">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-2 items-center px-4">
      <div class="flex justify-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 text-blue-700 text-3xl">
          <i class="pi pi-search"></i>
        </div>
      </div>

      <div class="text-center">
        <h1 class="text-4xl font-bold text-slate-950">Wah, Halaman Tidak Ditemukan!</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">
          Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan. Yuk, kembali ke halaman utama untuk lanjut
          belanja.
        </p>
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
          <Button label="Kembali ke Beranda" severity="primary" icon="pi pi-home" class="min-w-[220px]"
            @click="goHome" />
        </div>
      </div>
    </div>
  </main>
</template>
