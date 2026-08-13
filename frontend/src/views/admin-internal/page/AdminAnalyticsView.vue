<script setup lang="ts">
import ProgressSpinner from 'primevue/progressspinner';
import { onMounted, ref } from 'vue';
import * as XLSX from 'xlsx';
// @ts-ignore
import html2pdf from 'html2pdf.js';

// Import Child Components
import { useToast } from 'primevue/usetoast';
import AdminAnalyticsHeader from '../components/analytics/AdminAnalyticsHeader.vue';
import AdminAnalyticsStatCards, { type StatMetric } from '../components/analytics/AdminAnalyticsStatCards.vue';
import AdminCategoryRevenueChart from '../components/analytics/AdminCategoryRevenueChart.vue';
import AdminDailySalesChart from '../components/analytics/AdminDailySalesChart.vue';
import AdminTopProductsTable, { type TopProductItem } from '../components/analytics/AdminTopProductsTable.vue';
import AdminTopShopsChart from '../components/analytics/AdminTopShopsChart.vue';

const isLoading = ref<boolean>(true);
const analyticsContainer = ref<HTMLElement | null>(null);
const toast = useToast();

// Mock Data State
const statCards = ref<StatMetric[]>([]);
const topProducts = ref<TopProductItem[]>([]);

// Fetch Data Simulation
const fetchAnalyticsData = () => {
  isLoading.value = true;

  setTimeout(() => {
    // Stat Cards Mock Data
    statCards.value = [
      {
        title: 'Total Revenue',
        value: 'Rp 45.2jt',
        percentage: '15%',
        isPositive: true,
        icon: 'pi pi-wallet'
      },
      {
        title: 'Total Orders',
        value: '342',
        percentage: '8%',
        isPositive: true,
        icon: 'pi pi-shopping-bag'
      },
      {
        title: 'Total Products Sold',
        value: '1,247',
        percentage: '12%',
        isPositive: true,
        icon: 'pi pi-box'
      },
      {
        title: 'Average Order Value',
        value: 'Rp 132rb',
        percentage: '5%',
        isPositive: true,
        icon: 'pi pi-chart-line'
      }
    ];

    // Top 10 Products Mock Data
    topProducts.value = [
      {
        rank: 1,
        productName: 'Kemeja Pria Kasual',
        shopName: 'Fashion Store A',
        qtySold: 450,
        revenue: 'Rp 4.5jt',
        profit: 'Rp 850rb'
      },
      {
        rank: 2,
        productName: 'Smartwatch Pro X',
        shopName: 'Elektronik Jaya',
        qtySold: 312,
        revenue: 'Rp 6.2jt',
        profit: 'Rp 1.2jt'
      },
      {
        rank: 3,
        productName: 'Set Gelas Keramik',
        shopName: 'Kerajinan Lokal',
        qtySold: 280,
        revenue: 'Rp 2.8jt',
        profit: 'Rp 900rb'
      },
      {
        rank: 4,
        productName: 'Serum Wajah Glowing',
        shopName: 'Beauty Care',
        qtySold: 215,
        revenue: 'Rp 3.1jt',
        profit: 'Rp 1.5jt'
      }
    ];

    isLoading.value = false;
  }, 800);
};

// ==========================================
// 📊 FUNGSI EXPORT EXCEL REAL (xlsx)
// ==========================================
const handleExportExcel = () => {
  try {
    // 1. Buat Sheet Metrik Utama
    const statsData = statCards.value.map(s => ({
      'Indikator': s.title,
      'Nilai': s.value,
      'Pertumbuhan vs Bln Lalu': s.percentage
    }));
    const statsSheet = XLSX.utils.json_to_sheet(statsData);

    // 2. Buat Sheet Top Produk
    const productsData = topProducts.value.map(p => ({
      'Peringkat': p.rank,
      'Nama Produk': p.productName,
      'Nama Toko': p.shopName,
      'Jumlah Terjual (Qty)': p.qtySold,
      'Total Revenue': p.revenue,
      'Total Profit': p.profit
    }));
    const productsSheet = XLSX.utils.json_to_sheet(productsData);

    // 3. Gabungkan Sheet ke Workbook
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, statsSheet, 'Ringkasan Performa');
    XLSX.utils.book_append_sheet(workbook, productsSheet, 'Top Produk');

    // 4. Unduh File .xlsx
    XLSX.writeFile(workbook, `Laporan_Analitik_Kabita_${new Date().toISOString().slice(0, 10)}.xlsx`);

    toast.add({
      severity: 'success',
      summary: 'Export Berhasil',
      detail: 'File Excel (.xlsx) berhasil diunduh.',
      life: 3000
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Export Gagal',
      detail: 'Terjadi kesalahan saat memproses file Excel.',
      life: 3000
    });
  }
};

// ==========================================
// 📄 FUNGSI EXPORT PDF REAL (html2pdf)
// ==========================================
const handleExportPdf = () => {
  if (!analyticsContainer.value) return;

  toast.add({
    severity: 'info',
    summary: 'Memproses PDF',
    detail: 'Menyiapkan dokumen PDF...',
    life: 2000
  });

  const opt = {
    margin: 0.3,
    filename: `Laporan_Analitik_Kabita_${new Date().toISOString().slice(0, 10)}.pdf`,
    image: { type: 'jpeg' as const, quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' as const }
  };

  html2pdf().set(opt).from(analyticsContainer.value).save().then(() => {
    toast.add({
      severity: 'success',
      summary: 'Export Berhasil',
      detail: 'File PDF berhasil diunduh.',
      life: 3000
    });
  });
};

const handleDateChange = () => {
  fetchAnalyticsData();
};

onMounted(() => {
  fetchAnalyticsData();
});
</script>

<template>
  <div ref="analyticsContainer" class="relative min-h-screen p-4 sm:p-6 space-y-6 bg-slate-50/50">
    <Transition name="fade">
      <div v-if="isLoading"
        class="fixed inset-0 z-50 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center gap-3">
        <ProgressSpinner strokeWidth="4" animationDuration=".8s" class="w-14 h-14" />
        <p class="text-sm font-semibold text-slate-600">Memuat Laporan & Analitik...</p>
      </div>
    </Transition>

    <AdminAnalyticsHeader @export-pdf="handleExportPdf" @export-excel="handleExportExcel"
      @date-change="handleDateChange" />

    <AdminAnalyticsStatCards :stats="statCards" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <AdminCategoryRevenueChart />
      <AdminTopShopsChart />
    </div>

    <AdminDailySalesChart />

    <AdminTopProductsTable :products="topProducts" />
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>