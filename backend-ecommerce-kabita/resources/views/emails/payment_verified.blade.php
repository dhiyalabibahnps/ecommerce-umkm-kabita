<!DOCTYPE html>
<html>

<head>
  <title>Pembayaran Diverifikasi</title>
</head>

<body>
  <h2>Halo, {{ $sellerName }}!</h2>
  <p>Pembayaran untuk pesanan <strong>{{ $orderNumber }}</strong> telah diverifikasi oleh admin.</p>
  <p><strong>Jumlah:</strong> Rp {{ number_format((float)$amount, 0, ',', '.') }}</p>
  <p>Silakan segera proses pesanan ini.</p>
  <p>Terima kasih,<br>Kabita Team</p>
</body>

</html>