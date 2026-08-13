import axios, {
  type AxiosInstance,
  type AxiosResponse,
  type InternalAxiosRequestConfig
} from 'axios';
import router from '../router';

// Konfigurasi dasar Axios
const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // withCredentials: true, // Aktifkan ini jika nanti pakai Sanctum Cookie-based auth
});

// Request Interceptor: Otomatis suntikkan Bearer Token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response Interceptor: Handle error global (misal: Token expired 401)
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    return response;
  },
  (error) => {
    // Jika status 401 (Unauthorized), hapus token dan redirect ke login
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      
      // Hindari infinite loop jika user sudah di halaman login
      if (router.currentRoute.value.path !== '/') {
        router.push('/');
      }
    }
    return Promise.reject(error);
  }
);

export default apiClient;