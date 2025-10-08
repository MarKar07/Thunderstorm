<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
secure_session_start();
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
require_once '../includes/error_handler.php';
require_once '../config/database.php';

$message = "";
$message_type = "";

// Käsitellään rekisteröinti
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF-suojaus
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        handle_error("Turvallisuusvirhe. Yritä uudelleen.");
        log_error("CSRF token verification failed on registration");
    } else {
        // Rate limiting
        $rate_check = check_rate_limit('register', MAX_REGISTER_ATTEMPTS, RATE_LIMIT_WINDOW);
        
        if (is_array($rate_check) && isset($rate_check['blocked'])) {
            handle_error("Liikaa rekisteröintiyrityksiä. Odota {$rate_check['wait_minutes']} minuuttia.");
        } else {
            // Puhdista ja validoi syötteet
            $username = sanitize_string($_POST['username'] ?? '');
            $email = sanitize_string($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validointi
            $errors = [];
            
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $errors[] = "Kaikki kentät ovat pakollisia!";
            }
            
            if (!validate_username($username)) {
                $errors[] = "Käyttäjätunnus saa sisältää vain kirjaimia, numeroita, alaviivoja ja väliviivoja (3-50 merkkiä)";
            }
            
            if (!validate_email($email)) {
                $errors[] = "Virheellinen sähköpostiosoite!";
            }
            
            if ($password !== $confirm_password) {
                $errors[] = "Salasanat eivät täsmää!";
            }
            
            $password_errors = [];
            if (!validate_password($password, $password_errors)) {
                $errors = array_merge($errors, $password_errors);
            }
            
            if (!empty($errors)) {
                $message = implode("<br>", $errors);
                $message_type = "error";
            } else {
                try {
                    // Tarkista onko käyttäjätunnus tai email jo käytössä
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    
                    if ($stmt->fetch()) {
                        handle_error("Käyttäjätunnus tai sähköpostiosoite on jo käytössä!");
                    } else {
                        // Hashataan salasana ja luodaan käyttäjä
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $email, $password_hash]);
                        
                        // Nollaa rate limit
                        reset_rate_limit('register');
                        
                        // Loki onnistunut rekisteröinti
                        log_security_event('register_success', [
                            'username' => $username,
                            'email' => $email
                        ]);
                        
                        $message = "Rekisteröinti onnistui! Voit nyt kirjautua sisään.";
                        $message_type = "success";
                    }
                } catch (PDOException $e) {
                    handle_db_error($e, "Rekisteröinti epäonnistui. Yritä myöhemmin uudelleen.");
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekisteröidy - <?php echo escape(SITE_NAME); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <!-- Animoidut tausta-blobit -->
    <div class="blob-2"></div>
    <div class="blob-3"></div>
    
    <header>
        <nav>
            <h1>
                <img src="../assets/images/logo.png" alt="<?php echo escape(SITE_NAME); ?>">
                <?php echo escape(SITE_NAME); ?>
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
        <h1>Rekisteröidy festivaalille</h1>
        <p>Luo käyttäjätunnus ja osta liput Thunderstorm Rock Festivalille!</p>
        
        <?php if (!empty($message)): ?>
        <div class="message <?php echo escape($message_type); ?>">
            <?php echo $message; /* Sisältää jo HTML, ei escapoida */ ?>
        </div>
        <?php endif; ?>
        
        <form method="post">
            <?php echo csrf_field(); ?>
            
            <label for="username">Käyttäjätunnus:</label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   required 
                   maxlength="50"
                   pattern="[a-zA-Z0-9_-]{3,50}"
                   title="3-50 merkkiä: kirjaimet, numerot, alaviiva, väliviiva"
                   value="<?php echo escape($_POST['username'] ?? ''); ?>">
            
            <label for="email">Sähköpostiosoite:</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   required 
                   maxlength="100"
                   value="<?php echo escape($_POST['email'] ?? ''); ?>">
            
            <label for="password">Salasana (vähintään 6 merkkiä):</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   required 
                   minlength="6">
            
            <label for="confirm_password">Vahvista salasana:</label>
            <input type="password" 
                   id="confirm_password" 
                   name="confirm_password" 
                   required 
                   minlength="6">
            
            <?php
            $remaining = get_remaining_attempts('register', MAX_REGISTER_ATTEMPTS);
            if ($remaining <= 2 && $remaining > 0):
            ?>
            <p style="color: #ffa726;">
                <strong>Varoitus:</strong> Sinulla on <?php echo $remaining; ?> rekisteröintiyritystä jäljellä.
            </p>
            <?php endif; ?>
            
            <button type="submit">Rekisteröidy</button>
        </form>
        
        <p><a href="login.php">Onko sinulla jo käyttäjätunnus? Kirjaudu tästä.</a></p>
        
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
                    <p><strong><?php echo escape(EVENT_NAME); ?></strong></p>
                    <p><?php echo escape(EVENT_DATE); ?></p>
                    <p><?php echo escape(EVENT_LOCATION); ?></p>
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
                <p>&copy; 2025 <?php echo escape(SITE_NAME); ?>. Kaikki oikeudet pidätetään.</p>
            </div>
        </footer>
    </main>
</body>
</html>