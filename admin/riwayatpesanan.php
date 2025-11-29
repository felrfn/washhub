<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../db_connect.php';

$err = '';

// Filters: mode can be range|month|year
$mode = $_GET['mode'] ?? 'range';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$month = $_GET['month'] ?? '';
$year = $_GET['year'] ?? '';

$list = [];
// Group label for rendering headers when not range
$grouping = $mode;
try {
  $conn = db();
  // Build query dynamically based on filters
  $sql = "SELECT p.id_pendaftaran, p.nomor_plat, p.tgl_booking, p.jam_booking, c.nama_customer
          FROM pendaftaran p
          JOIN customer c ON c.id_customer = p.id_customer
          WHERE p.status_cucian = 'Selesai'";
  $params = [];
  $types = '';

  if ($mode === 'range') {
    if ($start !== '' && $end !== '') {
      $sql .= " AND p.tgl_booking BETWEEN ? AND ?";
      $params[] = $start; $params[] = $end; $types .= 'ss';
    } elseif ($start !== '') {
      $sql .= " AND p.tgl_booking >= ?";
      $params[] = $start; $types .= 's';
    } elseif ($end !== '') {
      $sql .= " AND p.tgl_booking <= ?";
      $params[] = $end; $types .= 's';
    }
    $sql .= " ORDER BY p.tgl_booking DESC, p.jam_booking DESC";
  } elseif ($mode === 'month') {
    if ($month === '') { $month = date('m'); }
    if ($year === '') { $year = date('Y'); }
    $sql .= " AND YEAR(p.tgl_booking) = ? AND MONTH(p.tgl_booking) = ? ORDER BY p.tgl_booking DESC, p.jam_booking DESC";
    $params[] = (int)$year; $params[] = (int)$month; $types .= 'ii';
  } elseif ($mode === 'year') {
    if ($year === '') { $year = date('Y'); }
    $sql .= " AND YEAR(p.tgl_booking) = ? ORDER BY p.tgl_booking DESC, p.jam_booking DESC";
    $params[] = (int)$year; $types .= 'i';
  } else {
    $sql .= " ORDER BY p.tgl_booking DESC, p.jam_booking DESC";
  }

  if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
  } else {
    $res = $conn->query($sql);
  }

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
    <form method="get" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-brand-dark/10 bg-white/80 p-4 text-sm" id="filterForm">
      <div>
        <label class="block mb-1">Mode</label>
        <select name="mode" class="rounded-md border border-brand-dark/20 px-2 py-1" id="modeSelect">
          <option value="range" <?php echo $mode==='range'?'selected':''; ?>>Range Tanggal</option>
          <option value="month" <?php echo $mode==='month'?'selected':''; ?>>Per Bulan</option>
          <option value="year" <?php echo $mode==='year'?'selected':''; ?>>Per Tahun</option>
        </select>
      </div>
      <div id="rangeFields" <?php echo $mode==='range'?'':'style="display:none"'; ?>>
        <label class="block mb-1">Mulai</label>
        <input type="date" name="start" value="<?php echo htmlspecialchars($start, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-md border border-brand-dark/20 px-2 py-1" />
      </div>
      <div id="rangeFields2" <?php echo $mode==='range'?'':'style="display:none"'; ?>>
        <label class="block mb-1">Selesai</label>
        <input type="date" name="end" value="<?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-md border border-brand-dark/20 px-2 py-1" />
      </div>
      <div id="monthFields" <?php echo $mode==='month'?'':'style="display:none"'; ?>>
        <label class="block mb-1">Bulan</label>
        <input type="number" min="1" max="12" name="month" value="<?php echo htmlspecialchars($month ?: date('m'), ENT_QUOTES, 'UTF-8'); ?>" class="w-20 rounded-md border border-brand-dark/20 px-2 py-1" />
      </div>
      <div id="yearFields" <?php echo ($mode==='month'||$mode==='year')?'':'style="display:none"'; ?>>
        <label class="block mb-1">Tahun</label>
        <input type="number" name="year" value="<?php echo htmlspecialchars($year ?: date('Y'), ENT_QUOTES, 'UTF-8'); ?>" class="w-24 rounded-md border border-brand-dark/20 px-2 py-1" />
      </div>
      <div>
        <button type="submit" class="rounded-md bg-brand-primary px-3 py-1.5 text-white font-bold">Terapkan</button>
      </div>
    </form>
    <div class="overflow-x-auto rounded-xl border border-brand-dark/10 bg-white/80">
      <table class="min-w-full text-sm">
        <thead class="bg-brand-teal/20">
          <tr>
            <th class="text-left p-3">Tanggal</th>
            <th class="text-left p-3">Plat</th>
            <th class="text-left p-3">Customer</th>
            <th class="text-left p-3">Jam</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $currentGroupKey = null;
            foreach ($list as $q):
              // Determine group key based on mode
              if ($mode === 'month') {
                $groupKey = date('Y-m', strtotime($q['tgl_booking']));
                $groupLabel = date('F Y', strtotime($q['tgl_booking']));
              } elseif ($mode === 'year') {
                $groupKey = date('Y', strtotime($q['tgl_booking']));
                $groupLabel = $groupKey;
              } else {
                $groupKey = $q['tgl_booking'];
                $groupLabel = $groupKey;
              }
              if ($currentGroupKey !== $groupKey) {
                $currentGroupKey = $groupKey;
          ?>
              <tr class="bg-brand-teal/15">
                <td colspan="4" class="p-2 font-semibold"><?php echo htmlspecialchars($groupLabel, ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
          <?php }
          ?>
          <tr class="border-top border-brand-dark/10">
            <td class="p-3"><?php echo htmlspecialchars($q['tgl_booking'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nomor_plat'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($q['nama_customer'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="p-3"><?php echo htmlspecialchars(substr($q['jam_booking'],0,5), ENT_QUOTES, 'UTF-8'); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$list): ?>
          <tr><td class="p-3" colspan="4">Belum ada riwayat selesai.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
  <script>
    (function(){
      const modeEl = document.getElementById('modeSelect');
      const range1 = document.getElementById('rangeFields');
      const range2 = document.getElementById('rangeFields2');
      const monthEl = document.getElementById('monthFields');
      const yearEl = document.getElementById('yearFields');
      function updateVisibility(){
        const v = modeEl.value;
        const showRange = v === 'range';
        const showMonth = v === 'month';
        const showYear = v === 'year' || v === 'month';
        range1.style.display = showRange ? '' : 'none';
        range2.style.display = showRange ? '' : 'none';
        monthEl.style.display = showMonth ? '' : 'none';
        yearEl.style.display = showYear ? '' : 'none';
      }
      modeEl.addEventListener('change', updateVisibility);
    })();
  </script>
</body>
</html>
