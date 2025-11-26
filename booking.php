<?php
require_once __DIR__ . '/db_connect.php';

// Helper queue number: A001 per tanggal
function generate_queue_no(mysqli $conn, string $date): string {
  $stmt = $conn->prepare("SELECT no_antrian FROM pendaftaran WHERE tgl_booking = ? ORDER BY id_pendaftaran DESC LIMIT 1");
  $stmt->bind_param('s', $date);
  $stmt->execute();
  $res = $stmt->get_result();
  $last = $res->fetch_assoc()['no_antrian'] ?? null;
  if ($last && preg_match('/^([A-Z])(\d{3})$/', $last, $m)) {
    $prefix = $m[1];
    $num = intval($m[2]) + 1;
    return $prefix . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
  }
  return 'A001';
}

$vehicleTypes = [];
$washPackages = [];

try {
  $conn = db();
  if ($res = $conn->query('SELECT id_jenis AS id, nama_jenis AS name FROM jenis_mobil ORDER BY id_jenis')) {
    while ($row = $res->fetch_assoc()) { $vehicleTypes[] = $row; }
  }
  if ($res = $conn->query('SELECT id_paket AS id, nama_paket AS name, harga FROM paket_cuci ORDER BY id_paket')) {
    while ($row = $res->fetch_assoc()) { $washPackages[] = $row; }
  }
} catch (Throwable $e) {
}

// Build map harga paket
$paketHarga = [];
foreach ($washPackages as $wp) { $paketHarga[(int)$wp['id']] = (int)($wp['harga'] ?? 0); }

// Determine step
$step = $_POST['step'] ?? 'form';
$state = [
  'name' => trim($_POST['name'] ?? ''),
  'phone' => trim($_POST['phone'] ?? ''),
  'vehicle_type_id' => (int)($_POST['vehicle_type_id'] ?? 0),
  'license_plate' => trim($_POST['license_plate'] ?? ''),
  'wash_package_id' => (int)($_POST['wash_package_id'] ?? 0),
  'date' => $_POST['date'] ?? '',
  'time' => $_POST['time'] ?? '',
];

// Normalize plate to uppercase without double spaces
if ($state['license_plate']) { $state['license_plate'] = strtoupper(preg_replace('/\s+/', ' ', $state['license_plate'])); }

