<script setup lang="ts">
import type { OrderStatus } from '@/types';
import { computed } from 'vue';
import { formatCourierDisplay } from '@/constants/courier';

const props = defineProps<{
  status: OrderStatus;
  shippingMethod?: string;
  paymentMethod?: string;
  isVerified?: boolean;
  hasProofImage?: boolean;
  courier?: string | null;
  trackingNumber?: string | null;
  createdAt: string;
  updatedAt: string;
}>();

interface TimelineItem {
  title: string;
  desc: string;
  time?: string;
  done: boolean;
  current: boolean;
  badge?: string;
}

const formatTimelineTime = (isoString?: string) => {
  if (!isoString) return '';
  const dateObj = new Date(isoString);
  const day = dateObj.getDate();
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const month = months[dateObj.getMonth()];
  const hours = String(dateObj.getHours()).padStart(2, '0');
  const minutes = String(dateObj.getMinutes()).padStart(2, '0');
  return `${day} ${month} ${hours}:${minutes}`;
};

const timelineItems = computed<TimelineItem[]>(() => {
  const isCod = props.shippingMethod === 'cod' || props.paymentMethod === 'cod';

  if (props.status === 'cancelled') {
    return [
      {
        title: 'Pesanan Dibuat',
        desc: 'Pesanan dibuat oleh pembeli.',
        time: formatTimelineTime(props.createdAt),
        done: true,
        current: false,
      },
      {
        title: 'Pesanan Dibatalkan',
        desc: 'Pesanan ini telah dibatalkan.',
        time: formatTimelineTime(props.updatedAt),
        done: true,
        current: true,
      },
    ];
  }

  if (isCod) {
    const isProcessing = ['processing', 'packed', 'shipped', 'cod_meeting', 'completed'].includes(props.status);
    const isPacked = ['packed', 'shipped', 'cod_meeting', 'completed'].includes(props.status);
    const isMeeting = ['cod_meeting', 'completed'].includes(props.status);
    const isCompleted = props.status === 'completed';

    return [
      {
        title: 'Pesanan Dibuat (COD)',
        desc: 'Pesanan COD berhasil dibuat.',
        time: formatTimelineTime(props.createdAt),
        done: true,
        current: props.status === 'awaiting_verification' && !isProcessing,
      },
      {
        title: 'Pesanan Dikonfirmasi',
        desc: isProcessing ? 'Pesanan dikonfirmasi dan disiapkan.' : 'Menunggu penjual memproses pesanan.',
        done: isProcessing,
        current: props.status === 'processing',
      },
      {
        title: 'Dikemas',
        desc: isPacked ? 'Barang telah dikemas rapi oleh penjual.' : 'Menunggu penjual mengemas produk.',
        done: isPacked,
        current: props.status === 'packed',
      },
      {
        title: 'Menuju Titik Temu (COD)',
        desc: isMeeting ? 'Pesanan siap diantarkan menuju lokasi pertemuan.' : 'Menunggu konfirmasi jadwal titik temu.',
        done: isMeeting,
        current: props.status === 'cod_meeting',
      },
      {
        title: 'Pesanan Selesai',
        desc: isCompleted ? 'Pembeli telah menerima barang dan pesanan selesai.' : 'Menunggu serah terima barang COD.',
        time: isCompleted ? formatTimelineTime(props.updatedAt) : undefined,
        done: isCompleted,
        current: isCompleted,
      },
    ];
  }

  // Regular / Courier Flow with Transfer
  const isVerified = props.isVerified || ['processing', 'packed', 'shipped', 'completed'].includes(props.status);
  const isProcessing = ['processing', 'packed', 'shipped', 'completed'].includes(props.status);
  const isPacked = ['packed', 'shipped', 'completed'].includes(props.status);
  const isShipped = ['shipped', 'completed'].includes(props.status);
  const isCompleted = props.status === 'completed';

  return [
    {
      title: 'Pesanan Dibuat',
      desc: 'Pesanan berhasil dibuat oleh pembeli.',
      time: formatTimelineTime(props.createdAt),
      done: true,
      current: false,
    },
    {
      title: 'Bukti Pembayaran Diupload',
      desc: props.hasProofImage
        ? 'Bukti transfer pembayaran telah diunggah oleh pembeli.'
        : 'Menunggu pembeli mengunggah bukti transfer.',
      done: Boolean(props.hasProofImage || isVerified),
      current: props.status === 'awaiting_verification' && Boolean(props.hasProofImage),
    },
    {
      title: 'Pembayaran Diverifikasi',
      desc: isVerified
        ? 'Pembayaran telah disetujui dan diverifikasi oleh admin.'
        : 'Bukti transfer sedang dalam antrean verifikasi admin.',
      done: isVerified,
      current: props.status === 'awaiting_verification' && !isVerified,
    },
    {
      title: 'Pesanan Dikonfirmasi',
      desc: isProcessing
        ? 'Pesanan dikonfirmasi dan siap untuk dikemas.'
        : 'Menunggu proses konfirmasi oleh penjual.',
      done: isProcessing,
      current: props.status === 'processing',
    },
    {
      title: 'Dikemas',
      desc: isPacked
        ? 'Penjual telah selesai mengemas produk dengan aman.'
        : 'Menunggu penjual mengemas pesanan.',
      done: isPacked,
      current: props.status === 'packed',
    },
    {
      title: 'Dikirim',
      desc: isShipped
        ? `Pesanan dikirim via ${formatCourierDisplay(props.courier)}${props.trackingNumber ? ` (Resi: ${props.trackingNumber})` : ''}.`
        : 'Menunggu penyerahan paket ke kurir & input nomor resi.',
      done: isShipped,
      current: props.status === 'shipped',
      badge: isShipped && props.trackingNumber ? props.trackingNumber : undefined,
    },
    {
      title: 'Pesanan Diterima & Selesai',
      desc: isCompleted
        ? 'Pembeli telah mengonfirmasi pesanan diterima dengan baik.'
        : 'Menunggu pembeli menerima barang & konfirmasi selesai.',
      time: isCompleted ? formatTimelineTime(props.updatedAt) : undefined,
      done: isCompleted,
      current: isCompleted,
    },
  ];
});
</script>

