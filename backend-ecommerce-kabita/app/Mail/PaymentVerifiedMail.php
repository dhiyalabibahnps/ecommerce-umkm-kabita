<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentVerifiedMail extends Notification implements ShouldQueue
{
  use Queueable;

  public string $buyerName;
  public string $sellerName;
  public string $orderNumber;
  public string $amount;

  public function __construct(string $buyerName, string $sellerName, string $orderNumber, string $amount)
  {
    $this->buyerName = $buyerName;
    $this->sellerName = $sellerName;
    $this->orderNumber = $orderNumber;
    $this->amount = $amount;
  }

  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Pembayaran Diverifikasi — Kabita')
      ->greeting("Halo, {$this->sellerName}!")
      ->line("Pembayaran untuk pesanan <strong>{$this->orderNumber}</strong> telah diverifikasi oleh admin.")
      ->line("**Jumlah:** Rp " . number_format((float) $this->amount, 0, ',', '.'))
      ->line('Silakan segera proses pesanan ini.')
      ->salutation('Terima kasih,<br>Kabita Team');
  }
}
