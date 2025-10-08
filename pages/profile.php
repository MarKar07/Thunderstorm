<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
secure_session_start();
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';
require_once '../config/database.php';

// Pakota kirjautuminen
require_login();

$message = "";
$message_type = "";
$user_id = $_SESSION['user_id'];

// Haetaan käyttäjän profiilitiedot
try {
    $stmt = $pdo->prepare("
        SELECT u.username, u.email, u.role, u.created_at,
               p.first_name, p.last_name, p.phone, p.age, p.city
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Käyttäjää ei löydy - kirjaa ulos
        destroy_session();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    handle_db_error($e, "Profiilin lataus epäonnistui.");
}

// Tarkistetaan onko käyttäjä ilmoittautunut tapahtumaan
$is_registered = false;
$registration_info = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM event_registrations WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $registration_info = $stmt->fetch();
    $is_registered = !empty($registration_info);
} catch (PDOException $e) {
    log_error("Failed to check registration status", ['error' => $e->getMessage()]);
}

// Käsitellään lomakkeet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tarkista CSRF kaikille POST-pyynnöille
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        handle_error("Turvallisuusvirhe. Yritä uudelleen.");
        log_error("CSRF token verification failed on profile");
    } else {
        $post_keys = array_keys($_POST);
        
        // PROFIILIN PÄIVITYS
        if (in_array('update_profile', $post_keys)) {
            $first_name = sanitize_string($_POST['first_name'] ?? '');
            $last_name = sanitize_string($_POST['last_name'] ?? '');
            $phone = sanitize_string($_POST['phone'] ?? '');
            $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
            $city = sanitize_string($_POST['city'] ?? '');
            
            // Validointi
            if (!empty($phone) && !preg_match('/^[0-9+\s\-()]{7,20}$/', $phone)) {
                handle_error("Virheellinen puhelinnumero!");
            } elseif (!empty($age) && ($age < 1 || $age > 120)) {
                handle_error("Virheellinen ikä!");
            } else {
                try {
                    // Tarkista onko profiili olemassa
                    $stmt = $pdo->prepare("SELECT user_id FROM profiles WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $profile_exists = $stmt->fetch();
                    
                    if ($profile_exists) {
                        // Päivitä olemassa oleva profiili
                        $stmt = $pdo->prepare("
                            UPDATE profiles 
                            SET first_name = ?, last_name = ?, phone = ?, age = ?, city = ?
                            WHERE user_id = ?
                        ");
                        $stmt->execute([$first_name, $last_name, $phone, $age, $city, $user_id]);
                    } else {
                        // Luo uusi profiili
                        $stmt = $pdo->prepare("
                            INSERT INTO profiles (user_id, first_name, last_name, phone, age, city)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$user_id, $first_name, $last_name, $phone, $age, $city]);
                    }
                    
                    log_security_event('profile_updated', ['user_id' => $user_id]);
                    set_message("Profiilitiedot päivitetty onnistuneesti!", "success");
                    
                    // Päivitä $user-muuttuja
                    $stmt = $pdo->prepare("
                        SELECT u.username, u.email, u.role, u.created_at,
                               p.first_name, p.last_name, p.phone, p.age, p.city
                        FROM users u
                        LEFT JOIN profiles p ON u.id = p.user_id
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                } catch (PDOException $e) {
                    handle_db_error($e, "Profiilin päivitys epäonnistui.");
                }
            }
        }
        
        // SALASANAN VAIHTO
        if (in_array('change_password', $post_keys)) {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                handle_error("Kaikki salasanakentät ovat pakollisia!");
            } elseif ($new_password !== $confirm_password) {
                handle_error("Uudet salasanat eivät täsmää!");
            } else {
                $password_errors = [];
                if (!validate_password($new_password, $password_errors)) {
                    $message = implode("<br>", $password_errors);
                    $message_type = "error";
                } else {
                    try {
                        // Tarkista nykyinen salasana
                        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user_data = $stmt->fetch();
                        
                        if ($user_data && password_verify($current_password, $user_data['password'])) {
                            // Vaihda salasana
                            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->execute([$new_password_hash, $user_id]);
                            
                            log_security_event('password_changed', ['user_id' => $user_id]);
                            set_message("Salasana vaihdettu onnistuneesti!", "success");
                        } else {
                            handle_error("Nykyinen salasana on väärin!");
                        }
                    } catch (PDOException $e) {
                        handle_db_error($e, "Salasanan vaihto epäonnistui.");
                    }
                }
            }
        }
        
        // TAPAHTUMAILMOITTAUTUMINEN
        if (in_array('register_event', $post_keys)) {
            $ticket_type = sanitize_string($_POST['ticket_type'] ?? 'day');
            
            // Validoi lipputyyppi
            if (!in_array($ticket_type, ['day', 'weekend', 'vip'])) {
                handle_error("Virheellinen lipputyyppi!");
            } else {
                try {
                    if ($is_registered) {
                        handle_error("Olet jo ilmoittautunut tapahtumaan!");
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO event_registrations (user_id, ticket_type) VALUES (?, ?)");
                        $stmt->execute([$user_id, $ticket_type]);
                        
                        log_security_event('event_registration', [
                            'user_id' => $user_id,
                            'ticket_type' => $ticket_type
                        ]);
                        
                        set_message("Ilmoittautuminen onnistui! Tervetuloa Thunderstorm Rock Festivalille!", "success");
                        $is_registered = true;
                        
                        // Päivitetään rekisteröintitiedot
                        $stmt = $pdo->prepare("SELECT * FROM event_registrations WHERE user_id = ? AND status = 'active'");
                        $stmt->execute([$user_id]);
                        $registration_info = $stmt->fetch();
                    }
                } catch (PDOException $e) {
                    handle_db_error($e, "Ilmoittautuminen epäonnistui.");
                }
            }
        }
        
        // ILMOITTAUTUMISEN PERUUTUS
        if (in_array('cancel_registration', $post_keys)) {
            try {
                $stmt = $pdo->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE user_id = ? AND status = 'active'");
                $stmt->execute([$user_id]);
                
                log_security_event('event_cancellation', ['user_id' => $user_id]);
                set_message("Ilmoittautuminen peruutettu.", "success");
                $is_registered = false;
                $registration_info = null;
            } catch (PDOException $e) {
                handle_db_error($e, "Peruutus epäonnistui.");
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
    <title>Profiili - <?php echo escape(SITE_NAME); ?></title>
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
                <li><a href="profile.php">Profiili</a></li>
                <?php if ($user['role'] == 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Kirjaudu ulos</a></li>
            </ul>
            <div class="mobile-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="hero-profile">
            <h1>Tervetuloa, <?php echo escape($user['username']); ?>!</h1>
            <p class="hero-text">Oma festivaaliprofiilisi</p>
        </div>
        
        <!-- Viestit käyttäjälle -->
        <?php if (!empty($message)): ?>
        <div class="message <?php echo escape($message_type); ?>">
            <?php echo escape($message); ?>
        </div>
        <?php endif; ?>
        
        <!-- Käyttäjän perustiedot -->
        <section>
            <h2>Käyttäjätiedot</h2>
            <p><strong>Käyttäjätunnus:</strong> <?php echo escape($user['username']); ?></p>
            <p><strong>Sähköposti:</strong> <?php echo escape($user['email']); ?></p>
            <p><strong>Rooli:</strong> <?php echo escape($user['role'] == 'admin' ? 'Ylläpitäjä' : 'Käyttäjä'); ?></p>
            <p><strong>Rekisteröitynyt:</strong> <?php echo escape(date('d.m.Y', strtotime($user['created_at']))); ?></p>
        </section>
        
        <!-- PROFIILIN MUOKKAUS -->
        <section>
            <h2>Muokkaa profiilia</h2>
            
            <form method="post">
                <?php echo csrf_field(); ?>
                
                <h3>Henkilötiedot</h3>
                
                <label for="first_name">Etunimi:</label>
                <input type="text" 
                       id="first_name" 
                       name="first_name" 
                       maxlength="50"
                       value="<?php echo escape($user['first_name'] ?? ''); ?>">
                
                <label for="last_name">Sukunimi:</label>
                <input type="text" 
                       id="last_name" 
                       name="last_name" 
                       maxlength="50"
                       value="<?php echo escape($user['last_name'] ?? ''); ?>">
                
                <label for="phone">Puhelinnumero:</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       maxlength="20"
                       pattern="[0-9+\s\-()]{7,20}"
                       title="7-20 merkkiä: numerot, +, välilyönti, -, ()"
                       value="<?php echo escape($user['phone'] ?? ''); ?>">
                
                <label for="age">Ikä:</label>
                <input type="number" 
                       id="age" 
                       name="age" 
                       min="1" 
                       max="120"
                       value="<?php echo escape($user['age'] ?? ''); ?>">
                
                <label for="city">Kaupunki:</label>
                <input type="text" 
                       id="city" 
                       name="city" 
                       maxlength="50"
                       value="<?php echo escape($user['city'] ?? ''); ?>">
                
                <button type="submit" name="update_profile" value="1">Tallenna tiedot</button>
            </form>
        </section>

        <!-- SALASANAN VAIHTO -->
        <section>
            <h2>Vaihda salasana</h2>
            
            <form method="post">
                <?php echo csrf_field(); ?>
                
                <label for="current_password">Nykyinen salasana:</label>
                <input type="password" id="current_password" name="current_password" required>
                
                <label for="new_password">Uusi salasana (vähintään 6 merkkiä):</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                
                <label for="confirm_password">Vahvista uusi salasana:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                
                <button type="submit" name="change_password" value="1">Vaihda salasana</button>
            </form>
        </section>
        
        <!-- Tapahtumailmoittautuminen -->
        <section>
            <h2><?php echo escape(EVENT_NAME); ?></h2>
            
            <?php if ($is_registered): ?>
                <div class="registration-info">
                    <h3>Olet ilmoittautunut tapahtumaan!</h3>
                    <p><strong>Lipputyyppi:</strong> 
                        <?php 
                        $ticket_names = [
                            'day' => 'Päivälippu (35€)',
                            'weekend' => 'Viikonloppulippu (60€)',
                            'vip' => 'VIP-lippu (120€)'
                        ];
                        echo escape($ticket_names[$registration_info['ticket_type']] ?? 'Tuntematon');
                        ?>
                    </p>
                    <p><strong>Ilmoittautumispäivä:</strong> <?php echo escape(date('d.m.Y H:i', strtotime($registration_info['registration_date']))); ?></p>
                    
                    <form method="post" onsubmit="return confirm('Haluatko varmasti peruuttaa ilmoittautumisen?')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" name="cancel_registration" value="1">Peruuta ilmoittautuminen</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="registration-form">
                    <h3>Ilmoittaudu festivaalille!</h3>
                    <p><strong><?php echo escape(EVENT_DATE); ?></strong> | <?php echo escape(EVENT_LOCATION); ?></p>
                    
                    <form method="post">
                        <?php echo csrf_field(); ?>
                        
                        <h4>Valitse lipputyyppi:</h4>
                        <label>
                            <input type="radio" name="ticket_type" value="day" checked> 
                            Päivälippu (35€) - Pääsy yhtenä päivänä
                        </label><br>
                        <label>
                            <input type="radio" name="ticket_type" value="weekend"> 
                            Kahden päivän lippu (60€) - Pääsy molempina päivinä
                        </label><br>
                        <label>
                            <input type="radio" name="ticket_type" value="vip"> 
                            VIP-lippu (120€) - VIP-alue, backstage-pääsy
                        </label><br><br>
                        
                        <button type="submit" name="register_event" value="1">Ilmoittaudu festivaalille</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Yhteystiedot</h3>
                    <p><strong>Rock Events Finland Oy</strong></p>
                    <p>Musiikkikatu 15<br>33100 Tampere</p>
                    <p>info@thunderstormrock.fi</p>
                    <p>+358 40 123 4567</p>
                </div>

                <div class="footer-section">
                    <h3>Linkit</h3>
                    <ul>
                        <li><a href="../index.php">Etusivu</a></li>
                        <li><a href="event-info.php">Tapahtuma</a></li>
                        <li><a href="contact.php">Yhteystiedot</a></li>
                        <li><a href="login.php">Kirjaudu</a></li>
                        <li><a href="register.php">Rekisteröidy</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Tapahtuma</h3>
                    <p><strong><?php echo escape(EVENT_NAME); ?></strong></p>
                    <p><?php echo escape(EVENT_DATE); ?></p>
                    <p><?php echo escape(EVENT_LOCATION); ?></p>
                </div>

                <div class="footer-section">
                    <h3>Seuraa meitä</h3>
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