<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Konkhmer+Sleokchher&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>

<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            dark: '#003354',    // Dark Blue Text
            primary: '#013E68', // Logo Blue
            teal: '#22C8D3',    // Teal Accent
            sky: '#D6F2F4',     // Light Background
          }
        },
        fontFamily: {
          display: ['"Konkhmer Sleokchher"', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
          sans: ['system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
          serif: ['"Times New Roman"', 'Times', 'serif'], // Untuk headline
        }
      }
    }
  }
</script>