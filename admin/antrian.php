<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../db_connect.php';

$err = '';
$today = date('Y-m-d');

// Update status handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $id = (int)($_POST['id_pendaftaran'] ?? 0);
  $status = $_POST['status_cucian'] ?? '';
  $allowed = ['Pending','Proses','Selesai'];
  if ($id > 0 && in_array($status, $allowed, true)) {
    try {
      $conn = db();
      $stmt = $conn->prepare('UPDATE pendaftaran SET status_cucian = ? WHERE id_pendaftaran = ?');
      $stmt->bind_param('si', $status, $id);
      $stmt->execute();
    } catch (Throwable $e) {
      $err = $e->getMessage();
    }
  }
}

// Delete handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pendaftaran'])) {
  $id = (int)($_POST['id_pendaftaran'] ?? 0);
  if ($id > 0) {
    try {
      $conn = db();
      $stmt = $conn->prepare('DELETE FROM pendaftaran WHERE id_pendaftaran = ?');
      $stmt->bind_param('i', $id);
      $stmt->execute();
    } catch (Throwable $e) {
      $err = $e->getMessage();
    }
  }
}

// Fetch today's queue (semua status termasuk Selesai)
$queue = [];
// Backlog (hari sebelumnya yang belum selesai)
$backlog = [];
try {
  $conn = db();
  // Query hari ini
  $stmt = $conn->prepare('SELECT p.id_pendaftaran, p.no_antrian, p.nomor_plat, p.tgl_booking, p.jam_booking, p.status_cucian,
                                 c.nama_customer, c.no_hp,
                                 j.nama_jenis, k.nama_paket
                          FROM pendaftaran p
                          JOIN customer c ON c.id_customer = p.id_customer
                          JOIN jenis_mobil j ON j.id_jenis = p.id_jenis_mobil
                          JOIN paket_cuci k ON k.id_paket = p.id_paket
                          WHERE p.tgl_booking = ?
                          ORDER BY p.jam_booking ASC, p.no_antrian ASC');
  $stmt->bind_param('s', $today);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) { $queue[] = $row; }

  // Query backlog (hari sebelumnya belum selesai)
  $stmt = $conn->prepare('SELECT p.id_pendaftaran, p.no_antrian, p.nomor_plat, p.tgl_booking, p.jam_booking, p.status_cucian,
                                  c.nama_customer, c.no_hp,
                                  j.nama_jenis, k.nama_paket
                           FROM pendaftaran p
                           JOIN customer c ON c.id_customer = p.id_customer
                           JOIN jenis_mobil j ON j.id_jenis = p.id_jenis_mobil
                           JOIN paket_cuci k ON k.id_paket = p.id_paket
                           WHERE p.tgl_booking < ? AND p.status_cucian <> "Selesai"
                           ORDER BY p.tgl_booking DESC, p.no_antrian ASC');
  $stmt->bind_param('s', $today);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) { $backlog[] = $row; }
} catch (Throwable $e) { $err = $e->getMessage(); }

// Determine current and next based on status priority and exclude 'Selesai'
$current = null; $next = null;
$processingIndex = null; $firstPendingIndex = null;
foreach ($queue as $idx => $row) {
  if ($row['status_cucian'] === 'Proses' && $processingIndex === null) {
    $processingIndex = $idx;
  }
  if ($row['status_cucian'] === 'Pending' && $firstPendingIndex === null) {
    $firstPendingIndex = $idx;
  }
}

