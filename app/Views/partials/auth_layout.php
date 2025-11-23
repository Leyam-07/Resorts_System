<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Resort Management' ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Auth CSS -->
    <link href="<?= BASE_URL ?>/assets/css/auth.css" rel="stylesheet">
    <!-- Font Awesome for theme switcher icon -->
    <link href="<?= BASE_URL ?>/assets/css/fontawesome.min.css" rel="stylesheet">
</head>
<body class="auth-body <?= $bodyClass ?? '' ?>">

    <div class="auth-container">
        <div class="auth-background-overlay"></div> <!-- Add this for the overlay -->
        <div class="auth-panel">
            <!-- Image Carousel Section -->
            <div class="auth-image-section">
                <div class="auth-image-overlay"></div>
                <div class="tagline text-center">Resort Haven — Your Exclusive Gateway to Paradise</div>
            </div>

            <!-- Form Section -->
            <div class="auth-form-section">
                <div class="theme-switcher" id="theme-switcher">
                    <div class="theme-switcher-slider"></div>
                    <i class="fas fa-sun"></i>
                    <i class="fas fa-moon"></i>
                </div>
                <?php
                    // This is where the content from login.php, register.php, etc. will be injected.
                    // The calling view must define a $formContent callable.
                    if (isset($formContent) && is_callable($formContent)) {
                        $formContent();
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Auth JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- Image Carousel ---
            const imageSection = document.querySelector('.auth-image-section');
            if (imageSection) {
                const images = [
                    '<?= BASE_URL ?>/assets/images/carousel-1.jpg',
                    '<?= BASE_URL ?>/assets/images/carousel-2.jpg',
                    '<?= BASE_URL ?>/assets/images/carousel-3.jpg'
                ];
                let currentImageIndex = 0;

                // Preload images
                images.forEach(src => {
                    const img = new Image();
                    img.src = src;
                });

                setInterval(() => {
                    currentImageIndex = (currentImageIndex + 1) % images.length;
                    imageSection.style.backgroundImage = `url('${images[currentImageIndex]}')`;
                }, 3000); // Change image every 3 seconds

                // Set initial image
                imageSection.style.backgroundImage = `url('${images[0]}')`;
            }

            // --- Theme Switcher ---
            const themeSwitcher = document.getElementById('theme-switcher');
            const body = document.body;
            const sunIcon = themeSwitcher.querySelector('.fa-sun');
            const moonIcon = themeSwitcher.querySelector('.fa-moon');

            if (themeSwitcher) {
                // Function to update icon state
                const updateIcons = () => {
                    if (body.classList.contains('dark-theme')) {
                        sunIcon.classList.remove('active');
                        moonIcon.classList.add('active');
                    } else {
                        moonIcon.classList.remove('active');
                        sunIcon.classList.add('active');
                    }
                };

                // Check for saved theme in localStorage
                const currentTheme = localStorage.getItem('theme');
                if (currentTheme === 'dark') {
                    body.classList.add('dark-theme');
                }
                updateIcons(); // Set initial icon state

                themeSwitcher.addEventListener('click', () => {
                    body.classList.toggle('dark-theme');
                    
                    // Save theme preference
                    if (body.classList.contains('dark-theme')) {
                        localStorage.setItem('theme', 'dark');
                    } else {
                        localStorage.removeItem('theme');
                    }
                    updateIcons(); // Update icons on click
                });
            }

        });
    </script>
</body>
</html>