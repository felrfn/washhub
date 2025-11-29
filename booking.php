<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Token untuk img.logo.dev: ambil dari ENV LOGO_TOKEN jika ada, fallback hardcode
$logoToken = getenv('LOGO_TOKEN') ?: 'pk_KmumDwahR9y2kUSSui1Ieg';
$logoTokenQuery = $logoToken ? ('?token=' . urlencode($logoToken)) : '';

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
$paketBySlug = [];
foreach ($washPackages as $wp) {
  $id = (int)$wp['id'];
  $name = strtolower(trim($wp['name'] ?? ''));
  $paketHarga[$id] = (int)($wp['harga'] ?? 0);
  if ($name) { $paketBySlug[$name] = $id; }
}

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

// Prefill from layanan link: booking.php?paket=basic|standard|premium (case-insensitive)
if ($state['wash_package_id'] === 0 && isset($_GET['paket'])) {
  $q = strtolower(trim($_GET['paket']));
  if (isset($paketBySlug[$q])) { $state['wash_package_id'] = (int)$paketBySlug[$q]; }
}

// Normalize plate to uppercase without double spaces
if ($state['license_plate']) { $state['license_plate'] = strtoupper(preg_replace('/\s+/', ' ', $state['license_plate'])); }

// Validation helpers
function base_valid_state(array $s): bool {
  return $s['name'] !== '' && $s['phone'] !== '' && $s['vehicle_type_id'] > 0 && $s['license_plate'] !== '' && $s['wash_package_id'] > 0 && $s['date'] !== '' && $s['time'] !== '';
}

function validate_datetime_future(string $date, string $time): array {
  // Returns [bool ok, string errorMessage]
  // Accepts YYYY-MM-DD and HH:MM (24h)
  $date = trim($date);
  $time = trim($time);
  // Business hours constraint 09:00-16:00
  $OPEN_MIN = '09:00';
  $OPEN_MAX = '16:00';
  if ($time !== '') {
    if ($time < $OPEN_MIN || $time > $OPEN_MAX) {
      return [false, 'Waktu pendaftaran hanya antara 09:00–16:00.'];
    }
  }
  $d = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
  if (!$d) {
    return [false, 'Tanggal/Waktu tidak valid.'];
  }
  $now = new DateTime('now');
  if ($d < $now) {
    // If same date but earlier time, give specific message
    $today = (new DateTime('now'))->format('Y-m-d');
    if ($date === $today) {
      return [false, 'Waktu tidak boleh sebelum waktu saat ini.'];
    }
    return [false, 'Tanggal tidak boleh sebelum hari ini.'];
  }
  return [true, ''];
}

// Load draft from session when returning to form (GET) so fields are prefilled
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['booking_draft'])) {
  $draft = $_SESSION['booking_draft'];
  $state = array_merge($state, [
    'name' => $draft['name'] ?? '',
    'phone' => $draft['phone'] ?? '',
    'vehicle_type_id' => (int)($draft['vehicle_type_id'] ?? 0),
    'license_plate' => $draft['license_plate'] ?? '',
    'wash_package_id' => (int)($draft['wash_package_id'] ?? 0),
    'date' => $draft['date'] ?? '',
    'time' => $draft['time'] ?? '',
  ]);
}

// Prioritize paket from layanan link over any session draft
if (isset($_GET['paket'])) {
  $q = strtolower(trim($_GET['paket']));
  if (isset($paketBySlug[$q])) { $state['wash_package_id'] = (int)$paketBySlug[$q]; }
}

// Compute validation states
$validFields = base_valid_state($state);
[$validFuture, $dateTimeErr] = validate_datetime_future($state['date'] ?? '', $state['time'] ?? '');
$canPreview = ($step === 'preview' && $validFields && $validFuture);
$canPay = ($step === 'pay' && $validFields && $validFuture);

// Persist draft on preview when valid
if ($canPreview) { $_SESSION['booking_draft'] = $state; }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking — WashHub</title>
  <?php include 'components/head-resources.php'; ?>
