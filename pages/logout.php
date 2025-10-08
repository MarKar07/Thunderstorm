<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
secure_session_start();
require_once '../includes/security.php';
require_once '../includes/error_handler.php';

// Loki uloskirjautuminen
if (is_logged_in()) {
    log_security_event('logout', [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username']
    ]);
}

// Tuhoaa session turvallisesti
destroy_session();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uloskirjautuminen - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta http-equiv="refresh" content="3;url=../index.php">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <header>
        <nav>
            <h1>
                <img src="../assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
                <?php echo SITE_NAME; ?>
            </h1>
            <ul>
                <li><a href="../index.php">Etusivu</a></li>
                <li><a href="event-info.php">Tapahtuma</a></li>
                <li><a href="contact.php">Yhteystiedot</a></li>
                <li><a href="login.php">Kirjaudu</a></li>
                <li><a href="register.php">Rekisteröidy</a></li>
            </ul>
            <div class="mobile-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    
    <main>
        <h1>Olet kirjautunut ulos onnistuneesti! 👋</h1>
        <p>Kiitos käynnistä Thunderstorm Rock Festival -sivustolla!</p>
        <p>Sinut ohjataan automaattisesti etusivulle 3 sekunnin kuluttua...</p>
        
        <p>Jos automaattinen ohjaus ei toimi, <a href="../index.php">klikkaa tästä</a>.</p>
        
        <div style="margin-top: 30px;">
            <p><strong>Haluatko:</strong></p>
            <ul>
                <li><a href="login.php">Kirjautua uudelleen sisään</a></li>
                <li><a href="register.php">Rekisteröidä uuden käyttäjän</a></li>
                <li><a href="../index.php">Palata etusivulle</a></li>
            </ul>
        </div>
        
        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>📍 Yhteystiedot</h3>
                    <p><strong>Rock Events Finland Oy</strong></p>
                    <p>Musiikkikatu 15<br>33100 Tampere</p>
                    <p>📧 info@thunderstormrock.fi</p>
                    <p>📞 +358 40 123 4567</p>
                </div>

                <div class="footer-section">
                    <h3>🔗 Linkit</h3>
                    <ul>
                        <li><a href="../index.php">Etusivu</a></li>
                        <li><a href="event-info.php">Tapahtuma</a></li>
                        <li><a href="contact.php">Yhteystiedot</a></li>
                        <li><a href="login.php">Kirjaudu</a></li>
                        <li><a href="register.php">Rekisteröidy</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>🎸 Tapahtuma</h3>
                    <p><strong><?php echo EVENT_NAME; ?></strong></p>
                    <p><?php echo EVENT_DATE; ?></p>
                    <p><?php echo EVENT_LOCATION; ?></p>
                </div>

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
                <img src="../assets/images/logo.png" alt="Logo" class="footer-logo">
                <p>&copy; 2025 <?php echo SITE_NAME; ?>. Kaikki oikeudet pidätetään.</p>
            </div>
        </footer>
    </main>
</body>
</html>