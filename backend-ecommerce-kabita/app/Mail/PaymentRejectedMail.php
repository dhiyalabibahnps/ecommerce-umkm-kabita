<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejectedMail extends Notification implements ShouldQueue
{
  use Queueable;

  public string $buyerName;
  public string $orderNumber;
  public string $rejectionReason;

  public function __construct(string $buyerName, string $orderNumber, string $rejectionReason)
  {
    $this->buyerName = $buyerName;
    $this->orderNumber = $orderNumber;
    $this->rejectionReason = $rejectionReason;
  }

  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  public function toMail(object $notifiable): MailMessage
  {
    $message = (new MailMessage)
      ->subject('Pembayaran Ditolak — Kabita')
      ->greeting("Halo, {$this->buyerName}!")
      ->line("Pembayaran untuk pesanan <strong>{$this->orderNumber}</strong> ditolak oleh admin.")
      ->line('Silakan unggah ulang bukti pembayaran yang benar melalui halaman order Anda.')
      ->salutation('Terima kasih,<br>Kabita Team');

    if ($this->rejectionReason) {
      $message->line("**Alasan penolakan:** {$this->rejectionReason}");
    }

    return $message;
  }
}
