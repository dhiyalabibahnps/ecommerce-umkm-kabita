<template>
  <div class="min-h-screen max-w-150 mx-auto bg-gray-50 flex flex-col justify-center -my-32 sm:px-6 lg:px-8">

    <div class="mt-8 sm:mx-auto w-full">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="mb-12">
          <h1 class="text-center text-3xl font-bold text-primary-600">Kabita</h1>
          <h2 class="mt-6 text-center text-2xl font-bold text-gray-900">
            Selamat Datang Kembali
          </h2>
          <p class="mt-2 text-center text-sm text-gray-600">
            Masuk untuk melanjutkan belanja
          </p>
        </div>
        <form class="space-y-6" @submit.prevent="handleLogin">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email
            </label>
            <div class="mt-1">
              <InputText id="email" v-model="form.email" type="email" placeholder="nama@email.com" class="w-full"
                :class="{ 'p-invalid': errors.email }" />
              <small v-if="errors.email" class="p-error">{{ errors.email[0] }}</small>
            </div>
          </div>

          <!-- Password -->
          <div class="w-full">
            <label for="password" class="block text-sm font-medium text-gray-700">
              Kata Sandi
            </label>

            <div class="mt-1">
              <Password v-model="form.password" inputId="password" :feedback="false" toggleMask placeholder="••••••••"
                fluid class="w-full!" :class="{ 'p-invalid': errors.password }" />

              <small v-if="errors.password" class="p-error">
                {{ errors.password[0] }}
              </small>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <Checkbox v-model="form.remember" :binary="true" inputId="remember" />
              <label for="remember" class="ml-2 block text-sm text-gray-900">
                Ingat saya
              </label>
            </div>

            <div class="text-sm">
              <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                Lupa password?
              </a>
            </div>
          </div>

          <!-- Verification Notice -->
          <div v-if="verificationNotice" class="rounded-md bg-yellow-50 p-4">
            <div class="flex">
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">{{ verificationNotice }}</h3>
                <div class="mt-2 text-sm text-yellow-700">
                  <router-link to="/verify-email" class="font-medium text-yellow-800 underline">
                    Verifikasi email sekarang
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">{{ errorMessage }}</h3>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <Button type="submit" label="Masuk" :loading="isLoading" class="w-full" />
          </div>

          <!-- Register Link -->
          <div class="text-center">
            <p class="text-sm text-gray-600">
              Belum punya akun?
              <router-link to="/register" class="font-medium text-primary-600 hover:text-primary-500">
                Daftar sekarang
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

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const errors = ref<Record<string, string[]>>({});
const errorMessage = ref('');
const verificationNotice = ref('');
const isLoading = ref(false);

async function handleLogin() {
  isLoading.value = true;
  errorMessage.value = '';
  verificationNotice.value = '';
  errors.value = {};

  try {
    const response = await authStore.login({
      email: form.email,
      password: form.password,
      remember: form.remember,
    });

    if (!response.success) {
      // Cek apakah error karena email belum terverifikasi
      if (response.message?.toLowerCase().includes('verif')) {
        verificationNotice.value = response.message || 'Email belum diverifikasi.';
      } else {
        errorMessage.value = response.message || 'Login gagal';
      }
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      const message = error.response?.data?.message || 'Terjadi kesalahan';

      // Cek apakah error karena email belum terverifikasi
      if (message.toLowerCase().includes('verif')) {
        verificationNotice.value = message;
      } else {
        errorMessage.value = message;
      }
    }
  } finally {
    isLoading.value = false;
  }
}

function socialLogin(provider: string) {
  // TODO: Implement social login
  console.log(`Login with ${provider}`);
}
</script>