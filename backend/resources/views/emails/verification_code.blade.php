<!DOCTYPE html>
<html>

<head>
  <title>Verifikasi Email Kabita</title>
</head>

<body>
  <h2>Halo, {{ $userName }}!</h2>
  <p>Terima kasih telah mendaftar di Kabita. Gunakan kode berikut untuk memverifikasi email Anda:</p>
  <h1 style="color: #2563EB; font-size: 32px; letter-spacing: 5px;">{{ $code }}</h1>
  <p>Kode ini akan kedaluwarsa dalam 10 menit.</p>
  <p>Jika Anda tidak mendaftar, abaikan email ini.</p>
</body>

</html>