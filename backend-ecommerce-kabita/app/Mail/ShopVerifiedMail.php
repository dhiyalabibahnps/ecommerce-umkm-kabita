<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShopVerifiedMail extends Notification implements ShouldQueue
{
  use Queueable;

  public string $sellerName;
  public string $shopName;
  public string $adminName;

  public function __construct(string $sellerName, string $shopName, string $adminName)
  {
    $this->sellerName = $sellerName;
    $this->shopName = $shopName;
    $this->adminName = $adminName;
  }

  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Toko Anda Diverifikasi — Kabita')
      ->greeting("Halo, {$this->sellerName}!")
      ->line("Selamat! Toko <strong>{$this->shopName}</strong> telah berhasil diverifikasi oleh admin.")
      ->line('Anda sekarang dapat mulai menjual produk di platform Kabita.')
      ->action('Kelola Toko Saya', config('app.frontend_url') . '/shops/my-shop')
      ->salutation('Terima kasih,<br>Kabita Team');
  }
}