</head>
<body class="min-h-dvh flex flex-col font-sans bg-white text-brand-dark">
  <?php include 'components/header.php'; ?>

  <main class="flex-grow w-full max-w-[500px] md:max-w-4xl mx-auto px-6 py-6 pb-20">
    <div class="flex items-center gap-4 mb-6">
      <img src="public/logo.png" alt="WashHub" class="w-16 md:w-20 object-contain" />
      <h1 class="text-2xl md:text-3xl font-bold leading-tight">
        <?php echo $step === 'preview' ? 'Metode Pembayaran' : ($step === 'success' ? 'Booking Berhasil' : 'Form Booking Cuci Mobil'); ?>
      </h1>
    </div>

    <?php if ($canPreview): ?>
      <?php $harga = $paketHarga[$state['wash_package_id']] ?? 0; ?>
      <?php $acc = (string)random_int(1000000000, 9999999999999); ?>
      <form id="payment-form" method="post" class="grid gap-4">
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
          <?php 
            $methods = [
              ['key'=>'QRIS','label'=>'QRIS','logo'=>'./public/qris.png'],
              ['key'=>'BRI','label'=>'BRI','logo'=>'https://img.logo.dev/bri.co.id' . $logoTokenQuery],
              ['key'=>'Mandiri','label'=>'Mandiri','logo'=>'https://img.logo.dev/mandiri-capital.co.id' . $logoTokenQuery],
              ['key'=>'BNI','label'=>'BNI','logo'=>'https://img.logo.dev/bni.co.id' . $logoTokenQuery],
              ['key'=>'BTN','label'=>'BTN','logo'=>'https://img.logo.dev/btn.co.id' . $logoTokenQuery],
              ['key'=>'BSI','label'=>'BSI','logo'=>'https://img.logo.dev/bankbsi.co.id' . $logoTokenQuery],
              ['key'=>'BCA','label'=>'BCA','logo'=>'https://img.logo.dev/bca.co.id' . $logoTokenQuery],
            ];
          ?>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <?php foreach ($methods as $m): ?>
              <label class="flex items-center gap-3 rounded-md border border-brand-dark/20 bg-white px-3 py-3 cursor-pointer">
                <input type="radio" name="method" value="<?php echo htmlspecialchars($m['key'], ENT_QUOTES, 'UTF-8'); ?>" required />
                <?php if ($m['key']==='QRIS'): ?>
                  <span class="text-sm font-semibold">QRIS</span>
                <?php elseif (!empty($m['logo'])): ?>
                  <img src="<?php echo htmlspecialchars($m['logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($m['label'], ENT_QUOTES, 'UTF-8'); ?>" class="h-7 w-auto" onerror="this.style.display='none'" />
                  <span class="text-sm font-semibold hidden md:inline"><?php echo htmlspecialchars($m['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php else: ?>
                  <span class="text-sm font-semibold"><?php echo htmlspecialchars($m['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div id="qris-inline" class="mt-3 hidden">
            <div class="text-sm opacity-80 mb-2">Scan QRIS berikut untuk pembayaran:</div>
            <div class="flex items-center justify-center">
              <img src="public/qris.png" alt="QRIS" class="w-56 h-56 object-contain" onerror="this.outerHTML='<div class=\'text-sm opacity-70\'>QRIS image not available</div>'" />
            </div>
          </div>
          <!-- Modal Pay Now -->
          <div id="paynow-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-[min(520px,92%)] p-5">
              <div class="flex items-center gap-3 mb-2">
                <img src="public/qris.png" alt="QR" class="w-10 h-10 object-contain hidden" id="paynow-qris-icon" />
                <h3 class="text-lg font-bold">Pay Now!</h3>
              </div>
              <p class="text-sm opacity-80 mb-3">Konfirmasi pembayaran untuk metode: <span id="paynow-method" class="font-semibold"></span></p>
              <div id="paynow-qris" class="hidden mb-4">
                <div class="text-sm opacity-80 mb-2">Scan QRIS berikut untuk pembayaran:</div>
                <div class="rounded-lg border border-brand-dark/20 bg-white p-3 flex items-center justify-center">
                  <img src="public/qris.png" alt="QRIS" class="w-56 h-56 object-contain" onerror="this.outerHTML='<div class=\'text-sm opacity-70\'>QRIS image not available</div>'" />
                </div>
              </div>
              <div id="paynow-target-box" class="rounded-lg border border-brand-dark/20 bg-white p-3 mb-4">
                <div class="text-xs opacity-70 mb-1">Nomor Tujuan</div>
                <div id="paynow-target" class="text-base font-mono tracking-wider select-all"><?php echo chunk_split($acc, 4, ' '); ?></div>
              </div>
              <div class="flex items-center justify-end gap-2">
                <button type="button" id="paynow-cancel" class="rounded-md bg-gray-200 px-4 py-2">Batal</button>
                <button type="button" id="paynow-ok" class="rounded-md bg-brand-primary text-white px-4 py-2 font-bold">OK</button>
              </div>
            </div>
          </div>

          <script>
            (function(){
              const form = document.getElementById('payment-form');
              const radios = document.querySelectorAll('input[name="method"]');
              const modal = document.getElementById('paynow-modal');
              const okBtn = document.getElementById('paynow-ok');
              const cancelBtn = document.getElementById('paynow-cancel');
              const methodEl = document.getElementById('paynow-method');
              const qrisIcon = document.getElementById('paynow-qris-icon');
              const qrisBox = document.getElementById('paynow-qris');
              const targetBox = document.getElementById('paynow-target-box');
              const qrisInline = document.getElementById('qris-inline');
              const nextBtn = document.getElementById('paynow-open');
              let chosen = null;
              function openModal(method){
                chosen = method;
                methodEl.textContent = method;
                const isQris = method === 'QRIS';
                qrisIcon.classList.toggle('hidden', !isQris);
                qrisBox.classList.toggle('hidden', !isQris);
                if (targetBox) targetBox.classList.toggle('hidden', isQris);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
              }
              function closeModal(){
                modal.classList.add('hidden');
                modal.classList.remove('flex');
              }
              radios.forEach(r=>r.addEventListener('change', (e)=>{
                if (qrisInline) qrisInline.classList.toggle('hidden', e.target.value !== 'QRIS');
                if (nextBtn) nextBtn.disabled = false;
                openModal(e.target.value);
              }));
              if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.addEventListener('click', ()=>{
                  const sel = Array.from(radios).find(r=>r.checked)?.value;
                  if (sel) openModal(sel);
                });
              }
              cancelBtn.addEventListener('click', ()=>{
                closeModal();
              });
              okBtn.addEventListener('click', ()=>{
                // auto-submit to pay
                const stepInput = document.createElement('input');
                stepInput.type = 'hidden'; stepInput.name = 'step'; stepInput.value = 'pay';
                form.appendChild(stepInput);
                const accInput = document.createElement('input');
                accInput.type = 'hidden'; accInput.name = 'account_number'; accInput.value = '<?php echo htmlspecialchars($acc, ENT_QUOTES, 'UTF-8'); ?>';
                form.appendChild(accInput);
                closeModal();
                form.submit();
              });
            })();
          </script>
        </div>

        <!-- Removed static Nomor Tujuan per request; shown only in modal -->

        <?php foreach ($state as $k=>$v): ?>
          <input type="hidden" name="<?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?>" />
        <?php endforeach; ?>
        <!-- Actions: Back on left, Next on right -->
        <div class="flex items-center justify-between gap-3 mt-2">
          <a href="booking.php" class="text-sm underline">Kembali</a>
          <button type="button" id="paynow-open" class="rounded-md bg-brand-primary px-5 py-2 font-bold text-white">Lanjutkan</button>
        </div>
      </form>
    <?php elseif ($canPay): ?>
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

          // Clear draft after successful creation
          unset($_SESSION['booking_draft']);

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
      <?php
        $errorMessage = '';
        if ($step !== 'form') {
          if (!$validFields) { $errorMessage = 'Lengkapi semua field wajib.'; }
          elseif (!$validFuture) { $errorMessage = $dateTimeErr; }
        }
      ?>
      <?php if (!empty($errorMessage)): ?>
        <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <form method="post" class="grid gap-4">
        <input type="hidden" name="step" value="preview" />
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Nama</label>
          <input name="name" type="text" required class="rounded-md border border-brand-dark/20 px-3 py-2" placeholder="Nama lengkap" value="<?php echo htmlspecialchars($state['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Nomor HP</label>
          <input name="phone" type="tel" required class="rounded-md border border-brand-dark/20 px-3 py-2" placeholder="08xxxxxxxxxx" value="<?php echo htmlspecialchars($state['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Tipe Mobil</label>
          <select name="vehicle_type_id" class="rounded-md border border-brand-dark/20 px-3 py-2" required>
            <option value="" disabled <?php echo (($state['vehicle_type_id'] ?? 0)===0)?'selected':''; ?>>Pilih tipe mobil</option>
            <?php foreach ($vehicleTypes as $vt): $vid=(int)$vt['id']; ?>
              <option value="<?php echo $vid; ?>" <?php echo ($vid===($state['vehicle_type_id'] ?? 0))?'selected':''; ?>><?php echo htmlspecialchars($vt['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Nomor Plat</label>
          <input name="license_plate" type="text" required class="rounded-md border border-brand-dark/20 px-3 py-2 uppercase" placeholder="B 1234 XYZ" value="<?php echo htmlspecialchars($state['license_plate'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
        <div class="grid gap-1">
          <label class="text-sm font-semibold">Paket Cuci</label>
          <select name="wash_package_id" class="rounded-md border border-brand-dark/20 px-3 py-2" required>
            <option value="" disabled <?php echo (($state['wash_package_id'] ?? 0)===0)?'selected':''; ?>>Pilih paket</option>
            <?php foreach ($washPackages as $wp): $pid=(int)$wp['id']; ?>
              <option value="<?php echo $pid; ?>" <?php echo ($pid===($state['wash_package_id'] ?? 0))?'selected':''; ?>><?php echo htmlspecialchars($wp['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="grid gap-1">
            <label class="text-sm font-semibold">Tanggal</label>
            <input id="booking-date" name="date" type="date" required class="rounded-md border border-brand-dark/20 px-3 py-2" min="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($state['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
          </div>
          <div class="grid gap-1">
            <label class="text-sm font-semibold">Jam</label>
            <select id="booking-time" name="time" required class="rounded-md border border-brand-dark/20 px-3 py-2" data-selected="<?php echo htmlspecialchars($state['time'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <option value="" disabled selected>Pilih jam</option>
            </select>
            <div id="booking-time-note" class="text-xs opacity-70"></div>
          </div>
        </div>
        <div class="flex items-center justify-between gap-3 pt-2">
          <a href="index.php" class="text-sm underline">Kembali</a>
          <button type="submit" class="rounded-md bg-brand-primary px-5 py-2 font-bold text-white hover:opacity-90">Lanjutkan</button>
        </div>
      </form>
    <?php endif; ?>
  </main>
  <script>
    (function(){
      const dateEl = document.getElementById('booking-date');
      const timeEl = document.getElementById('booking-time');
      const noteEl = document.getElementById('booking-time-note');
      if (!dateEl || !timeEl) return;
      const pad = n => (n<10?('0'+n):''+n);
      const toHM = d => pad(d.getHours())+':'+pad(d.getMinutes());
      const todayStr = (new Date()).toISOString().slice(0,10);
      const OPEN_MIN = '09:00';
      const OPEN_MAX = '16:00';
      const STEP_MIN = 15; // interval menit

      function buildOptions() {
        const now = new Date();
        const curHM = toHM(now);
        const selectedDate = dateEl.value || todayStr;
        // Hitung threshold min untuk hari terpilih
        let threshold = OPEN_MIN;
        if (selectedDate === todayStr) {
          threshold = (curHM > OPEN_MIN ? curHM : OPEN_MIN);
          if (threshold > OPEN_MAX) threshold = OPEN_MAX;
        }
        // Bersihkan opsi
        while (timeEl.options.length > 0) timeEl.remove(0);
        // Placeholder
        const ph = document.createElement('option');
        ph.textContent = 'Pilih jam'; ph.value = ''; ph.disabled = true; ph.selected = true;
        timeEl.appendChild(ph);

        // Generate slot 09:00 - 16:00 setiap 15 menit
        const [openH, openM] = OPEN_MIN.split(':').map(Number);
        const [closeH, closeM] = OPEN_MAX.split(':').map(Number);
        const base = new Date();
        base.setHours(openH, openM, 0, 0);
        const end = new Date();
        end.setHours(closeH, closeM, 0, 0);

        const saved = timeEl.getAttribute('data-selected') || '';
        let firstEnabledValue = '';

        for (let d = new Date(base); d <= end; d.setMinutes(d.getMinutes() + STEP_MIN)) {
          const hm = toHM(d);
          const opt = document.createElement('option');
          opt.value = hm; opt.textContent = hm;
          const disabled = (hm < OPEN_MIN) || (hm > OPEN_MAX) || (selectedDate === todayStr && hm < threshold);
          if (disabled) opt.disabled = true;
          if (!disabled && !firstEnabledValue) firstEnabledValue = hm;
          if (saved && saved === hm) opt.selected = true;
          timeEl.appendChild(opt);
        }

        // Jika terpilih disabled/placeholder, pilih opsi pertama yang aktif
        if (!timeEl.value || timeEl.value === '' || timeEl.selectedOptions[0]?.disabled) {
          if (firstEnabledValue) {
            timeEl.value = firstEnabledValue;
          }
        }

        // Catatan informasi
        if (selectedDate === todayStr) {
          if (threshold >= OPEN_MAX) {
            noteEl.textContent = 'Pendaftaran hari ini telah tutup (buka 09:00–16:00).';
          } else if (threshold > OPEN_MIN) {
            noteEl.textContent = 'Slot sebelum '+ threshold +' tidak tersedia untuk hari ini.';
          } else {
            noteEl.textContent = '';
          }
        } else {
          noteEl.textContent = '';
        }
      }

      // Pastikan min tanggal adalah hari ini
      if (!dateEl.min) dateEl.min = todayStr;
      // Build awal dan saat tanggal berubah
      buildOptions();
      dateEl.addEventListener('change', buildOptions);
    })();
  </script>
</body>
</html>
