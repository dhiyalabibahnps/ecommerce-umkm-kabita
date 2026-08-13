<!DOCTYPE html>
<html>

<head>
  <title>Pembayaran Ditolak</title>
</head>

<body>
  <h2>Halo, {{ $buyerName }}!</h2>
  <p>Pembayaran untuk pesanan <strong>{{ $orderNumber }}</strong> ditolak oleh admin.</p>
  @if($rejectionReason)
  <p><strong>Alasan penolakan:</strong> {{ $rejectionReason }}</p>
  @endif
  <p>Silakan unggah ulang bukti pembayaran yang benar melalui halaman order Anda.</p>
  <p>Terima kasih,<br>Kabita Team</p>
</body>

</html>