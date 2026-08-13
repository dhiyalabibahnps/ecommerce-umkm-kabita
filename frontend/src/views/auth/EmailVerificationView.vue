<script setup lang="ts">
import { CircleCheckBig } from '@lucide/vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

type StatusType = 'idle' | 'success';

type AuthErrorResponse = {
  response?: {
    data?: {
      message?: string;
    };
  };
};

const email = ref((route.query.email as string) || '');
const codeDigits = ref(['', '', '', '', '', '']);
const statusType = ref<StatusType>('idle');
const isVerifying = ref(false);
const isResending = ref(false);
const countdown = ref(60);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const code = computed(() => codeDigits.value.join(''));

function handleDigitInput(index: number, event: Event) {
  const target = event.target as HTMLInputElement;
  const nextValue = target.value.replace(/\D/g, '').slice(-1);

  codeDigits.value[index] = nextValue;

  if (nextValue && index < 5) {
    const inputs = document.querySelectorAll('.otp-input');
    (inputs[index + 1] as HTMLInputElement | undefined)?.focus();
  }
}

function handleBackspace(index: number) {
  if (codeDigits.value[index] === '' && index > 0) {
    const inputs = document.querySelectorAll('.otp-input');
    (inputs[index - 1] as HTMLInputElement | undefined)?.focus();
  } else {
    codeDigits.value[index] = '';
  }
}

// Fitur tambahan: Mendukung paste 6 digit OTP sekaligus
function handlePaste(event: ClipboardEvent) {
  event.preventDefault();
  const pastedData = event.clipboardData?.getData('text').trim() || '';
  const digits = pastedData.replace(/\D/g, '').slice(0, 6).split('');

  digits.forEach((digit, index) => {
    if (index < 6) {
      codeDigits.value[index] = digit;
    }
  });

  const nextIndex = Math.min(digits.length, 5);
  const inputs = document.querySelectorAll('.otp-input');
  (inputs[nextIndex] as HTMLInputElement | undefined)?.focus();
}

async function handleVerify() {
  if (code.value.length < 6) return;

  isVerifying.value = true;

  try {
    const response = await authStore.verifyEmail(email.value, code.value);

    if (response.success) {
      // 🟢 CASE 1: Input OTP Benar dan Sukses
      statusType.value = 'success';
      toast.add({
        severity: 'success',
        summary: 'Verifikasi Berhasil',
        detail: 'Email Anda berhasil diverifikasi.',
        life: 3000,
      });
      
      // Redirect ke login setelah verifikasi berhasil
      // setTimeout(() => {
      //   router.push('/login');
      // }, 2000);
    } else {
      // 🟡 CASE 3: Input OTP Tidak Valid (Respons API success: false)
      toast.add({
        severity: 'warn',
        summary: 'OTP Tidak Valid',
        detail: response.message || 'Kode OTP tidak valid atau telah kedaluwarsa.',
        life: 4000,
      });
    }
  } catch (error) {
    // 🔴 CASE 2: API Error / Error dari Sistem (Status Code Non-200/201)
    const authError = error as AuthErrorResponse;
    const errorMessage =
      authError.response?.data?.message ||
      'Gagal verifikasi OTP karena terjadi kesalahan pada sistem.';

    toast.add({
      severity: 'error',
      summary: 'Gagal Verifikasi',
      detail: errorMessage,
      life: 4000,
    });
  } finally {
    isVerifying.value = false;
  }
}

async function handleResend() {
  isResending.value = true;

  try {
    const response = await authStore.resendCode(email.value);

    if (response.success) {
      toast.add({
        severity: 'info',
        summary: 'Kode Terkirim',
        detail: 'Kode verifikasi baru telah dikirim ke email Anda.',
        life: 3000,
      });
      countdown.value = 60;
      startCountdown();
    } else {
      toast.add({
        severity: 'error',
        summary: 'Gagal Mengirim Kode',
        detail: response.message || 'Gagal mengirim ulang kode verifikasi.',
        life: 4000,
      });
    }
  } catch (error) {
    const authError = error as AuthErrorResponse;
    toast.add({
      severity: 'error',
      summary: 'Error Sistem',
      detail:
        authError.response?.data?.message ||
        'Terjadi kesalahan saat mengirim ulang kode.',
      life: 4000,
    });
  } finally {
    isResending.value = false;
  }
}

function handleStartShopping() {
  router.push('/');
}

function startCountdown() {
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }

  countdownInterval = setInterval(() => {
    countdown.value -= 1;
    if (countdown.value <= 0) {
      clearInterval(
        countdownInterval as ReturnType<typeof setInterval>
      );
      countdownInterval = null;
    }
  }, 1000);
}

onMounted(() => {
  startCountdown();
});

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }
});
</script>

<template>
  <div class="min-h-screen max-w-150 mx-auto bg-gray-50 flex flex-col justify-center -my-32 sm:px-6 lg:px-8">
    <div class="mt-8 sm:mx-auto w-full">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div v-if="statusType === 'idle'">
          <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-primary-600">Kabita</h1>
            <h2 class="mt-5 text-2xl font-semibold text-gray-900">
              Verifikasi Email Anda
            </h2>
            <p class="mt-3 text-sm leading-6 text-gray-600">
              Kami telah mengirimkan kode verifikasi 6 digit ke
              <span class="font-semibold text-gray-900">{{
                email
              }}</span>
            </p>
          </div>

          <div class="space-y-6">
            <div class="flex justify-center gap-2 sm:gap-3" @paste="handlePaste">
              <InputText v-for="(digit, index) in codeDigits" :key="index" v-model="codeDigits[index]" maxlength="1"
                inputmode="numeric" autocomplete="one-time-code"
                class="otp-input h-14 w-12 rounded-xl border border-gray-300 text-center text-2xl font-semibold text-gray-900 shadow-sm transition-all focus:border-primary-600 focus:ring-2 focus:ring-primary-100 sm:w-14"
                @input="handleDigitInput(index, $event)" @keydown.backspace="handleBackspace(index)" />
            </div>

            <div class="text-center">
              <p class="text-sm text-gray-600">
                Tidak menerima kode?
                <Button v-if="countdown === 0" type="button"
                  class="ml-1 font-semibold text-primary-600 transition hover:text-primary-500 disabled:opacity-50"
                  :disabled="isResending" @click="handleResend">
                  {{ isResending ? 'Mengirim...' : 'Kirim ulang' }}
                </Button>
                <span v-else class="text-gray-500">
                  Kirim ulang dalam {{ countdown }} detik
                </span>
              </p>
            </div>

            <Button label="Verifikasi" :loading="isVerifying" :disabled="code.length < 6" class="w-full"
              @click="handleVerify" />
          </div>
        </div>

        <div v-if="statusType === 'success'" class="flex flex-col items-center justify-center gap-y-8 text-center">
          <CircleCheckBig class="text-green-500" :size="96" />
          <div class="flex flex-col gap-y-2">
            <p class="text-2xl text-black font-bold">
              Email Berhasil Diverifikasi
            </p>
            <p class="text-sm text-black">
              Akun Anda telah aktif. Sekarang Anda dapat mulai
              menjelajahi produk UMKM terbaik di Kabita.
            </p>
          </div>
          <Button type="button"
            class="ml-1 font-semibold text-primary-600 transition hover:text-primary-500 disabled:opacity-50 w-full"
            fluid @click="handleStartShopping">
            Mulai Belanja
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>