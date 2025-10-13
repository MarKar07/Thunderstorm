<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
secure_session_start();
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';
require_once '../config/database.php';

$message = "";
$message_type = "";
$token_valid = false;
$user_id = null;

// Tarkista token
if (isset($_GET['token'])) {
    $token = sanitize_string($_GET['token']);
    
    try {
        // Hae token tietokannasta
        $stmt = $pdo->prepare("
            SELECT prt.id, prt.user_id, prt.expires_at, prt.used, u.username, u.email
            FROM password_reset_tokens prt
            JOIN users u ON prt.user_id = u.id
            WHERE prt.token = ? AND prt.used = 0
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();
        
        if ($token_data) {
            // Tarkista onko vanhentunut
            if (strtotime($token_data['expires_at']) > time()) {
                $token_valid = true;
                $user_id = $token_data['user_id'];
            } else {
                $message = "Linkki on vanhentunut. Pyydä uusi palautuslinkki.";
                $message_type = "error";
            }
        } else {
            $message = "Virheellinen tai jo käytetty linkki.";
            $message_type = "error";
        }
        
    } catch (PDOException $e) {
        handle_db_error($e, "Salasanan palautus epäonnistui.");
    }
} else {
    $message = "Puuttuva token. Pyydä uusi palautuslinkki.";
    $message_type = "error";
}

// Käsittele salasanan vaihto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    // CSRF-suojaus
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        handle_error("Turvallisuusvirhe. Yritä uudelleen.");
    } else {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($new_password) || empty($confirm_password)) {
            handle_error("Molemmat kentät ovat pakollisia!");
        } elseif ($new_password !== $confirm_password) {
            handle_error("Salasanat eivät täsmää!");
        } else {
            $password_errors = [];
            if (!validate_password($new_password, $password_errors)) {
                $message = implode("<br>", $password_errors);
                $message_type = "error";
            } else {
                try {
                    // Päivitä salasana
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$password_hash, $user_id]);
                    
                    // Merkitse token käytetyksi
                    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
                    $stmt->execute([$token]);
                    
                    log_security_event('password_reset_completed', [
                        'user_id' => $user_id
                    ]);
                    
                    set_message("Salasana vaihdettu onnistuneesti! Voit nyt kirjautua sisään.", "success");
                    $token_valid = false; // Estetään lomakkeen näyttäminen
                    
                } catch (PDOException $e) {
                    handle_db_error($e, "Salasanan vaihto epäonnistui.");
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
    <title>Vaihda salasana - <?php echo escape(SITE_NAME); ?></title>
    <link rel="stylesheet" href="https://geronimo.okol.org/~markar/Tapahtumasivut/assets/css/style.css">
    <script src="https://geronimo.okol.org/~markar/Tapahtumasivut/assets/js/script.js"></script>
</head>
<body>
    <!-- Animoidut tausta-blobit -->
    <div class="blob-2"></div>
    <div class="blob-3"></div>
    
    <header>
        <nav>
            <h1>
                <img src="https://geronimo.okol.org/~markar/Tapahtumasivut/assets/images/logo.png" alt="<?php echo escape(SITE_NAME); ?>">
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
        <h1>Vaihda salasana</h1>
        
        <?php if (!empty($message)): ?>
        <div class="message <?php echo escape($message_type); ?>">
            <?php echo $message; /* Sisältää HTML */ ?>
        </div>
        <?php endif; ?>
        
        <?php if ($token_valid): ?>
            <p>Anna uusi salasana tilillesi: <strong><?php echo escape($token_data['username']); ?></strong></p>
            
            <form method="post">
                <?php echo csrf_field(); ?>
                
                <label for="new_password">Uusi salasana (vähintään 6 merkkiä):</label>
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       required 
                       minlength="6">
                
                <label for="confirm_password">Vahvista uusi salasana:</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       required 
                       minlength="6">
                
                <button type="submit">Vaihda salasana</button>
            </form>
        <?php else: ?>
            <p><a href="forgot_password.php" class="btn btn-primary">Pyydä uusi palautuslinkki</a></p>
            <p><a href="login.php">⬅️ Takaisin kirjautumiseen</a></p>
        <?php endif; ?>
        
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