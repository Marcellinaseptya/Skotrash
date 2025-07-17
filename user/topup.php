<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: ../login_user.php");
  exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$tanggal = date('Y-m-d');
$nama = $user['nama'];
$kelas = $user['kelas'];

// Ambil total poin user
$get_user = mysqli_query($conn, "SELECT total_poin FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($get_user);
$total_poin_user = $user_data['total_poin'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['jenis_id'])) {
  $jenis_id = (int)$_POST['jenis_id'];
  $quantity = (float)$_POST['quantity'];

  if ($quantity <= 0) {
    $error = "Jumlah sampah harus lebih dari 0.";
  } else {
    $jenis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jenis_sampah WHERE id = $jenis_id"));
    $poin_per_satuan = $jenis['poin_per_satuan'];
    $total_poin = $poin_per_satuan * $quantity;

    mysqli_query($conn, "INSERT INTO penyetoran (user_id, tanggal, status, total_poin) VALUES ($user_id, '$tanggal', 'pending', $total_poin)");
    $penyetoran_id = mysqli_insert_id($conn);

    mysqli_query($conn, "INSERT INTO detail_penyetoran (penyetoran_id, jenis_id, jumlah, subtotal_poin) VALUES ($penyetoran_id, $jenis_id, $quantity, $total_poin)");

    mysqli_query($conn, "UPDATE users SET total_poin = total_poin + $total_poin WHERE id = $user_id");

    $success = "Berhasil setor sampah <strong>{$jenis['nama']}</strong> sebanyak <strong>$quantity {$jenis['satuan']}</strong> (Total Poin: <strong>$total_poin</strong>)";
    // Refresh total poin user
    $get_user = mysqli_query($conn, "SELECT total_poin FROM users WHERE id = $user_id");
    $user_data = mysqli_fetch_assoc($get_user);
    $total_poin_user = $user_data['total_poin'];
  }
}

$hour = date('H');
if ($hour < 12) $greeting = "Selamat pagi";
elseif ($hour < 17) $greeting = "Selamat siang";
else $greeting = "Selamat malam";

$data_jenis = mysqli_query($conn, "SELECT * FROM jenis_sampah");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Setor Sampah - Skotrash</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
    }
  </style>
  <script>
    function changeQuantity(id, step) {
      const input = document.getElementById('qty-' + id);
      let val = parseFloat(input.value);
      if (isNaN(val)) val = 0;
      val += step;
      if (val < 0) val = 0;
      input.value = val;
    }
  </script>
</head>
<body class="bg-green-50 min-h-screen pb-24">

<!-- Header Section -->
<div class="relative overflow-hidden">
  <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600"></div>
  <div class="absolute inset-0 bg-black opacity-10"></div>
  
  <div class="relative px-6 pt-16 pb-12">
    <!-- Top Bar -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
          <span class="text-2xl">♻️</span>
        </div>
        <div>
          <p class="text-white/80 text-sm"><?= $greeting ?>,</p>
          <p class="text-white font-bold text-xl"><?= htmlspecialchars($nama) ?></p>
          <p class="text-white/70 text-xs"><?= htmlspecialchars($kelas) ?> • SMK Telkom</p>
        </div>
      </div>
      
      <div class="flex items-center space-x-4">
        <div class="relative">
          <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
            <span class="text-white text-lg">🔔</span>
          </div>
          <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
            <span class="text-white text-xs font-bold">3</span>
          </div>
        </div>
        <a href="profil.php" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
          <span class="text-white text-lg">⚙️</span>
        </a>
      </div>
    </div>

  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-green-700">Setor Sampah</h1>
      <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-xl shadow">
        Poinmu: <span class="text-green-600 font-semibold"><?= $total_poin_user ?></span>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-700 px-4 py-3 mb-4 rounded-lg border border-green-300 shadow">
        <?= $success ?>
      </div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 text-red-700 px-4 py-3 mb-4 rounded-lg border border-red-300 shadow">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php while ($row = mysqli_fetch_assoc($data_jenis)): ?>
        <form method="POST" class="bg-white shadow hover:shadow-lg transition p-5 rounded-2xl border border-green-100">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($row['nama']) ?></h3>
            <img src="https://cdn-icons-png.flaticon.com/512/6193/6193877.png" alt="sampah" class="w-8 h-8">
          </div>
          <p class="text-sm text-gray-500 mb-3">Poin per <?= $row['satuan'] ?>: <strong><?= $row['poin_per_satuan'] ?></strong></p>

          <div class="flex items-center mb-4">
            <button type="button" onclick="changeQuantity(<?= $row['id'] ?>, -1)" class="px-3 py-1 bg-red-100 text-red-600 rounded-l hover:bg-red-200">-</button>
            <input type="number" name="quantity" id="qty-<?= $row['id'] ?>" value="0" step="0.1" min="0" class="w-full text-center border-t border-b border-gray-300 px-3 py-1">
            <button type="button" onclick="changeQuantity(<?= $row['id'] ?>, 1)" class="px-3 py-1 bg-green-100 text-green-600 rounded-r hover:bg-green-200">+</button>
          </div>

          <input type="hidden" name="jenis_id" value="<?= $row['id'] ?>">
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-xl font-semibold transition">
            Setor Sampah
          </button>
        </form>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Navigasi Bawah -->
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-inner z-50">
    <div class="flex justify-around text-sm text-gray-500">
      <a href="index.php" class="flex flex-col items-center p-2 hover:text-green-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m0 0l2 2m-2-2l-7 7-7-7" />
        </svg>
        Beranda
      </a>
      <a href="topup.php" class="flex flex-col items-center p-2 text-green-600 font-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Setor
      </a>
      <a href="riwayat.php" class="flex flex-col items-center p-2 hover:text-green-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v10M16 7v10" />
        </svg>
        Riwayat
      </a>
      <a href="profil.php" class="flex flex-col items-center p-2 hover:text-green-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.073 12.073 0 0112 15c2.762 0 5.304.938 7.121 2.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
        </svg>
        Profil
      </a>
    </div>
  </nav>
</body>
</html>
