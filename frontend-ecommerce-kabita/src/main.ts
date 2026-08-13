import 'primeicons/primeicons.css';
// import './assets/main.css'

import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import { createApp } from 'vue';

import App from './App.vue';
import KabitaPreset from './KabitaPreset.ts';
import router from './router';
const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(PrimeVue, {
  theme: {
    preset: KabitaPreset, options: {
      darkModeSelector: false // Disables dark mode completely
    }
  }
})
app.use(ToastService)

app.mount('#app')