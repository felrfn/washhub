<?php
// Landing page WashHub - Redesign sesuai referensi UI
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>WashHub — Clean. Shine. Repeat</title>
  <?php include 'components/head-resources.php'; ?>
</head>
<body class="min-h-screen flex flex-col relative font-sans overflow-hidden">

  <div class="absolute inset-0 z-0">
    <img src="public/home-background.png" alt="Background" class="w-full h-full object-cover object-center" />
    
    <div class="absolute inset-0 bg-gradient-to-b from-white/90 via-white/60 to-brand-teal/90 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-brand-teal/80 via-transparent to-white/40"></div>
  </div>

  <div class="relative z-10 flex flex-col min-h-screen w-full max-w-[500px] mx-auto md:max-w-full">
    
    <header class="w-full px-4 py-4 flex items-center justify-between md:justify-center md:gap-20 md:max-w-6xl md:mx-auto">
      
      <nav class="flex items-center gap-2 text-brand-primary font-bold text-sm md:text-base tracking-wide">
        <a href="index.php" class="hover:text-brand-dark">Home</a>
        <span class="text-brand-primary/40">|</span>
        <a href="layanan.php" class="hover:text-brand-dark">Layanan</a>
        <span class="text-brand-primary/40">|</span>
        <a href="booking.php" class="hover:text-brand-dark">Booking</a>
        <span class="text-brand-primary/40">|</span>
        <a href="kontak.php" class="hover:text-brand-dark">Kontak</a>
      </nav>

      <div class="absolute top-4 right-4 md:static">
        <a href="login-admin.php" class="bg-brand-primary text-white text-xs font-bold px-4 py-1.5 rounded-full hover:bg-brand-dark transition shadow-lg">
          login admin
        </a>
      </div>
    </header>

    <main class="flex-grow flex flex-col items-center text-center px-6 pt-0 pb-20 justify-center">
      
      <h2 class="text-[#2EA4BF] text-lg font-bold mb-4 tracking-wide">Selamat datang, di</h2>

      <div class="mb-6 flex flex-col items-center">
        <img src="public/logo.png" alt="WashHub Logo" class="w-32 md:w-40 drop-shadow-sm mb-2">
        </div>

      <div class="font-serif text-brand-dark space-y-1 mb-10">
        <h1 class="text-3xl md:text-5xl font-bold leading-tight">
          Mau Cuci Mobil?
        </h1>
        <h1 class="text-3xl md:text-5xl font-bold leading-tight">
          Booking Online
        </h1>
        <h1 class="text-3xl md:text-5xl font-bold leading-tight">
          Tanpa Ribet
        </h1>
      </div>

      <a href="booking.php" class="bg-brand-primary text-white font-serif text-xl tracking-widest font-bold px-12 py-3 rounded-xl shadow-[0_4px_14px_rgba(0,0,0,0.25)] hover:scale-105 hover:bg-brand-dark transition-transform duration-200 uppercase">
        Book Now
      </a>

    </main>

  </div>

</body>
</html>