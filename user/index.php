<?php
// user/index.php
session_start();
include '../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
  header("Location: ../login.php"); // Sesuaikan path jika perlu, mengasumsikan login.php ada di folder utama
  exit;
}

$user = $_SESSION['user'];
$user_id = $user['id']; // Ambil ID user dari sesi
$nama = $user['nama'];
$kelas = $user['kelas'];

// --- KOREKSI PENTING DI SINI ---
// Ambil saldo poin yang hanya berasal dari penyetoran dengan status 'approved'
// dan merupakan milik user yang sedang login.
// Ini akan menjumlahkan subtotal_poin dari setiap item sampah dalam transaksi approved.
$querySaldoApproved = "
    SELECT
        SUM(dp.subtotal_poin) AS saldo_approved
    FROM
        penyetoran p
    JOIN
        detail_penyetoran dp ON p.id = dp.penyetoran_id
    WHERE
        p.user_id = '$user_id' AND p.status = 'approved';
";

$resultSaldoApproved = mysqli_query($conn, $querySaldoApproved);

// Periksa apakah query berhasil dan ada hasil
if ($resultSaldoApproved && mysqli_num_rows($resultSaldoApproved) > 0) {
    $rowSaldoApproved = mysqli_fetch_assoc($resultSaldoApproved);
    // Saldo akan diatur ke 0 jika belum ada penyetoran approved
    $saldo = $rowSaldoApproved['saldo_approved'] ?? 0;
} else {
    $saldo = 0; // Atur saldo ke 0 jika tidak ada hasil atau query gagal
}


// Ambil 3 jenis sampah teratas (misalnya berdasarkan poin tertinggi per satuan)
// Pastikan kolom 'poin_per_satuan' ada di tabel jenis_sampah
$topSampahResult = mysqli_query($conn, "SELECT nama FROM jenis_sampah ORDER BY poin_per_satuan DESC LIMIT 3");
$topNamaSampah = [];
if ($topSampahResult) {
  while ($row = mysqli_fetch_assoc($topSampahResult)) {
    $topNamaSampah[] = $row['nama'];
  }
} else {
    // Anda bisa tambahkan logging error di sini jika perlu
    // echo "Error fetching top sampah: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard User - Skotrash</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen">

  <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-green-700">Skotrash</h1>
    <ul class="flex space-x-6 text-sm font-medium text-gray-700">
      <li><a href="index.php" class="hover:text-green-600">Home</a></li>
      <li><a href="aboutus.php" class="hover:text-green-600">About Us</a></li>
      <li><a href="profile.php" class="hover:text-green-600">Profile</a></li>
      <li><a href="riwayat.php" class="hover:text-green-600">Riwayat</a></li>
      <li><a href="sketsa.php" class="hover:text-green-600">Sketsa Board</a></li>
    </ul>
  </nav>

  <div class="container mx-auto p-6">
    <div class="text-2xl font-bold text-gray-800 mb-4">
      Hello 👋,
      <span class="text-green-700"><?= htmlspecialchars($nama) ?></span>
      <span class="text-sm text-gray-500">(<?= htmlspecialchars($kelas) ?>)</span>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500 mb-2">Saldo Poin</p>
        <div class="text-3xl font-bold text-green-600">
            Rp <?= number_format($saldo * 1000, 0, ',', '.') ?>
        </div>
        <div class="mt-4 flex gap-4">
          <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">tarik tunai</button>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap justify-between md:justify-start gap-4 mb-6">
      <a href="topup.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex-grow text-center">top up</a>
      <a href="riwayat.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex-grow text-center">riwayat</a>
      <a href="sketsa.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex-grow text-center">sketsa board</a>
    </div>

    <h3 class="text-xl font-bold text-gray-800 mb-4">Top 3 Jenis Sampah</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php if (!empty($topNamaSampah)): ?>
        <?php foreach ($topNamaSampah as $index => $sampah): ?>
          <div class="bg-white shadow rounded-lg p-6 text-center">
            <p class="text-lg font-semibold text-gray-700"><?= htmlspecialchars($sampah) ?></p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="col-span-full text-gray-500">Tidak ada data jenis sampah teratas.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>