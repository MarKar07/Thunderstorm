<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
secure_session_start();  // ← TÄRKEÄ!
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
require_once '../includes/error_handler.php';
require_once '../config/database.php';

$is_logged_in = is_logged_in();
$is_admin = is_admin();

$message = "";
$message_type = "";

// PALAUTELOMAKKEEN KÄSITTELY
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    // CSRF-suojaus
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        handle_error("Turvallisuusvirhe. Yritä uudelleen.");
        log_error("CSRF token verification failed on contact form");
    } else {
        
        // Rate limiting
        $rate_check = check_rate_limit('contact', MAX_CONTACT_ATTEMPTS, RATE_LIMIT_WINDOW);
        
        if (is_array($rate_check) && isset($rate_check['blocked'])) {
            handle_error("Liikaa yrityksiä. Odota {$rate_check['wait_minutes']} minuuttia.");
        } else {
            // Puhdista syötteet
            $name = sanitize_string($_POST['name'] ?? '');
            $email = sanitize_string($_POST['email'] ?? '');
            $subject = sanitize_string($_POST['subject'] ?? '');
            $feedback_message = sanitize_string($_POST['message'] ?? '');
            
            // Validointi
            if (empty($name) || empty($email) || empty($subject) || empty($feedback_message)) {
                handle_error("Kaikki kentät ovat pakollisia!");
            } elseif (!validate_email($email)) {
                handle_error("Virheellinen sähköpostiosoite!");
            } elseif (strlen($name) > 100 || strlen($subject) > 200 || strlen($feedback_message) > 2000) {
                handle_error("Syötteet ovat liian pitkiä!");
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO feedback (name, email, subject, message) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $subject, $feedback_message]);
                    
                    // Nollaa rate limit onnistuneen lähetyksen jälkeen
                    reset_rate_limit('contact');
                    
                    // Loki palautelomakkeen lähetys
                    log_security_event('feedback_submitted', [
                        'name' => $name,
                        'email' => $email,
                        'subject' => $subject
                    ]);
                    
                    set_message("Kiitos palautteestasi! Olemme vastaanottaneet viestisi ja vastaamme mahdollisimman pian.", "success");
                    
                } catch (PDOException $e) {
                    handle_db_error($e, "Palautteen lähetys epäonnistui. Yritä myöhemmin uudelleen.");
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
    <title>Yhteystiedot - <?php echo escape(SITE_NAME); ?></title>
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
                <?php if ($is_logged_in): ?>
                    <li><a href="profile.php">Profiili</a></li>
                    <?php if ($is_admin): ?>
                    <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Kirjaudu ulos</a></li>
                <?php else: ?>
                    <li><a href="login.php">Kirjaudu</a></li>
                    <li><a href="register.php">Rekisteröidy</a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="hero-contact">
            <h1>Ota yhteyttä!</h1>
            <p class="hero-text">Olemme täällä auttamassa sinua</p>
        </div>
        
        <section>
            <h2>Järjestäjä</h2>
            <p><strong>Rock Events Finland Oy</strong></p>
            <p>Musiikkikatu 15<br>
            33100 Tampere<br>
            Finland</p>
        </section>

        <section>
            <h2>Yhteystiedot</h2>
            
            <div>
                <h3>Lipunmyynti ja ilmoittautuminen</h3>
                <p><strong>Sähköposti:</strong> <a href="mailto:liput@thunderstormrock.fi">liput@thunderstormrock.fi</a></p>
                <p><strong>Puhelin:</strong> <a href="tel:+358401234567">+358 40 123 4567</a></p>
                <p><strong>Aukioloajat:</strong> Ma-Pe 9:00-17:00</p>
            </div>

            <div>
                <h3>Artistiasiat</h3>
                <p><strong>Sähköposti:</strong> <a href="mailto:artistit@thunderstormrock.fi">artistit@thunderstormrock.fi</a></p>
                <p><strong>Puhelin:</strong> <a href="tel:+358401234568">+358 40 123 4568</a></p>
            </div>

            <div>
                <h3>Media ja lehdistö</h3>
                <p><strong>Sähköposti:</strong> <a href="mailto:media@thunderstormrock.fi">media@thunderstormrock.fi</a></p>
                <p><strong>Puhelin:</strong> <a href="tel:+358401234569">+358 40 123 4569</a></p>
            </div>

            <div>
                <h3>Myynti ja sponsorit</h3>
                <p><strong>Sähköposti:</strong> <a href="mailto:myynti@thunderstormrock.fi">myynti@thunderstormrock.fi</a></p>
                <p><strong>Puhelin:</strong> <a href="tel:+358401234570">+358 40 123 4570</a></p>
            </div>

            <div>
                <h3>Yleiset tiedustelut</h3>
                <p><strong>Sähköposti:</strong> <a href="mailto:info@thunderstormrock.fi">info@thunderstormrock.fi</a></p>
                <p><strong>Puhelin:</strong> <a href="tel:+358401234571">+358 40 123 4571</a></p>
            </div>
        </section>

        <section>
            <h2>Asiakaspalvelu</h2>
            <p><strong>Asiakaspalvelumme on avoinna:</strong></p>
            <ul>
                <li><strong>Maanantai-Perjantai:</strong> 9:00-17:00</li>
                <li><strong>Lauantai:</strong> 10:00-15:00</li>
                <li><strong>Sunnuntai:</strong> Suljettu</li>
            </ul>
            
            <p><strong>Festivaalipäivinä 25.-26.7.2025:</strong></p>
            <ul>
                <li>Info-piste festivaalialueella avoinna 14:00-01:00</li>
                <li>Hätätilanteissa: <strong>112</strong></li>
                <li><strong>Festivaalin turvallisuus: <strong>+358 40 123 1234</strong></li>
            </ul>
        </section>

        <section>
            <h2>Sosiaalinen media</h2>
            <p>Seuraa meitä ja saat tuoreimmat uutiset festivaalista!</p>
            
            <ul>
                <li><strong>Facebook:</strong> <a href="#">@ThunderstormRockFestival</a></li>
                <li><strong>Instagram:</strong> <a href="#">@thunderstormrock</a></li>
                <li><strong>Twitter:</strong> <a href="#">@TRockFest</a></li>
                <li><strong>YouTube:</strong> <a href="#">Thunderstorm Rock Official</a></li>
                <li><strong>TikTok:</strong> <a href="#">@thunderstormrock</a></li>
            </ul>

            <p><strong>Virallinen hashtag:</strong> #ThunderstormRock2025</p>
        </section>

        <!-- PALAUTELOMAKE -->
        <section>
            <h2>Lähetä palautetta</h2>
            <p>Haluamme kuulla sinusta! Lähetä meille palautetta, kysymyksiä tai ehdotuksia.</p>
            
            <?php if (!empty($message)): ?>
            <div class="message <?php echo escape($message_type); ?>">
                <?php echo escape($message); ?>
            </div>
            <?php endif; ?>
            
            <form method="post">
                <?php echo csrf_field(); ?>
                
                <label for="name">Nimi:</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       required 
                       maxlength="100"
                       value="<?php echo escape($_POST['name'] ?? ''); ?>">
                
                <label for="email">Sähköposti:</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       maxlength="100"
                       value="<?php echo escape($_POST['email'] ?? ''); ?>">
                
                <label for="subject">Aihe:</label>
                <select id="subject" name="subject" required>
                    <option value="">Valitse aihe...</option>
                    <option value="Yleinen palaute">Yleinen palaute</option>
                    <option value="Liput ja hinnoittelu">Liput ja hinnoittelu</option>
                    <option value="Artistit ja ohjelma">Artistit ja ohjelma</option>
                    <option value="Festivaalialue">Festivaalialue ja palvelut</option>
                    <option value="Tekninen ongelma">Tekninen ongelma</option>
                    <option value="Muu">Muu</option>
                </select>
                
                <label for="message">Viesti:</label>
                <textarea id="message" 
                          name="message" 
                          rows="6" 
                          required 
                          maxlength="2000"><?php echo escape($_POST['message'] ?? ''); ?></textarea>
                
                <?php
                $remaining = get_remaining_attempts('contact', MAX_CONTACT_ATTEMPTS);
                if ($remaining <= 2 && $remaining > 0):
                ?>
                <p style="color: #ffa726;">
                    <strong>Varoitus:</strong> Sinulla on <?php echo $remaining; ?> lähetysyritystä jäljellä.
                </p>
                <?php endif; ?>
                
                <button type="submit" name="submit_feedback" value="1">Lähetä palaute</button>
            </form>
        </section>

        <section>
            <h2>Usein kysytyt kysymykset</h2>
            
            <h3>Miten voin peruuttaa lipun?</h3>
            <p>Lippuja voi peruuttaa 14 päivää ennen tapahtumaa. Voit peruuttaa osallistumisen profiilistasi.</p>
            
            <h3>Voiko alueelle tuoda omaa ruokaa?</h3>
            <p>Ei. Alueella on runsaasti ravintola-palveluita kaikille makumieltymyksille.</p>
            
            <h3>Onko alueella pankkiautomaatti?</h3>
            <p>Kyllä. Festivaalialueella on kaksi pankkiautomaattia.</p>
            
            <h3>Miten pääsen backstagelle?</h3>
            <p>VIP-lipunhaltijoilla on backstage-pääsy.</p>
            
            <h3>Voinko tuoda kameran?</h3>
            <p>Normaalit kamerat ja puhelinten kamerat ovat sallittuja. Ammattikamerat kielletty.</p>
        </section>

        <section>
            <h2>Hätätilanteet</h2>
            <p><strong>Yleinen hätänumero:</strong> <strong>112</strong></p>
            <p><strong>Ensiapu festivaalilla:</strong> Info-pisteeltä ohjeistus</p>
            <p><strong>Kadonneet esineet:</strong> Löytötavarat info-pisteessä</p>
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
                    <p><?php echo escape(EVENT_DATE); ?></strong></p>
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