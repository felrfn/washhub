<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../db_connect.php';

$err = '';
$msg = '';

// Ambil admin id dari session bila ada, default 1
$adminId = (int)($_SESSION['admin']['id_admin'] ?? 1);

// Handle update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $username = trim($_POST['username'] ?? '');
  $nama = trim($_POST['nama_lengkap'] ?? '');
  $nomor_hp = trim($_POST['nomor_hp'] ?? '');
  $alamat = trim($_POST['alamat'] ?? '');
  $newPass = trim($_POST['new_password'] ?? '');
  $confirmPass = trim($_POST['confirm_password'] ?? '');

  if ($username === '' || $nama === '') {
    $err = 'Username dan Nama tidak boleh kosong.';
  } elseif ($newPass !== '' && $newPass !== $confirmPass) {
    $err = 'Konfirmasi password tidak cocok.';
  } else {
    try {
      $conn = db();
      // Update dasar
      $stmt = $conn->prepare('UPDATE admin SET username = ?, nama_lengkap = ?, nomor_hp = ?, alamat = ? WHERE id_admin = ?');
      $stmt->bind_param('ssssi', $username, $nama, $nomor_hp, $alamat, $adminId);
      $stmt->execute();

      // Update password jika diisi
      if ($newPass !== '') {
        $stmt = $conn->prepare('UPDATE admin SET password = ? WHERE id_admin = ?');
        $stmt->bind_param('si', $newPass, $adminId);
        $stmt->execute();
      }

      $msg = 'Profil berhasil diperbarui.';
      // Sinkronkan ke session
      $_SESSION['admin']['username'] = $username;
      $_SESSION['admin']['nama_lengkap'] = $nama;
    } catch (Throwable $e) {
      $err = $e->getMessage();
    }
  }
}

// Ambil profil admin
$profile = null;
try {
  $conn = db();
  $stmt = $conn->prepare('SELECT id_admin, username, nama_lengkap, nomor_hp, alamat FROM admin WHERE id_admin = ? LIMIT 1');
  $stmt->bind_param('i', $adminId);
  $stmt->execute();
  $res = $stmt->get_result();
  $profile = $res->fetch_assoc();
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

  <main class="mx-auto w-[min(900px,92%)] py-8 grow">
    <h1 class="mb-4 text-2xl font-bold">Profil Admin</h1>
    <?php if ($err): ?>
      <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif ($msg): ?>
      <div class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" class="grid gap-4 rounded-xl border border-brand-dark/10 bg-white/80 p-6">
      <input type="hidden" name="update_profile" value="1" />
      <div class="grid md:grid-cols-2 gap-4">
        <div class="grid gap-1">
          <label class="text-sm opacity-80">Username</label>
          <input name="username" type="text" required class="rounded-md border border-brand-dark/20 px-3 py-2" value="<?php echo htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm opacity-80">Nama Lengkap</label>
          <input name="nama_lengkap" type="text" required class="rounded-md border border-brand-dark/20 px-3 py-2" value="<?php echo htmlspecialchars($profile['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm opacity-80">Nomor HP</label>
          <input name="nomor_hp" type="text" class="rounded-md border border-brand-dark/20 px-3 py-2" value="<?php echo htmlspecialchars($profile['nomor_hp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm opacity-80">Alamat</label>
          <textarea name="alamat" rows="2" class="rounded-md border border-brand-dark/20 px-3 py-2"><?php echo htmlspecialchars($profile['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="rounded-md bg-brand-primary px-5 py-2 text-white font-bold">Simpan Perubahan</button>
        <a href="change_password.php" class="rounded-md bg-brand-dark px-5 py-2 text-white font-bold">Password</a>
      </div>
    </form>
  </main>
</body>
</html>
