<script setup lang="ts">
</script>

<template>
  <footer class="bg-[#faf8ff] mt-auto py-12">
    <div class="container max-w-7xl mx-auto px-4">
      <div class="grid gap-8 md:grid-cols-4">
        <div>
          <h4 class="font-bold mb-3 text-lg text-slate-950">Kabita</h4>
          <p class="text-slate-600">Memberdayakan UMKM lokal untuk pasar global.</p>
        </div>
        <div>
          <h4 class="font-bold mb-3 text-lg text-slate-950">Perusahaan</h4>
          <ul class="space-y-2 text-slate-600">
            <li><router-link to="/" class="no-underline hover:text-slate-900">About Us</router-link></li>
            <li><router-link to="/" class="no-underline hover:text-slate-900">Contact</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="font-bold mb-3 text-lg text-slate-950">Bantuan</h4>
          <ul class="space-y-2 text-slate-600">
            <li><router-link to="/" class="no-underline hover:text-slate-900">Help Center</router-link></li>
            <li><router-link to="/" class="no-underline hover:text-slate-900">Panduan Belanja</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="font-bold mb-3 text-lg text-slate-950">Legal</h4>
          <ul class="space-y-2 text-slate-600">
            <li><router-link to="/" class="no-underline hover:text-slate-900">Privacy Policy</router-link></li>
            <li><router-link to="/" class="no-underline hover:text-slate-900">Terms of Service</router-link></li>
          </ul>
        </div>
      </div>
      <div
        class="mt-10 border-t border-slate-200 pt-6 text-slate-500 text-sm md:flex md:items-center md:justify-between">
        <p>© 2024 Kabita UMKM Marketplace. All rights reserved.</p>
        <p>Memberdayakan UMKM lokal untuk pasar global.</p>
      </div>
    </div>
  </footer>
</template>
