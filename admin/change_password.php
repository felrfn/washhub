<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../db_connect.php';

$err = '';
$msg = '';
$adminId = (int)($_SESSION['admin']['id_admin'] ?? 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old = trim($_POST['old_password'] ?? '');
  $new = trim($_POST['new_password'] ?? '');
  $confirm = trim($_POST['confirm_password'] ?? '');

  if ($old === '' || $new === '' || $confirm === '') {
    $err = 'Semua field wajib diisi.';
  } elseif ($new !== $confirm) {
    $err = 'Konfirmasi password tidak cocok.';
  } else {
    try {
      $conn = db();
      // Ambil password saat ini
      $stmt = $conn->prepare('SELECT password FROM admin WHERE id_admin = ? LIMIT 1');
      $stmt->bind_param('i', $adminId);
      $stmt->execute();
      $res = $stmt->get_result();
      $row = $res->fetch_assoc();
      $current = (string)($row['password'] ?? '');

      if ($current === '') { $err = 'Data admin tidak ditemukan.'; }
      elseif ($old !== $current) { $err = 'Password lama tidak sesuai.'; }
      else {
        // Update ke password baru (plain)
        $stmt = $conn->prepare('UPDATE admin SET password = ? WHERE id_admin = ?');
        $stmt->bind_param('si', $new, $adminId);
        $stmt->execute();
        $msg = 'Password berhasil diubah.';
      }
    } catch (Throwable $e) {
      $err = $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin · Ubah Password</title>
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

  <main class="mx-auto w-[min(700px,92%)] py-8 grow">
    <h1 class="mb-4 text-2xl font-bold">Ubah Password</h1>
    <?php if ($err): ?>
      <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif ($msg): ?>
      <div class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" class="grid gap-4 rounded-xl border border-brand-dark/10 bg-white/80 p-6">
      <div class="grid gap-1">
        <label class="text-sm opacity-80">Password Lama</label>
        <input name="old_password" type="password" required class="rounded-md border border-brand-dark/20 px-3 py-2" />
      </div>
      <div class="grid gap-1">
        <label class="text-sm opacity-80">Password Baru</label>
        <input name="new_password" type="password" required class="rounded-md border border-brand-dark/20 px-3 py-2" />
      </div>
      <div class="grid gap-1">
        <label class="text-sm opacity-80">Konfirmasi Password Baru</label>
        <input name="confirm_password" type="password" required class="rounded-md border border-brand-dark/20 px-3 py-2" />
      </div>
      <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-brand-primary px-5 py-2 text-white font-bold">Simpan</button>
        <a href="./" class="rounded-md bg-brand-dark px-5 py-2 text-white font-bold">Kembali</a>
      </div>
    </form>
  </main>
</body>
</html>
