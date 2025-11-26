<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../db_connect.php';

$admins = [];
try {
  $conn = db();
  $res = $conn->query('SELECT id_admin, username, nama_lengkap FROM admin ORDER BY id_admin ASC');
  while ($row = $res->fetch_assoc()) { $admins[] = $row; }
} catch (Throwable $e) {
  $err = $e->getMessage();
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin · Profil — WashHub</title>
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
    <h1 class="mb-4 text-2xl font-bold">Daftar Admin</h1>
    <?php if (!empty($err)): ?>
      <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="overflow-x-auto rounded-xl border border-brand-dark/10 bg-white/80">
      <table class="min-w-full text-sm">
        <thead class="bg-brand-teal/20">
          <tr>
            <th class="text-left p-3">ID</th>
            <th class="text-left p-3">Username</th>
            <th class="text-left p-3">Nama Lengkap</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $a): ?>
          <tr class="border-t border-brand-dark/10">
            <td class="p-3 font-mono text-brand-dark/80"><?php echo (int)$a['id_admin']; ?></td>
            <td class="p-3 font-semibold"><?php echo htmlspecialchars($a['username'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($a['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$admins): ?>
          <tr><td class="p-3" colspan="3">Tidak ada data admin.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
