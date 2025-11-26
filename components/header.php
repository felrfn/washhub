<?php
// Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);

// Fungsi helper untuk class menu aktif
function navActive($page_name, $current_page) {
    if ($page_name === $current_page) {
        // Style jika aktif (Tebal & warna dark brand)
        return 'text-brand-dark font-extrabold'; 
    }
    // Style default (Hover effect)
    return 'hover:text-brand-dark transition-colors'; 
}
?>

<header class="w-full px-4 py-4 flex items-center justify-between md:justify-center md:gap-20 md:max-w-6xl md:mx-auto relative z-50">
  
  <nav class="flex items-center gap-2 text-brand-dark font-bold text-sm md:text-base tracking-wide">
    <a href="index.php" class="<?= navActive('index.php', $current_page) ?>">Home</a>
    
    <span class="text-brand-dark/40 font-light">|</span>
    
    <a href="layanan.php" class="<?= navActive('layanan.php', $current_page) ?>">Layanan</a>
    
    <span class="text-brand-dark/40 font-light">|</span>
    
    <a href="booking.php" class="<?= navActive('booking.php', $current_page) ?>">Booking</a>
    
    <span class="text-brand-dark/40 font-light">|</span>
    
    <a href="kontak.php" class="<?= navActive('kontak.php', $current_page) ?>">Kontak</a>
  </nav>

  <?php if ($current_page == 'index.php') : ?>
    <div class="absolute top-4 right-4 md:static">
      <a href="login-admin.php" class="bg-brand-dark text-white text-xs font-bold px-4 py-1.5 rounded-full hover:bg-brand-dark/90 transition shadow-lg uppercase tracking-wider">
        login admin
      </a>
    </div>
  <?php endif; ?>

</header>