<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}
$admin = $_SESSION['admin'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — WashHub</title>
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
        <span class="opacity-80">Halo, <?php echo htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8'); ?></span>
        <a class="hover:underline" href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="mx-auto w-[min(1100px,92%)] py-10 grow">
    <h1 class="mb-4 text-2xl font-bold">Dashboard Admin</h1>
    <div class="rounded-xl border border-brand-soft/60 bg-white p-6 shadow-[0_10px_28px_rgba(193,225,246,.45)]">
      <h2 class="mb-3 text-xl font-semibold">Profil</h2>
      <div class="grid gap-3 md:grid-cols-2">
        <div>
          <div class="text-sm opacity-70">Username</div>
          <div class="font-semibold"><?php echo htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div>
          <div class="text-sm opacity-70">Nama</div>
          <div class="font-semibold"><?php echo htmlspecialchars($admin['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div>
          <div class="text-sm opacity-70">Telepon</div>
          <div class="font-semibold"><?php echo htmlspecialchars($admin['phone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div>
          <div class="text-sm opacity-70">Alamat</div>
          <div class="font-semibold"><?php echo nl2br(htmlspecialchars($admin['address'] ?? '-', ENT_QUOTES, 'UTF-8')); ?></div>
        </div>
      </div>
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
