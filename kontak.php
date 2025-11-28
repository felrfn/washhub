<?php
require_once __DIR__ . '/db_connect.php';

function normalizePhone($number, $format = '0') {
    $number = preg_replace('/\D/', '', $number);

    if ($format === '0') {
        if (substr($number, 0, 2) === '62') {
            return '0' . substr($number, 2);
        }
        return $number;
    }

    if ($format === '62') {
        if (substr($number, 0, 1) === '0') {
            return '62' . substr($number, 1);
        }
        return $number;
    }

    return $number;
}

// Ambil nomor admin dari DB (id_admin = 1) dengan fallback
$number = "082123123123";
try {
  $conn = db();
  if ($stmt = $conn->prepare('SELECT nomor_hp FROM admin WHERE id_admin = 1 LIMIT 1')) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
      $hp = trim((string)($row['nomor_hp'] ?? ''));
      if ($hp !== '') { $number = $hp; }
    }
  }
} catch (Throwable $e) {
  // keep fallback
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontak — WashHub</title>
  
  <?php include 'components/head-resources.php'; ?>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen flex flex-col font-sans bg-gray-50">

  <div class="fixed inset-0 z-0 h-[65vh] w-full">
    <img src="public/contact-background.png" alt="Car Wash Background" class="w-full h-full object-cover">
   <div class="absolute inset-0 bg-brand-teal/25 mix-blend-multiply"></div>
  </div>

  <div class="relative z-10 flex flex-col flex-grow w-full max-w-[500px] mx-auto md:max-w-4xl shadow-2xl bg-transparent min-h-screen">
    
    <?php include 'components/header.php'; ?>

    <main class="flex-grow flex flex-col">
      
      <div class="flex flex-col items-center justify-center pt-4 pb-12 px-6 text-center">
        
        <div class="flex items-center gap-4 mb-10">
          <img src="public/logo.png" alt="WashHub" class="w-20 md:w-24 drop-shadow-sm">
          <h1 class="text-4xl font-bold text-brand-dark tracking-tight">Kontak</h1>
        </div>

        <div class="mb-2">
            <i class="fa-brands fa-whatsapp text-6xl text-[#25D366] drop-shadow-sm"></i>
        </div>
        
        <h2 class="text-[#25D366] text-3xl font-bold mb-6 tracking-wide drop-shadow-[0_2px_2px_rgba(255,255,255,0.8)]">
            WhatsApp
        </h2>

        <a href="https://wa.me/<?php echo normalizePhone($number, "62"); ?>" target="_blank" class="bg-brand-dark text-white font-serif text-2xl md:text-3xl font-bold py-3 px-10 rounded-full shadow-[0_4px_15px_rgba(0,51,84,0.4)] hover:scale-105 transition-transform mb-3">
            <?php echo normalizePhone($number, "0"); ?>
        </a>
        
        <a href="https://wa.me/<?php echo normalizePhone($number, "62"); ?>" target="_blank"  class="text-brand-dark font-bold text-sm md:text-base opacity-90">
            Start New Chat
        </a>

      </div>

      <div class="bg-brand-dark text-white pt-10 pb-12 px-8 rounded-t-[2.5rem] mt-auto shadow-[0_-10px_30px_rgba(0,0,0,0.1)]">
        
        <h3 class="text-2xl font-bold mb-8">Crystal Car Wash</h3>

        <ul class="space-y-6 text-sm md:text-base font-medium leading-relaxed">
          
          <li class="flex items-start gap-4">
            <i class="fa-solid fa-location-dot mt-1 text-lg w-6 text-center text-brand-teal"></i>
            <span>
              Jl. Waru No. 37; Jakarta, Indonesia 13220, Daerah Khusus Ibukota Jakarta
            </span>
          </li>

          <li class="flex items-center gap-4">
            <i class="fa-solid fa-phone text-lg w-6 text-center text-brand-teal"></i>
            <span><?php echo normalizePhone($number, "0"); ?></span>
          </li>

          <li class="flex items-center gap-4">
            <i class="fa-brands fa-instagram text-lg w-6 text-center text-brand-teal"></i>
            <span>@washhubcar.id</span>
          </li>

          <li class="flex items-center gap-4">
            <i class="fa-solid fa-globe text-lg w-6 text-center text-brand-teal"></i>
            <a href="#" class="hover:underline hover:text-brand-teal transition-colors">www.washhubcar.id</a>
          </li>

        </ul>

      </div>

    </main>
  </div>

</body>
</html>