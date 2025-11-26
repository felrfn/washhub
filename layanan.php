<?php
// Halaman Layanan WashHub
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Layanan — WashHub</title>
    <?php include __DIR__ . '/components/head-resources.php'; ?>
</head>
<body class="min-h-screen flex flex-col font-sans bg-gradient-to-b from-white via-[#E0F8FA] to-[#3DD1DB] text-brand-dark">

  <?php include __DIR__ . '/components/header.php'; ?>

  <main class="flex-grow w-full max-w-[500px] md:max-w-4xl mx-auto px-6 py-6 pb-20">

    <div class="flex items-center gap-4 mb-8">
      <img src="public/logo.png" alt="WashHub" class="w-20 md:w-24 object-contain">
      <h1 class="text-3xl md:text-4xl font-bold text-brand-dark leading-tight">
        Jenis Layanan Cuci
      </h1>
    </div>

    <div class="flex w-full">
      
      <div class="flex-1 pr-2 space-y-10">
        
        <div class="group">
          <h2 class="text-brand-cyan text-2xl font-bold uppercase mb-1">PAKET BASIC</h2>
          <p class="text-brand-dark text-xl font-bold mb-3">Rp50.000</p>
          
          <ul class="text-sm font-semibold text-brand-dark/90 space-y-1 leading-snug mb-4">
            <li>Pencucian bagian luar bodi</li>
            <li>Bilas standar menggunakan air bertekanan sedang</li>
            <li>Pembersihan cepat kaca & spion</li>
            <li>Pengeringan dasar dengan microfiber</li>
            <li>Estimasi waktu: 20–25 menit</li>
          </ul>

          <a href="booking.php?paket=basic" class="inline-block bg-white text-brand-cyan font-bold py-1.5 px-6 shadow-card hover:scale-105 transition-transform border border-gray-100">
            PILIH PAKET
          </a>
        </div>

        <div class="group">
          <h2 class="text-brand-cyan text-2xl font-bold uppercase mb-1">PAKET STANDARD</h2>
          <p class="text-brand-dark text-xl font-bold mb-3">Rp90.000</p>
          
          <ul class="text-sm font-semibold text-brand-dark/90 space-y-1 leading-snug mb-4">
            <li>Cuci luar & dalam (interior ringan)</li>
            <li>Bilas tekanan tinggi untuk kotoran membandel</li>
            <li>Pembersihan dashboard & vakum interior cepat</li>
            <li>Pengeringan premium microfiber anti-baret</li>
            <li>Semprot pewangi kabin</li>
            <li>Pengecekan kebersihan sebelum diserahkan</li>
            <li>Estimasi waktu: 35–45 menit</li>
          </ul>

          <a href="booking.php?paket=standard" class="inline-block bg-white text-brand-cyan font-bold py-1.5 px-6 shadow-card hover:scale-105 transition-transform border border-gray-100">
            PILIH PAKET
          </a>
        </div>

        <div class="group">
          <h2 class="text-brand-cyan text-2xl font-bold uppercase mb-1">PAKET PREMIUM</h2>
          <p class="text-brand-dark text-xl font-bold mb-3">Rp150.000</p>
          
          <ul class="text-sm font-semibold text-brand-dark/90 space-y-1 leading-snug mb-4">
            <li>Deep cleaning luar & dalam</li>
            <li>Bilas ultra-clear untuk hasil lebih kinclong</li>
            <li>Pengeringan detail termasuk sela-sela pintu & grill</li>
            <li>Vakum full interior termasuk kolong jok</li>
            <li>Pembersihan area sulit dijangkau (sela-sela kecil)</li>
            <li>Pewangi premium tahan lama</li>
            <li>Finishing glossy untuk tampilan maksimal</li>
            <li>Estimasi waktu: 60–75 menit</li>
          </ul>

          <a href="booking.php?paket=premium" class="inline-block bg-white text-brand-cyan font-bold py-1.5 px-6 shadow-card hover:scale-105 transition-transform border border-gray-100">
            PILIH PAKET
          </a>
        </div>

      </div>

      <div class="w-[35%] md:w-[30%] flex flex-col pt-2">
        <img src="public/layanan/layanan-1.png" alt="Service Showcase 1" class="w-full object-cover">
        <img src="public/layanan/layanan-2.png" alt="Service Showcase 2" class="w-full object-cover">
      </div>

    </div>

  </main>

</body>
</html>