<template>
  <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs mb-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
      <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Aktivitas Pesanan</h3>
      <span class="text-[10px] text-slate-500 font-medium">Timeline Pelacakan</span>
    </div>

    <div class="relative pl-6 space-y-5 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
      <div v-for="(item, idx) in timelineItems" :key="idx" class="relative">
        <!-- Dot / Icon Indicator -->
        <span
          class="absolute -left-6 top-0 flex h-4 w-4 items-center justify-center rounded-full transition-all"
          :class="[
            item.done
              ? 'bg-blue-600 text-white shadow-2xs'
              : item.current
                ? 'bg-blue-600 text-white ring-4 ring-blue-100 shadow-2xs'
                : 'bg-slate-200 text-slate-400'
          ]"
        >
          <i v-if="item.done" class="pi pi-check text-[8px]"></i>
          <span v-else class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
        </span>

        <div class="flex items-start justify-between gap-2">
          <h4
            class="text-xs font-bold transition"
            :class="item.done || item.current ? 'text-slate-900' : 'text-slate-400'"
          >
            {{ item.title }}
          </h4>
          <span v-if="item.time" class="text-[10px] font-mono text-slate-400 shrink-0">
            {{ item.time }}
          </span>
        </div>

        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
          {{ item.desc }}
        </p>

        <span
          v-if="item.badge"
          class="mt-1 inline-block font-mono text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-200/70 px-2 py-0.5 rounded"
        >
          Resi: {{ item.badge }}
        </span>
      </div>
    </div>
  </div>
</template>
