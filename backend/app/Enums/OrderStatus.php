<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case AWAITING_VERIFICATION = 'awaiting_verification';
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COD_MEETING = 'cod_meeting';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AWAITING_VERIFICATION => 'Pembayaran Sedang Diverifikasi',
            self::PENDING => 'Menunggu Konfirmasi',
            self::PROCESSING => 'Dikonfirmasi',
            self::PACKED => 'Dikemas',
            self::SHIPPED => 'Dikirim',
            self::DELIVERED => 'Diterima Pembeli',
            self::COD_MEETING => 'Ketemuan',
            self::COMPLETED => 'Pesanan Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