if ($processingIndex !== null) {
  $current = $queue[$processingIndex];
  for ($i = $processingIndex + 1; $i < count($queue); $i++) {
    if ($queue[$i]['status_cucian'] === 'Pending') { $next = $queue[$i]; break; }
  }
} elseif ($firstPendingIndex !== null) {
  $current = $queue[$firstPendingIndex];
  for ($i = $firstPendingIndex + 1; $i < count($queue); $i++) {
    if ($queue[$i]['status_cucian'] === 'Pending') { $next = $queue[$i]; break; }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin · Antrian — WashHub</title>
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
    <h1 class="mb-4 text-2xl font-bold">Antrian Hari Ini (<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>)</h1>
    <?php if ($err): ?>
      <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="grid gap-4 md:grid-cols-2">
      <div class="rounded-xl border border-brand-dark/10 bg-white/80 p-4">
        <div class="text-sm opacity-70">Antrian Saat Ini</div>
        <?php if ($current): ?>
          <div class="mt-1 text-3xl font-extrabold tracking-widest text-brand-primary"><?php echo htmlspecialchars($current['no_antrian'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="mt-2 text-sm">Plat <?php echo htmlspecialchars($current['nomor_plat'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($current['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="text-sm opacity-80"><?php echo htmlspecialchars($current['nama_jenis'].' · '.$current['nama_paket'], ENT_QUOTES, 'UTF-8'); ?></div>
          <form method="post" class="mt-3 flex items-center gap-2">
            <input type="hidden" name="id_pendaftaran" value="<?php echo (int)$current['id_pendaftaran']; ?>" />
            <select name="status_cucian" class="rounded-md border border-brand-dark/20 px-2 py-1 text-sm">
              <?php foreach (['Pending','Proses','Selesai'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $current['status_cucian']===$st?'selected':''; ?>><?php echo $st; ?></option>
              <?php endforeach; ?>
            </select>
            <button name="update_status" value="1" class="rounded-md bg-brand-primary px-3 py-1.5 text-white text-sm font-bold">Update</button>
          </form>
        <?php else: ?>
          <div class="mt-2">Tidak ada antrian.</div>
        <?php endif; ?>
      </div>

      <div class="rounded-xl border border-brand-dark/10 bg-white/80 p-4">
        <div class="text-sm opacity-70">Antrian Selanjutnya</div>
        <?php if ($next): ?>
          <div class="mt-1 text-2xl font-extrabold tracking-widest"><?php echo htmlspecialchars($next['no_antrian'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="mt-2 text-sm">Plat <?php echo htmlspecialchars($next['nomor_plat'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($next['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="text-sm opacity-80"><?php echo htmlspecialchars($next['nama_jenis'].' · '.$next['nama_paket'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php else: ?>
          <div class="mt-2">-</div>
        <?php endif; ?>
      </div>
    </div>

    <h2 class="mt-8 mb-2 text-xl font-semibold">Daftar Antrian</h2>
    <div class="overflow-x-auto rounded-xl border border-brand-dark/10 bg-white/80">
      <table class="min-w-full text-sm">
        <thead class="bg-brand-teal/20">
          <tr>
            <th class="text-left p-3">No</th>
            <th class="text-left p-3">Plat</th>
            <th class="text-left p-3">Customer</th>
            <th class="text-left p-3">Paket</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($queue as $q): $rowId = 'row_'.$q['id_pendaftaran']; ?>
          <tr class="border-t border-brand-dark/10 hover:bg-brand-teal/10 cursor-pointer" onclick="toggleRow('<?php echo $rowId; ?>')">
            <td class="p-3 font-mono"><?php echo htmlspecialchars($q['no_antrian'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nomor_plat'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nama_paket'], ENT_QUOTES, 'UTF-8'); ?></td>
          </tr>
          <tr id="<?php echo $rowId; ?>" class="hidden border-t border-brand-dark/10 bg-white/70">
            <td colspan="4" class="p-3">
              <div class="grid gap-2 md:grid-cols-2">
                <div class="text-sm">Tanggal: <span class="font-mono"><?php echo htmlspecialchars($q['tgl_booking'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="text-sm">Jam: <span class="font-mono"><?php echo htmlspecialchars($q['jam_booking'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="text-sm">Status: <span class="font-semibold"><?php echo htmlspecialchars($q['status_cucian'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="flex items-center gap-2 mt-2">
                  <form method="post" class="flex items-center gap-2">
                    <input type="hidden" name="id_pendaftaran" value="<?php echo (int)$q['id_pendaftaran']; ?>" />
                    <select name="status_cucian" class="rounded-md border border-brand-dark/20 px-2 py-1 text-sm">
                      <?php foreach (['Pending','Proses','Selesai'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $q['status_cucian']===$st?'selected':''; ?>><?php echo $st; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button name="update_status" value="1" class="rounded-md bg-brand-primary px-3 py-1.5 text-white text-sm font-bold">Update</button>
                  </form>
                  <form method="post" onsubmit="return confirm('Hapus antrian ini?');">
                    <input type="hidden" name="id_pendaftaran" value="<?php echo (int)$q['id_pendaftaran']; ?>" />
                    <button name="delete_pendaftaran" value="1" class="rounded-md bg-red-500 px-3 py-1.5 text-white text-sm font-bold">Hapus</button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if ($backlog): ?>
          <tr class="bg-brand-dark/5">
            <td colspan="4" class="p-3 font-semibold">Backlog</td>
          </tr>
          <?php $lastDate = null; foreach ($backlog as $q): $rowId = 'backlog_'.$q['id_pendaftaran']; ?>
            <?php if ($lastDate !== $q['tgl_booking']): $lastDate = $q['tgl_booking']; ?>
              <tr class="bg-brand-teal/15">
                <td colspan="4" class="p-2 font-semibold">Tanggal: <?php echo htmlspecialchars($q['tgl_booking'], ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
            <?php endif; ?>
            <tr class="border-t border-brand-dark/10 hover:bg-brand-teal/10 cursor-pointer" onclick="toggleRow('<?php echo $rowId; ?>')">
              <td class="p-3 font-mono"><?php echo htmlspecialchars($q['no_antrian'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="p-3"><?php echo htmlspecialchars($q['nomor_plat'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="p-3"><?php echo htmlspecialchars($q['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="p-3"><?php echo htmlspecialchars($q['nama_paket'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr id="<?php echo $rowId; ?>" class="hidden border-t border-brand-dark/10 bg-white/70">
              <td colspan="4" class="p-3">
                <div class="grid gap-2 md:grid-cols-2">
                  <div class="text-sm">Tanggal: <span class="font-mono"><?php echo htmlspecialchars($q['tgl_booking'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                  <div class="text-sm">Jam: <span class="font-mono"><?php echo htmlspecialchars($q['jam_booking'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                  <div class="text-sm">Status: <span class="font-semibold"><?php echo htmlspecialchars($q['status_cucian'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                  <div class="flex items-center gap-2 mt-2">
                    <form method="post" class="flex items-center gap-2">
                      <input type="hidden" name="id_pendaftaran" value="<?php echo (int)$q['id_pendaftaran']; ?>" />
                      <select name="status_cucian" class="rounded-md border border-brand-dark/20 px-2 py-1 text-sm">
                        <?php foreach (['Pending','Proses','Selesai'] as $st): ?>
                          <option value="<?php echo $st; ?>" <?php echo $q['status_cucian']===$st?'selected':''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button name="update_status" value="1" class="rounded-md bg-brand-primary px-3 py-1.5 text-white text-sm font-bold">Update</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Hapus antrian ini?');">
                      <input type="hidden" name="id_pendaftaran" value="<?php echo (int)$q['id_pendaftaran']; ?>" />
                      <button name="delete_pendaftaran" value="1" class="rounded-md bg-red-500 px-3 py-1.5 text-white text-sm font-bold">Hapus</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          <?php if (!$queue): ?>
          <tr><td class="p-3" colspan="4">Belum ada antrian hari ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
  <script>
    function toggleRow(id){
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.toggle('hidden');
    }
  </script>
</body>
</html>
