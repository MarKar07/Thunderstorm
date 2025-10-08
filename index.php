<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
    // Lataa turvallisuusfunktiot
    require_once 'config/config.php';
    require_once 'includes/session.php';
    secure_session_start();
    require_once 'includes/security.php';
    
    echo escape(SITE_NAME); 
    ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body>
    <!-- Animoidut tausta-blobit -->
    <div class="blob-2"></div>
    <div class="blob-3"></div>
    
    <?php
    $is_logged_in = is_logged_in();
    $is_admin = is_admin();
    ?>
    
    <!-- Navigointi -->
    <header>
        <nav>
            <h1>
                <img src="assets/images/logo.png" alt="<?php echo escape(SITE_NAME); ?>">
                <?php echo escape(SITE_NAME); ?>
            </h1>
            <ul>
                <li><a href="index.php">Etusivu</a></li>
                <li><a href="pages/event-info.php">Tapahtuma</a></li>
                <li><a href="pages/contact.php">Yhteystiedot</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="pages/profile.php">Profiili</a></li>
                    <?php if ($is_admin): ?>
                    <li><a href="pages/admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="pages/logout.php">Kirjaudu ulos</a></li>
                <?php else: ?>
                    <li><a href="pages/login.php">Kirjaudu</a></li>
                    <li><a href="pages/register.php">Rekisteröidy</a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    
    <!--Pääkontentti -->
    <main>
        <div class="hero-section">
            <div class="hero-background-1"></div>
            <div class="hero-background-2"></div>
            <div class="hero-content-wrapper">
                <h1><?php echo escape(SITE_NAME); ?></h1>
                <p class="hero-text">2 päivää täyttä rockia!</p>
                <p class="event-date"><strong><?php echo escape(EVENT_DATE); ?></strong> | <?php echo escape(EVENT_LOCATION); ?></p>

                <div class="cta-buttons">
                    <a href="pages/register.php" class="btn btn-primary">Osta liput</a>
                    <a href="pages/event-info.php" class="btn btn-secondary">Lisätietoja</a>
                </div>
            </div>
        </div>

        <section class="intro-section">
            <h2>Tervetuloa <?php echo escape(SITE_NAME); ?>ille!</h2>
            <p>Kaksi päivää täynnä huikeita rock-bändejä, hyvää ruokaa ja unohtumattomia hetkiä. 
               Tule mukaan Suomen rockaavimpaan tapahtumaan!</p>
            
            <div class="features">
                <div class="feature">
                    <h3>🎸 Huippuartistit</h3>
                    <p>Suomen ja kansainväliset rock-tähdet samalla lavalla</p>
                </div>
                <div class="feature">
                    <h3>🍔 Herkullista ruokaa</h3>
                    <p>Food truck -kulttuurin parhaimmistoa</p>
                </div>
                <div class="feature">
                    <h3>🎪 Festivaalitunnelmaa</h3>
                    <p>Ainutlaatuinen kokemus koko viikonlopuksi</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <!-- Yhteystiedot -->
            <div class="footer-section">
                <h3>📍 Yhteystiedot</h3>
                <p><strong>Rock Events Finland Oy</strong></p>
                <p>Musiikkikatu 15<br>33100 Tampere</p>
                <p>📧 info@thunderstormrock.fi</p>
                <p>📞 +358 40 123 4567</p>
            </div>

            <!-- Linkit -->
            <div class="footer-section">
                <h3>🔗 Linkit</h3>
                <ul>
                    <li><a href="index.php">Etusivu</a></li>
                    <li><a href="pages/event-info.php">Tapahtuma</a></li>
                    <li><a href="pages/contact.php">Yhteystiedot</a></li>
                    <li><a href="pages/login.php">Kirjaudu</a></li>
                    <li><a href="pages/register.php">Rekisteröidy</a></li>
                </ul>
            </div>

            <!-- Tapahtuma -->
            <div class="footer-section">
                <h3>🎸 Tapahtuma</h3>
                <p><strong><?php echo escape(EVENT_NAME); ?></strong></p>
                <p><?php echo escape(EVENT_DATE); ?></p>
                <p><?php echo escape(EVENT_LOCATION); ?></p>
            </div>

            <!-- Sosiaalinen media -->
            <div class="footer-section">
                <h3>🌐 Seuraa meitä</h3>
                <div class="social-links">
                    <a href="#" title="Facebook">📘</a>
                    <a href="#" title="Instagram">📷</a>
                    <a href="#" title="Twitter">🐦</a>
                    <a href="#" title="YouTube">📺</a>
                    <a href="#" title="TikTok">🎵</a>
                </div>
                <p style="margin-top: 15px;">#ThunderstormRock2025</p>
            </div>
        </div>

        <div class="footer-bottom">
            <img src="assets/images/logo.png" alt="Logo" class="footer-logo">
            <p>&copy; 2025 <?php echo escape(SITE_NAME); ?>. Kaikki oikeudet pidätetään.</p>
        </div>
    </footer>

    <script>
    // Hero-slideshow - Kaksi kerrosta vaihtuvat
    const heroImages = [
        'assets/images/audience-1867754_1920.jpg',
        'assets/images/musician-1658887_1920.jpg',
        'assets/images/music-819152_1920.jpg',
        'assets/images/people-2569551_1920.jpg',
        'assets/images/people-2600644_1920.jpg', 
        'assets/images/singer-1595864_1920.jpg',
    ];

    let currentImageIndex = 0;
    let useLayer1 = true;

    function changeHeroImage() {
        const layer1 = document.querySelector('.hero-background-1');
        const layer2 = document.querySelector('.hero-background-2');
        
        currentImageIndex = (currentImageIndex + 1) % heroImages.length;
        const newImage = `linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('${heroImages[currentImageIndex]}')`;
        
        if (useLayer1) {
            layer2.style.backgroundImage = newImage;
            layer2.style.opacity = '1';
            layer1.style.opacity = '0';
        } else {
            layer1.style.backgroundImage = newImage;
            layer1.style.opacity = '1';
            layer2.style.opacity = '0';
        }
        
        useLayer1 = !useLayer1;
    }

    // Vaihda kuvaa 6 sekunnin välein
    setInterval(changeHeroImage, 6000);
    </script>

</body>
</html>