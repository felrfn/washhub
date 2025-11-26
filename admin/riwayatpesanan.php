<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../db_connect.php';

$err = '';
$list = [];
try {
  $conn = db();
  $res = $conn->query("SELECT p.id_pendaftaran, p.no_antrian, p.nomor_plat, p.tgl_booking, p.jam_booking,
                              c.nama_customer, j.nama_jenis, k.nama_paket
                       FROM pendaftaran p
                       JOIN customer c ON c.id_customer = p.id_customer
                       JOIN jenis_mobil j ON j.id_jenis = p.id_jenis_mobil
                       JOIN paket_cuci k ON k.id_paket = p.id_paket
                       WHERE p.status_cucian = 'Selesai'
                       ORDER BY p.tgl_booking DESC, p.no_antrian DESC
                       LIMIT 200");
  while ($row = $res->fetch_assoc()) { $list[] = $row; }
} catch (Throwable $e) { $err = $e->getMessage(); }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin · Riwayat — WashHub</title>
  <?php include __DIR__ . '/../components/head-resources.php'; ?>
</head>
<body class="min-h-dvh flex flex-col font-display bg-gradient-to-b from-white via-brand-sky to-brand-teal/70 text-brand-dark">
  <header class="sticky top-0 z-50 bg-white/70 backdrop-blur border-b border-brand-dark/10">
    <div class="mx-auto w-[min(1100px,92%)] flex items-center justify-between py-3.5">
      <a class="text-xl font-bold tracking-wide" href="../index.php">WashHub</a>
      <nav class="flex items-center gap-4 text-[0.95rem]">
        <a class="hover:underline" href="./">Profil</a>
        <a class="hover:underline" href="./antrian.php">Antrian</a>
        <a class="hover:underline" href="./riwayatpesanan.php">Riwayat</a>
        <a class="hover:underline" href="../logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="mx-auto w-[min(1100px,92%)] py-8 grow">
    <h1 class="mb-4 text-2xl font-bold">Riwayat Pesanan Selesai</h1>
    <?php if ($err): ?>
      <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="overflow-x-auto rounded-xl border border-brand-dark/10 bg-white/80">
      <table class="min-w-full text-sm">
        <thead class="bg-brand-teal/20">
          <tr>
            <th class="text-left p-3">Tanggal</th>
            <th class="text-left p-3">No</th>
            <th class="text-left p-3">Plat</th>
            <th class="text-left p-3">Customer</th>
            <th class="text-left p-3">Jenis</th>
            <th class="text-left p-3">Paket</th>
            <th class="text-left p-3">Jam</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $q): ?>
          <tr class="border-top border-brand-dark/10">
            <td class="p-3"><?php echo htmlspecialchars($q['tgl_booking'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3 font-mono"><?php echo htmlspecialchars($q['no_antrian'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nomor_plat'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nama_jenis'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nama_paket'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['jam_booking'], ENT_QUOTES, 'UTF-8'); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$list): ?>
          <tr><td class="p-3" colspan="7">Belum ada riwayat selesai.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
