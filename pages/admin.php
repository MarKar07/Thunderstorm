<?php
// ========================================
// TURVALLISUUSPARANNUKSET LISÄTTY!
// ========================================

// 1. Lataa config
require_once '../config/config.php';

// 2. Käynnistä turvallinen sessio
require_once '../includes/session.php';

// 3. Lataa turvallisuusfunktiot
require_once '../includes/security.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
require_once '../includes/error_handler.php';

// 4. Lataa tietokanta
session_start();
include '../config/database.php';

// Tarkistetaan että käyttäjä on kirjautunut ja admin
if (!array_key_exists('user_id', $_SESSION) || empty($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

// Käsitellään admin-toiminnot
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_keys = array_keys($_POST);
    
    // Käyttäjän poistaminen
    if (in_array('delete_user', $post_keys)) {
        $delete_user_id = array_key_exists('user_id', $_POST) ? (int)$_POST['user_id'] : 0;
        
        if ($delete_user_id > 0 && $delete_user_id != $_SESSION['user_id']) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$delete_user_id]);
                
                $message = "Käyttäjä poistettu onnistuneesti.";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Käyttäjän poisto epäonnistui.";
                $message_type = "error";
            }
        } else {
            $message = "Et voi poistaa omaa käyttäjätiliäsi.";
            $message_type = "error";
        }
    }
    
    // Tapahtumailmoittautumisen poistaminen
    if (in_array('remove_registration', $post_keys)) {
        $registration_id = array_key_exists('registration_id', $_POST) ? (int)$_POST['registration_id'] : 0;
        
        if ($registration_id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$registration_id]);
                
                $message = "Tapahtumailmoittautuminen poistettu.";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Ilmoittautumisen poisto epäonnistui.";
                $message_type = "error";
            }
        }
    }
    
    // Palautteen tilan muuttaminen
    if (in_array('mark_feedback', $post_keys)) {
        $feedback_id = array_key_exists('feedback_id', $_POST) ? (int)$_POST['feedback_id'] : 0;
        $new_status = array_key_exists('status', $_POST) ? $_POST['status'] : 'read';
        
        if ($feedback_id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $feedback_id]);
                
                $message = "Palautteen tila päivitetty.";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Päivitys epäonnistui.";
                $message_type = "error";
            }
        }
    }
    
    // Palautteen poistaminen
    if (in_array('delete_feedback', $post_keys)) {
        $feedback_id = array_key_exists('feedback_id', $_POST) ? (int)$_POST['feedback_id'] : 0;
        
        if ($feedback_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
                $stmt->execute([$feedback_id]);
                
                $message = "Palaute poistettu.";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "Poisto epäonnistui.";
                $message_type = "error";
            }
        }
    }
}

// Haetaan kaikki käyttäjät
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.role, u.created_at, u.is_active,
               COUNT(er.id) as registration_count
        FROM users u
        LEFT JOIN event_registrations er ON u.id = er.user_id AND er.status = 'active'
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $message = "Käyttäjien lataus epäonnistui.";
    $message_type = "error";
}