// Validation quick
function valid_state(array $s): bool {
  return $s['name'] !== '' && $s['phone'] !== '' && $s['vehicle_type_id'] > 0 && $s['license_plate'] !== '' && $s['wash_package_id'] > 0 && $s['date'] !== '' && $s['time'] !== '';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking — WashHub</title>
  <?php include __DIR__ . '/components/head-resources.php'; ?>
</head>
<body class="min-h-dvh flex flex-col font-display bg-gradient-to-b from-white via-brand-sky to-brand-teal/70 text-brand-dark">
  <?php include __DIR__ . '/components/header.php'; ?>

  <main class="flex-grow w-full max-w-[500px] md:max-w-4xl mx-auto px-6 py-6 pb-20">
    <div class="flex items-center gap-4 mb-6">
      <img src="public/logo.png" alt="WashHub" class="w-16 md:w-20 object-contain" />
      <h1 class="text-2xl md:text-3xl font-bold leading-tight">
        <?php echo $step === 'preview' ? 'Metode Pembayaran' : ($step === 'success' ? 'Booking Berhasil' : 'Form Booking Cuci Mobil'); ?>
      </h1>
    </div>

    <?php if ($step === 'preview' && valid_state($state)): ?>
      <?php $harga = $paketHarga[$state['wash_package_id']] ?? 0; ?>
      <div class="grid gap-4 bg-white/70 backdrop-blur-sm rounded-xl p-5 border border-brand-dark/10">
        <div>
          <div class="font-semibold">Ringkasan</div>
          <div class="text-sm opacity-80">
            <?php echo htmlspecialchars($state['name'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($state['phone'], ENT_QUOTES, 'UTF-8'); ?> · Plat <?php echo htmlspecialchars($state['license_plate'], ENT_QUOTES, 'UTF-8'); ?><br/>
            Tanggal: <?php echo htmlspecialchars($state['date'], ENT_QUOTES, 'UTF-8'); ?> · Jam: <?php echo htmlspecialchars($state['time'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        </div>
        <div class="text-lg font-bold">Total: Rp<?php echo number_format($harga, 0, ',', '.'); ?></div>
        <div>
          <div class="font-semibold mb-2">Pilih Metode Pembayaran</div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <?php $methods = ['QRIS','BCA','Mandiri','BNI','BRI','BTN','BSI']; foreach ($methods as $m): ?>
            <label class="flex items-center gap-2 rounded-md border border-brand-dark/20 bg-white px-3 py-2">
              <input type="radio" name="method" value="<?php echo $m; ?>" required />
              <span class="text-sm font-semibold"><?php echo $m; ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <?php $acc = (string)random_int(1000000000, 9999999999999); ?>
        <div class="rounded-lg border border-brand-dark/20 bg-white p-4">
          <div class="text-sm opacity-80 mb-1">Nomor Tujuan</div>
          <div class="text-xl font-mono tracking-wider select-all"><?php echo chunk_split($acc, 4, ' '); ?></div>
          <div class="text-xs opacity-70 mt-1">Gunakan nomor di atas sesuai metode yang dipilih.</div>
        </div>

        <form method="post" class="mt-2">
          <?php foreach ($state as $k=>$v): ?>
            <input type="hidden" name="<?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>" />
          <?php endforeach; ?>
          <input type="hidden" name="step" value="pay" />
          <input type="hidden" name="account_number" value="<?php echo htmlspecialchars($acc, ENT_QUOTES, 'UTF-8'); ?>" />
          <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-brand-primary px-5 py-2 font-bold text-white">Bayar</button>
            <a href="booking.php" class="text-sm underline">Kembali</a>
          </div>
        </form>
      </div>
    <?php elseif ($step === 'pay' && valid_state($state)): ?>
      <?php
        try {
          $conn = db();
          // 1. Customer by phone
          $stmt = $conn->prepare('SELECT id_customer FROM customer WHERE no_hp = ? LIMIT 1');
          $stmt->bind_param('s', $state['phone']);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($row = $res->fetch_assoc()) { $customerId = (int)$row['id_customer']; }
          else {
            $stmt = $conn->prepare('INSERT INTO customer (nama_customer, no_hp, alamat) VALUES (?,?,NULL)');
            $stmt->bind_param('ss', $state['name'], $state['phone']);
            $stmt->execute();
            $customerId = $conn->insert_id;
          }

          // 2. Queue number for date
          $queueNo = generate_queue_no($conn, $state['date']);

          // 3. Insert pendaftaran
          $stmt = $conn->prepare('INSERT INTO pendaftaran (no_antrian, id_customer, id_jenis_mobil, id_paket, nomor_plat, tgl_booking, jam_booking, status_cucian) VALUES (?,?,?,?,?,?,?,\'Pending\')');
          $stmt->bind_param('siissss', $queueNo, $customerId, $state['vehicle_type_id'], $state['wash_package_id'], $state['license_plate'], $state['date'], $state['time']);
          $stmt->execute();
          $pendaftaranId = $conn->insert_id;

          // Harga from paket
          $harga = $paketHarga[$state['wash_package_id']] ?? 0;

          // Show success UI
        } catch (Throwable $e) {
          echo '<div class="rounded-xl border border-red-300 bg-red-50 p-4 text-red-700">Gagal memproses booking: '.htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8').'</div>';
        }
      ?>
      <div class="grid gap-4 bg-white/70 backdrop-blur-sm rounded-xl p-5 border border-brand-dark/10 text-center">
        <div class="text-2xl font-bold">Pembayaran Berhasil</div>
        <div>No. Antrian Anda</div>
        <div class="text-4xl font-extrabold tracking-widest text-brand-primary"><?php echo htmlspecialchars($queueNo ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="opacity-80">Jadwal Cuci: <?php echo htmlspecialchars(($state['date'].' '.$state['time']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="text-lg font-semibold">Total: Rp<?php echo number_format($harga ?? 0, 0, ',', '.'); ?></div>
        <div class="pt-2">
          <a href="index.php" class="rounded-md bg-brand-dark px-5 py-2 font-bold text-white inline-block">Kembali ke Beranda</a>
        </div>
      </div>
    <?php else: ?>
      <form method="post" class="grid gap-4 bg-white/70 backdrop-blur-sm rounded-xl p-5 border border-brand-dark/10">
        <input type="hidden" name="step" value="preview" />
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Nama</label>
          <input name="name" type="text" required class="rounded-md border border-brand-dark/20 px-3 py-2" placeholder="Nama lengkap" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Nomor HP</label>
          <input name="phone" type="tel" required class="rounded-md border border-brand-dark/20 px-3 py-2" placeholder="08xxxxxxxxxx" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Tipe Mobil</label>
          <select name="vehicle_type_id" class="rounded-md border border-brand-dark/20 px-3 py-2" required>
            <option value="" disabled selected>Pilih tipe mobil</option>
            <?php foreach ($vehicleTypes as $vt): ?>
              <option value="<?php echo (int)$vt['id']; ?>"><?php echo htmlspecialchars($vt['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Nomor Plat</label>
          <input name="license_plate" type="text" required class="rounded-md border border-brand-dark/20 px-3 py-2 uppercase" placeholder="B 1234 XYZ" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Paket Cuci</label>
          <select name="wash_package_id" class="rounded-md border border-brand-dark/20 px-3 py-2" required>
            <option value="" disabled selected>Pilih paket</option>
            <?php foreach ($washPackages as $wp): ?>
              <option value="<?php echo (int)$wp['id']; ?>"><?php echo htmlspecialchars($wp['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="grid gap-1">
            <label class="text-sm font-semibold">Tanggal</label>
            <input name="date" type="date" required class="rounded-md border border-brand-dark/20 px-3 py-2" />
          </div>
          <div class="grid gap-1">
            <label class="text-sm font-semibold">Jam</label>
            <input name="time" type="time" required class="rounded-md border border-brand-dark/20 px-3 py-2" />
          </div>
        </div>
        <div class="pt-2">
          <button type="submit" class="rounded-md bg-brand-primary px-5 py-2 font-bold text-white hover:opacity-90">Lanjutkan</button>
        </div>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
