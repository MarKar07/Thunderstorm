<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
secure_session_start();
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
require_once '../includes/error_handler.php';
require_once '../includes/mailer.php';
require_once '../config/database.php';

$message = "";
$message_type = "";

// Käsittele lomake
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF-suojaus
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        handle_error("Turvallisuusvirhe. Yritä uudelleen.");
    } else {
        // Rate limiting (max 3 yritystä / 15min)
        $rate_check = check_rate_limit('forgot_password', 3, 900);
        
        if (is_array($rate_check) && isset($rate_check['blocked'])) {
            handle_error("Liikaa yrityksiä. Odota {$rate_check['wait_minutes']} minuuttia.");
        } else {
            $email = sanitize_string($_POST['email'] ?? '');
            
            if (empty($email)) {
                handle_error("Sähköpostiosoite on pakollinen!");
            } elseif (!validate_email($email)) {
                handle_error("Virheellinen sähköpostiosoite!");
            } else {
                try {
                    // Hae käyttäjä
                    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        // Luo token
                        $token = bin2hex(random_bytes(32));
                        $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 tunti
                        
                        // Tallenna token tietokantaan
                        $stmt = $pdo->prepare("
                            INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$user['id'], $token, $expires_at]);
                        
                        // Lähetä sähköposti
                        $email_sent = send_password_reset_email($user['email'], $user['username'], $token);
                        
                        if ($email_sent) {
                            log_security_event('password_reset_requested', [
                                'user_id' => $user['id'],
                                'email' => $user['email']
                            ]);
                            
                            set_message("Salasanan palautuslinkki lähetetty sähköpostiisi!", "success");
                        } else {
                            handle_error("Sähköpostin lähetys epäonnistui. Yritä myöhemmin uudelleen.");
                        }
                    } else {
                        // Turvallisuussyistä näytetään sama viesti vaikka käyttäjää ei löytyisi
                        set_message("Jos sähköpostiosoite on rekisteröity, saat palautuslinkin.", "success");
                    }
                    
                } catch (PDOException $e) {
                    handle_db_error($e, "Salasanan palautus epäonnistui.");
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
    <title>Unohdin salasanan - <?php echo escape(SITE_NAME); ?></title>
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
        <h1>Unohdin salasanan</h1>
        <p>Anna sähköpostiosoitteesi, niin lähetämme linkin salasanan vaihtamiseen.</p>
        
        <?php if (!empty($message)): ?>
        <div class="message <?php echo escape($message_type); ?>">
            <?php echo escape($message); ?>
        </div>
        <?php endif; ?>
        
        <form method="post">
            <?php echo csrf_field(); ?>
            
            <label for="email">Sähköpostiosoite:</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   required 
                   maxlength="100"
                   placeholder="esimerkki@email.fi"
                   value="<?php echo escape($_POST['email'] ?? ''); ?>">
            
            <?php
            $remaining = get_remaining_attempts('forgot_password', 3);
            if ($remaining <= 1 && $remaining > 0):
            ?>
            <p style="color: #ffa726;">
                <strong>Varoitus:</strong> Sinulla on <?php echo $remaining; ?> yritystä jäljellä.
            </p>
            <?php endif; ?>
            
            <button type="submit">Lähetä palautuslinkki</button>
        </form>
        
        <p><a href="login.php">⬅️ Takaisin kirjautumiseen</a></p>
        
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