// Haetaan tapahtumailmoittautumiset
try {
    $stmt = $pdo->prepare("
        SELECT er.id, er.registration_date, er.ticket_type, er.status,
               u.id as user_id, u.username, u.email
        FROM event_registrations er
        JOIN users u ON er.user_id = u.id
        WHERE er.status = 'active'
        ORDER BY er.registration_date DESC
    ");
    $stmt->execute();
    $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $registrations = [];
}

// Haetaan palautteet
try {
    $stmt = $pdo->prepare("
        SELECT id, name, email, subject, message, created_at, status
        FROM feedback
        ORDER BY 
            CASE status
                WHEN 'new' THEN 1
                WHEN 'read' THEN 2
                WHEN 'resolved' THEN 3
            END,
            created_at DESC
    ");
    $stmt->execute();
    $feedbacks = $stmt->fetchAll();
} catch (PDOException $e) {
    $feedbacks = [];
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-hallinta - Thunderstorm Rock Festival</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <!-- Animoidut tausta-blobit -->
    <div class="blob-2"></div>
    <div class="blob-3"></div>
    <!-- Muu sisältö jatkuu normaalisti... -->
    <header>
        <nav>
            <h1>
                <img src="../assets/images/logo.png" alt="Thunderstorm Rock Festival">
                Thunderstorm Rock Festival - Admin
            </h1>
            <ul>
                <li><a href="../index.php">Etusivu</a></li>
                <li><a href="event-info.php">Tapahtuma</a></li>
                <li><a href="contact.php">Yhteystiedot</a></li>
                <li><a href="profile.php">Profiili</a></li>
                <li><a href="admin.php">Admin</a></li>
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
        <h1>Admin-hallintapaneeli</h1>
        <p>Tervetuloa <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        
        <!-- Viestit -->
        <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <!-- Tilastot -->
        <section>
            <h2>Yhteenveto</h2>
            <ul>
                <li><strong>Käyttäjiä yhteensä:</strong> <?php echo count($users); ?></li>
                <li><strong>Tapahtumaan ilmoittautuneita:</strong> <?php echo count($registrations); ?></li>
                <li><strong>Palautteita yhteensä:</strong> <?php echo count($feedbacks); ?></li>
                <li><strong>Uusia palautteita:</strong> <?php echo count(array_filter($feedbacks, function($f) { return $f['status'] == 'new'; })); ?></li>
                <li><strong>VIP-lippuja:</strong> <?php echo count(array_filter($registrations, function($r) { return $r['ticket_type'] == 'vip'; })); ?></li>
                <li><strong>Kahden päivän lippuja:</strong> <?php echo count(array_filter($registrations, function($r) { return $r['ticket_type'] == 'weekend'; })); ?></li>
                <li><strong>Päivälippuja:</strong> <?php echo count(array_filter($registrations, function($r) { return $r['ticket_type'] == 'day'; })); ?></li>
            </ul>
        </section>
        
        <!-- Palautteet -->
        <section>
            <h2>Palautteet</h2>
            
            <?php if (empty($feedbacks)): ?>
                <p>Ei palautteita.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Aika</th>
                            <th>Nimi</th>
                            <th>Sähköposti</th>
                            <th>Aihe</th>
                            <th>Viesti</th>
                            <th>Tila</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $fb): ?>
                        <tr style="<?php echo $fb['status'] == 'new' ? 'background-color: rgba(255, 107, 53, 0.1);' : ''; ?>">
                            <td data-label="Aika"><?php echo date('d.m.Y H:i', strtotime($fb['created_at'])); ?></td>
                            <td data-label="Nimi"><?php echo htmlspecialchars($fb['name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($fb['email']); ?></td>
                            <td data-label="Aihe"><strong><?php echo htmlspecialchars($fb['subject']); ?></strong></td>
                            <td data-label="Viesti" style="max-width: 300px;"><?php echo nl2br(htmlspecialchars($fb['message'])); ?></td>
                            <td data-label="Tila">
                                <?php 
                                switch($fb['status']) {
                                    case 'new': echo 'Uusi'; break;
                                    case 'read': echo 'Luettu'; break;
                                    case 'resolved': echo 'Ratkaistu'; break;
                                }
                                ?>
                            </td>
                            <td data-label="Toiminnot">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="">Vaihda tila...</option>
                                        <option value="new">Uusi</option>
                                        <option value="read">Luettu</option>
                                        <option value="resolved">Ratkaistu</option>
                                    </select>
                                    <input type="hidden" name="mark_feedback" value="1">
                                </form>
                                <form method="post" style="display: inline; margin-left: 5px;">
                                    <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                                    <button type="submit" name="delete_feedback" value="1" onclick="return confirm('Haluatko varmasti poistaa palautteen?')">Poista</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        
        <!-- Käyttäjähallinta -->
        <section>
            <h2>Käyttäjähallinta</h2>
            
            <?php if (empty($users)): ?>
                <p>Ei käyttäjiä.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Käyttäjätunnus</th>
                            <th>Sähköposti</th>
                            <th>Rooli</th>
                            <th>Rekisteröitynyt</th>
                            <th>Ilmoittautunut</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="ID"><?php echo $user['id']; ?></td>
                            <td data-label="Käyttäjä"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td data-label="Rooli"><?php echo $user['role'] == 'admin' ? 'Admin' : 'Käyttäjä'; ?></td>
                            <td data-label="Rekisteröitynyt"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                            <td data-label="Ilmoittautunut"><?php echo $user['registration_count'] > 0 ? 'Kyllä' : 'Ei'; ?></td>
                            <td data-label="Toiminnot">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Haluatko varmasti poistaa käyttäjän <?php echo htmlspecialchars($user['username']); ?>?')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" value="1">Poista</button>
                                    </form>
                                <?php else: ?>
                                    <em>Oma tili</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        
        <!-- Tapahtumailmoittautumiset -->
        <section>
            <h2>Tapahtuma-ilmoittautumiset</h2>
            
            <?php if (empty($registrations)): ?>
                <p>Ei ilmoittautumisia.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Käyttäjä</th>
                            <th>Sähköposti</th>
                            <th>Lipputyyppi</th>
                            <th>Ilmoittautumisaika</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td data-label="Käyttäjä"><?php echo htmlspecialchars($reg['username']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($reg['email']); ?></td>
                            <td data-label="Lippu">
                                <?php 
                                switch($reg['ticket_type']) {
                                    case 'day': echo 'Päivälippu'; break;
                                    case 'weekend': echo 'Viikonloppu'; break;
                                    case 'vip': echo 'VIP'; break;
                                    default: echo 'Tuntematon';
                                }
                                ?>
                            </td>
                            <td data-label="Aika"><?php echo date('d.m.Y H:i', strtotime($reg['registration_date'])); ?></td>
                            <td data-label="Toiminnot">
                                <form method="post" style="display: inline;" onsubmit="return confirm('Haluatko varmasti poistaa ilmoittautumisen?')">
                                    <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                    <button type="submit" name="remove_registration" value="1">Poista</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                    <p><strong>Thunderstorm Rock Festival 2025</strong></p>
                    <p>25.-26. Heinäkuuta 2025</p>
                    <p>Ratina, Tampere</p>
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
                <p>&copy; 2025 Thunderstorm Rock Festival. Kaikki oikeudet pidätetään.</p>
            </div>
        </footer>
    </main>
</body>
</html>