<?php
// topup.php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: ../login_user.php");
  exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$tanggal = date('Y-m-d');

// Handle submit setor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['jenis_id'])) {
  $jenis_id = (int)$_POST['jenis_id'];
  $quantity = (float)$_POST['quantity'];

  // Ambil poin per satuan dari jenis sampah
  $jenis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jenis_sampah WHERE id = $jenis_id"));
  $poin_per_satuan = $jenis['poin_per_satuan'];
  $total_poin = $poin_per_satuan * $quantity;

  // Simpan ke tabel penyetoran
  mysqli_query($conn, "INSERT INTO penyetoran (user_id, tanggal, status, total_poin) VALUES ($user_id, '$tanggal', 'pending', $total_poin)");
  $penyetoran_id = mysqli_insert_id($conn);

  // Simpan ke detail_penyetoran
  mysqli_query($conn, "INSERT INTO detail_penyetoran (penyetoran_id, jenis_id, jumlah, subtotal_poin) VALUES ($penyetoran_id, $jenis_id, $quantity, $total_poin)");

  // Update total poin user
  mysqli_query($conn, "UPDATE users SET total_poin = total_poin + $total_poin WHERE id = $user_id");

  $success = "Setor berhasil untuk jenis '{$jenis['nama']}' sebanyak $quantity (Total Poin: $total_poin)";
}

// Ambil semua jenis sampah
$data_jenis = mysqli_query($conn, "SELECT * FROM jenis_sampah");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Top Up Sampah - Skotrash</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-green-50 min-h-screen">
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-green-700 mb-4">Setor Sampah (Top Up)</h1>

    <?php if (isset($success)): ?>
      <div class="bg-green-100 text-green-700 px-4 py-3 mb-4 rounded">
        <?= $success ?>
      </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php while ($row = mysqli_fetch_assoc($data_jenis)): ?>
        <form method="POST" class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-2"><?= htmlspecialchars($row['nama']) ?></h3>
          <p class="text-sm text-gray-500 mb-4">Poin per <?= $row['satuan'] ?>: <strong><?= $row['poin_per_satuan'] ?></strong></p>

          <div class="flex items-center mb-4">
            <button type="button" onclick="changeQuantity(<?= $row['id'] ?>, -1)" class="px-3 py-1 bg-red-100 text-red-700 rounded-l">-</button>
            <input type="number" name="quantity" id="qty-<?= $row['id'] ?>" value="0" step="0.1" min="0" class="w-full text-center border-t border-b px-3 py-1">
            <button type="button" onclick="changeQuantity(<?= $row['id'] ?>, 1)" class="px-3 py-1 bg-green-100 text-green-700 rounded-r">+</button>
          </div>

          <input type="hidden" name="jenis_id" value="<?= $row['id'] ?>">
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded font-semibold">
            Setor
          </button>
        </form>
      <?php endwhile; ?>
    </div>
  </div>
  <a href="index.php">< back</a>
</body>
</html>
