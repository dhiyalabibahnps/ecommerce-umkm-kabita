<template>
  <div class="min-h-screen max-w-120 mx-auto bg-gray-50 flex flex-col justify-center -my-32 sm:px-6 lg:px-8">

    <div>
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="mb-7">
          <h1 class="text-center text-3xl font-bold text-primary-600">Kabita</h1>
          <h2 class="mt-6 text-center text-2xl font-bold text-gray-900">
            Buat Akun Baru
          </h2>
          <p class="mt-2 text-center text-sm text-gray-600">
            Bergabung dengan UMKM Kabita
          </p>
        </div>
        <!-- Step 1: Pilih Role -->
        <div v-if="step === 1" class="space-y-6">
          <p class="text-center text-sm text-gray-600">Saya mendaftar sebagai:</p>

          <div class="grid grid-cols-2 gap-4">
            <div class="border-2 rounded-lg p-6 text-center cursor-pointer transition-all hover:border-primary-500"
              :class="selectedRole === 'buyer' ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
              @click="selectedRole = 'buyer'">
              <i class="pi pi-shopping-cart text-3xl text-primary-600 mb-2"></i>
              <h3 class="font-semibold text-gray-900">Pembeli</h3>
              <p class="text-xs text-gray-500 mt-1">Saya ingin berbelanja produk UMKM</p>
            </div>

            <div class="border-2 rounded-lg p-6 text-center cursor-pointer transition-all hover:border-primary-500"
              :class="selectedRole === 'seller' ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
              @click="selectedRole = 'seller'">
              <i class="pi pi-shop text-3xl text-primary-600 mb-2"></i>
              <h3 class="font-semibold text-gray-900">Penjual</h3>
              <p class="text-xs text-gray-500 mt-1">Saya ingin menjual produk di Kabita</p>
            </div>
          </div>

          <Button label="Lanjutkan" :disabled="!selectedRole" class="w-full" @click="step = 2" />

          <div class="text-center">
            <p class="text-sm text-gray-600">
              Sudah punya akun?
              <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
                Masuk di sini
              </router-link>
            </p>
          </div>
        </div>

        <!-- Step 2: Form Register -->
        <form v-else class="space-y-6" @submit.prevent="handleRegister">
          <!-- Nama -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ selectedRole === 'seller' ? 'Nama Lengkap Pemilik' : 'Nama Lengkap' }}
            </label>
            <InputText v-model="form.name" placeholder="Cth. Budi Santoso" class="w-full mt-1"
              :class="{ 'p-invalid': errors.name }" />
            <small v-if="errors.name" class="p-error">{{ errors.name[0] }}</small>
          </div>

          <!-- Nama Toko (hanya untuk seller) -->
          <div v-if="selectedRole === 'seller'">
            <label class="block text-sm font-medium text-gray-700">
              Nama Toko
            </label>
            <InputText v-model="form.shop_name" placeholder="Cth. Kopi Budi Jaya" class="w-full mt-1"
              :class="{ 'p-invalid': errors.shop_name }" />
            <small v-if="errors.shop_name" class="p-error">{{ errors.shop_name[0] }}</small>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Alamat Email
            </label>
            <InputText v-model="form.email" type="email" placeholder="budi@contoh.com" class="w-full mt-1"
              :class="{ 'p-invalid': errors.email }" />
            <small v-if="errors.email" class="p-error">{{ errors.email[0] }}</small>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Nomor WhatsApp
            </label>
            <InputText v-model="form.phone" placeholder="0812xxxx" class="w-full mt-1"
              :class="{ 'p-invalid': errors.phone }" />
            <small v-if="errors.phone" class="p-error">{{ errors.phone[0] }}</small>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Kata Sandi
            </label>

            <div class="mt-1">
              <Password v-model="form.password" inputId="password" :feedback="false" toggleMask placeholder="••••••••"
                unstyled :class="{ 'p-invalid': errors.password }" :pt="{
                  root: {
                    class: 'relative block w-full'
                  },
                  input: {
                    class: 'w-full h-9 rounded-md border border-gray-300 bg-white px-3 pr-10 text-sm text-gray-700 outline-none placeholder:text-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500'
                  },
                  showIcon: {
                    class: 'text-gray-400'
                  },
                  hideIcon: {
                    class: 'text-gray-400'
                  }
                }" />

              <small v-if="errors.password" class="p-error">
                {{ errors.password[0] }}
              </small>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Konfirmasi Kata Sandi
            </label>
            <Password v-model="form.password_confirmation" :feedback="false" toggleMask placeholder="••••••••"
              class="w-full mt-1" :class="{ 'p-invalid': errors.password_confirmation }" />
            <small v-if="errors.password_confirmation" class="p-error">{{ errors.password_confirmation[0] }}</small>
          </div>

          <!-- Terms -->
          <div class="flex items-start">
            <Checkbox v-model="form.agree_terms" :binary="true" inputId="terms" />
            <label for="terms" class="ml-2 block text-sm text-gray-900">
              Saya menyetujui <a href="#" class="text-primary-600 hover:underline">Syarat dan Ketentuan</a> serta <a
                href="#" class="text-primary-600 hover:underline">Privasi Kabita</a>
            </label>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
            <h3 class="text-sm font-medium text-red-800">{{ errorMessage }}</h3>
          </div>

          <!-- Success Message -->
          <div v-if="successMessage" class="rounded-md bg-green-50 p-4">
            <h3 class="text-sm font-medium text-green-800">{{ successMessage }}</h3>
            <p class="mt-2 text-sm text-green-700">
              Silakan periksa email Anda untuk kode verifikasi.
            </p>
          </div>

          <!-- Submit -->
          <Button type="submit" :label="selectedRole === 'seller' ? 'Daftar Toko Sekarang' : 'Daftar Sekarang'"
            :loading="isLoading" class="w-full" />

          <div class="text-center">
            <p class="text-sm text-gray-600">
              Sudah punya akun?
              <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
                Masuk di sini
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const step = ref(1);
const selectedRole = ref<'buyer' | 'seller' | null>(null);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  shop_name: '',
  agree_terms: false,
});

const errors = ref<Record<string, string[]>>({});
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);

async function handleRegister() {
  if (!form.agree_terms) {
    errorMessage.value = 'Anda harus menyetujui Syarat dan Ketentuan';
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    const response = await authStore.register({
      name: form.name,
      email: form.email,
      phone: form.phone,
      password: form.password,
      password_confirmation: form.password_confirmation,
      role: selectedRole.value!,
      shop_name: selectedRole.value === 'seller' ? form.shop_name : undefined,
    });

    if (response.success) {
      successMessage.value = 'Pendaftaran berhasil!';
      
      // Redirect ke halaman verifikasi email setelah 1 detik
      setTimeout(() => {
        router.push({
          name: 'verify-email',
          query: { email: form.email },
        });
      }, 1000);
    } else {
      errorMessage.value = response.message || 'Registrasi gagal';
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan';
    }
  } finally {
    isLoading.value = false;
  }
}
</script>