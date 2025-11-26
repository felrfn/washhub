<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (isset($_SESSION['admin'])) {
  header('Location: admin/');
  exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    $error = 'Username dan password wajib diisi';
  } else {
    try {
      $conn = db();
      $stmt = $conn->prepare('SELECT id_admin, username, password, nama_lengkap FROM admin WHERE username = ? LIMIT 1');
      $stmt->bind_param('s', $username);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($row = $result->fetch_assoc()) {
        // Catatan: Skema saat ini password VARCHAR(50). Jika Anda ingin hashing, ubah ke VARCHAR(255) dan gunakan password_verify.
        if ($password === $row['password']) {
          $_SESSION['admin'] = [
            'id' => (int)$row['id_admin'],
            'username' => $row['username'],
            'name' => $row['nama_lengkap'] ?? '',
            'phone' => null,
            'address' => null,
          ];
          header('Location: admin/');
          exit;
        } else {
          $error = 'Username atau password salah';
        }
      } else {
        $error = 'Username atau password salah';
      }
    } catch (Throwable $e) {
      $error = 'Kesalahan koneksi database: ' . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin — WashHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Konkhmer+Sleokchher&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { brand: { dark:'#013E68', accent:'#22C8D3', soft:'#C1E1F6' } }, fontFamily: { display: ['\"Konkhmer Sleokchher\"','system-ui','-apple-system','Segoe UI','Roboto','Arial','sans-serif'] } } } }
  </script>
</head>
<body class="font-display text-brand-dark min-h-dvh flex flex-col bg-[linear-gradient(180deg,#FFFFFF_29%,#22C8D3_100%)]">
  <header class="sticky top-0 backdrop-saturate-150 backdrop-blur-md bg-white/70 border-b border-brand-dark/15">
    <div class="mx-auto w-[min(1100px,92%)] flex items-center justify-between py-3.5">
      <a class="text-xl font-bold tracking-wide" href="index.php">WashHub</a>
      <nav class="flex items-center gap-4 text-[0.95rem]">
        <a class="hover:underline" href="index.php">Home</a>
        <a class="hover:underline" href="login.php">Login</a>
      </nav>
    </div>
  </header>

  <main class="mx-auto w-[min(1100px,92%)] py-10 grow">
    <div class="mx-auto max-w-md rounded-xl border border-brand-soft/60 bg-white p-6 shadow-[0_10px_28px_rgba(193,225,246,.45)]">
      <h1 class="mb-4 text-2xl font-bold">Login Admin</h1>
      <?php if ($error): ?>
        <div class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <form method="post" class="grid gap-3">
        <label class="grid gap-1">
          <span class="text-sm">Username</span>
          <input name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-md border border-brand-dark/20 px-3 py-2" type="text" required />
        </label>
        <label class="grid gap-1">
          <span class="text-sm">Password</span>
          <input name="password" class="rounded-md border border-brand-dark/20 px-3 py-2" type="password" required />
        </label>
        <button class="mt-2 rounded-md bg-brand-dark px-4 py-2 font-bold text-white" type="submit">Masuk</button>
      </form>
    </div>
  </main>

  <footer class="mt-auto py-6 pb-9 text-center text-brand-dark/80">
    <div class="mx-auto flex w-[min(1100px,92%)] items-center justify-center gap-2">
      <span>© <?php echo date('Y'); ?> WashHub</span>
      <span>·</span>
      <a class="opacity-90 hover:underline" href="index.php">Home</a>
    </div>
  </footer>
</body>
</html>
