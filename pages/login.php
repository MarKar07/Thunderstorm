<?php
// ========================================
// TURVALLISUUSPARANNUKSET LISÄTTY!
// ========================================

// 1. Lataa config
require_once '../config/config.php';

// 2. Käynnistä turvallinen sessio
require_once '../includes/session.php';
secure_session_start();

// 3. Lataa turvallisuusfunktiot
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
require_once '../includes/error_handler.php';

// 4. Lataa tietokanta
require_once '../config/database.php';

$message = "";
$message_type = "";

// Jos on timeout-viesti, näytä se
if (isset($_SESSION['timeout_message'])) {
    $message = $_SESSION['timeout_message'];
    $message_type = "error";
    unset($_SESSION['timeout_message']);
}

// Käsitellään kirjautuminen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. CSRF-suojaus
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        handle_error("Turvallisuusvirhe. Yritä uudelleen.");
        log_error("CSRF token verification failed on login", [
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
    } else {
        // 2. Rate limiting
        $rate_check = check_rate_limit('login', MAX_LOGIN_ATTEMPTS, RATE_LIMIT_WINDOW);
        
        if (is_array($rate_check) && isset($rate_check['blocked'])) {
            handle_error("Liikaa kirjautumisyrityksiä. Odota {$rate_check['wait_minutes']} minuuttia.");
            log_error("Rate limit exceeded for login", [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'wait_minutes' => $rate_check['wait_minutes']
            ]);
        } else {
            // 3. Validointi ja puhdistus
            $username = sanitize_string($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                handle_error("Käyttäjätunnus ja salasana ovat pakollisia!");
            } else {
                try {
                    // Haetaan käyttäjä tietokannasta
                    $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $username]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($password, $user['password'])) {
                        // ONNISTUNUT KIRJAUTUMINEN
                        
                        // Nollaa rate limit
                        reset_rate_limit('login');
                        
                        // Uudista session ID (estää session fixation)
                        regenerate_session_on_login();
                        
                        // Tallenna käyttäjätiedot
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Loki onnistunut kirjautuminen
                        log_security_event('login_success', [
                            'user_id' => $user['id'],
                            'username' => $user['username'],
                            'role' => $user['role']
                        ]);
                        
                        // Ohjaa käyttäjä roolin mukaan
                        if ($user['role'] == 'admin') {
                            header("Location: admin.php");
                        } else {
                            header("Location: profile.php");
                        }
                        exit();
                    } else {
                        // EPÄONNISTUNUT KIRJAUTUMINEN
                        handle_error("Väärä käyttäjätunnus tai salasana!");
                        
                        // Loki epäonnistunut yritys
                        log_security_event('login_failed', [
                            'username_attempt' => $username,
                            'remaining_attempts' => get_remaining_attempts('login', MAX_LOGIN_ATTEMPTS)
                        ]);
                    }
                } catch (PDOException $e) {
                    handle_db_error($e, "Kirjautuminen epäonnistui. Yritä myöhemmin uudelleen.");
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
    <title>Kirjaudu - <?php echo escape(SITE_NAME); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
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
        <h1>Kirjaudu sisään</h1>
        <p>Kirjaudu sisään ostaaksesi liput ja hallitaksesi profiiliasi.</p>
        
        <!-- Viestit käyttäjälle (XSS-suojattu) -->
        <?php if (!empty($message)): ?>
        <div class="message <?php echo escape($message_type); ?>">
            <?php echo escape($message); ?>
        </div>
        <?php endif; ?>
        
        <form method="post">
            <!-- CSRF-suojaus -->
            <?php echo csrf_field(); ?>
            
            <label for="username">Käyttäjätunnus tai sähköposti:</label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   maxlength="100"
                   required 
                   value="<?php echo escape($_POST['username'] ?? ''); ?>">
            
            <label for="password">Salasana:</label>
            <input type="password" id="password" name="password" required>
            
            <?php
            // Näytä jäljellä olevat yritykset jos lähellä rajaa
            $remaining = get_remaining_attempts('login', MAX_LOGIN_ATTEMPTS);
            if ($remaining <= 2 && $remaining > 0):
            ?>
            <p style="color: #ffa726;">
                <strong>Varoitus:</strong> Sinulla on <?php echo $remaining; ?> kirjautumisyritystä jäljellä.
            </p>
            <?php endif; ?>
            
            <button type="submit">Kirjaudu</button>
        </form>
        
        <p><a href="register.php">Eikö sinulla ole vielä käyttäjätunnusta? Rekisteröidy tästä.</a></p>
        
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