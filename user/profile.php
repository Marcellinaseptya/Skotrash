<?php
// profile.php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: ../login_user.php");
  exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$nama = $user['nama'];
$kelas = $user['kelas'];
$nis = $user['nis'];
$total_poin = $user['total_poin'];

// Ambil total poin dari detail penyetoran (subtotal_poin)
$result_poin = mysqli_query($conn, "
  SELECT SUM(dp.subtotal_poin) as skor 
  FROM detail_penyetoran dp
  JOIN penyetoran p ON dp.penyetoran_id = p.id
  WHERE p.user_id = $user_id
");
$skor = mysqli_fetch_assoc($result_poin)['skor'] ?? 0;

// Ambil jumlah total sampah disetor (jumlah)
$result_jumlah = mysqli_query($conn, "
  SELECT SUM(dp.jumlah) as total_jumlah
  FROM detail_penyetoran dp
  JOIN penyetoran p ON dp.penyetoran_id = p.id
  WHERE p.user_id = $user_id
");
$jumlah_setoran = mysqli_fetch_assoc($result_jumlah)['total_jumlah'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya - Skotrash</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen">
  <!-- Navbar -->
  <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-green-700 font-bold text-xl">Skotrash</div>
    <ul class="flex space-x-6 text-sm font-semibold text-gray-600">
      <li><a href="index.php" class="hover:text-green-600">Home</a></li>
      <li><a href="aboutus.php" class="hover:text-green-600">About Us</a></li>
      <li><a href="profile.php" class="hover:text-green-600">Profile</a></li>
    </ul>
  </nav>

  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6 text-center">
      <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center text-green-700 text-4xl mb-4">
        👤
      </div>
    </div>

    <!-- Skor dan Jumlah Setoran -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex justify-between">
        <div class="text-center">
          <p class="text-2xl font-bold text-green-700">Skor/ranking: <?= $skor ?></p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-bold text-green-700">Jumlah setoran: <?= $jumlah_setoran ?> kg</p>
        </div>
      </div>
    </div>

    <!-- Informasi User -->
    <div class="bg-white shadow rounded-lg p-6">
      <ul class="space-y-2 text-gray-700">
        <li><strong>Username:</strong> <?= htmlspecialchars($nama) ?></li>
        <li><strong>Kelas:</strong> <?= htmlspecialchars($kelas) ?></li>
        <li><strong>NIS:</strong> <?= htmlspecialchars($nis) ?></li>
        <li><strong>Total Poin:</strong> <?= $total_poin ?></li>
      </ul>
    </div>
  </div>
</body>
</html>
