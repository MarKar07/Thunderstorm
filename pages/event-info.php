<?php
// Lataa turvallisuusfunktiot
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/security.php';

$is_logged_in = is_logged_in();
$is_admin = is_admin();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tapahtuman tiedot - <?php echo escape(SITE_NAME); ?></title>
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
    
    <!--Pääkontentti -->
    <main>
        <div class="hero-event">
            <h1>Tapahtumainfo</h1>
            <p class="hero-text">Kaksi päivää täynnä huippurock-musiikkia!</p>
        </div>

        <section>
            <h2>🎤 Esiintyjät</h2>
            <p>Tutustu festivaalin upeisiin artisteihin!</p>

            <div class="artists-grid">
                <!-- The Rising Storm -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/band-4823341_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">PERJANTAI</span>
                        <h3 class="artist-name">The Rising Storm</h3>
                        <span class="artist-day">Rock</span>
                    </div>
                </div>

                <!-- Electric Thunder -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/band-2812392_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">PERJANTAI</span>
                        <h3 class="artist-name">Electric Thunder</h3>
                        <span class="artist-day">Rock</span>
                    </div>
                </div>

                <!-- Metal Mayhem -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/hostile-886029_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">PERJANTAI</span>
                        <h3 class="artist-name">Metal Mayhem</h3>
                        <span class="artist-day">Heavy Metal</span>
                    </div>
                </div>

                <!-- Rock Legends -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/band-4671748_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">PERJANTAI</span>
                        <h3 class="artist-name">Rock Legends</h3>
                        <span class="artist-day">Classic Rock</span>
                    </div>
                </div>

                <!-- Finnish Rock Masters -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/avett-brothers-2390713_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">⭐ HEADLINER</span>
                        <h3 class="artist-name">Finnish Rock Masters</h3>
                        <span class="artist-day">Perjantain pääesiintyjä</span>
                    </div>
                </div>

                <!-- New Wave Rockers -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/concert-316381_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">LAUANTAI</span>
                        <h3 class="artist-name">New Wave Rockers</h3>
                        <span class="artist-day">New Wave</span>
                    </div>
                </div>

                <!-- Classic Revival -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/musician-1658887_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">LAUANTAI</span>
                        <h3 class="artist-name">Classic Revival</h3>
                        <span class="artist-day">Classic Rock</span>
                    </div>
                </div>

                <!-- Heavy Metal Heroes -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/singer-1595864_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">LAUANTAI</span>
                        <h3 class="artist-name">Heavy Metal Heroes</h3>
                        <span class="artist-day">Heavy Metal</span>
                    </div>
                </div>

                <!-- Progressive Power -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/photography-2449748_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">LAUANTAI</span>
                        <h3 class="artist-name">Progressive Power</h3>
                        <span class="artist-day">Progressive Rock</span>
                    </div>
                </div>

                <!-- Alternative Assault -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/concert-2566002_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">LAUANTAI</span>
                        <h3 class="artist-name">Alternative Assault</h3>
                        <span class="artist-day">Alternative Rock</span>
                    </div>
                </div>

                <!-- International Rock Stars -->
                <div class="artist-card">
                    <div class="artist-card-bg" style="background-image: url('../assets/images/concert-705914_1920.jpg');"></div>
                    <div class="artist-card-overlay">
                        <span class="artist-type">⭐ HEADLINER</span>
                        <h3 class="artist-name">International Rock Stars</h3>
                        <span class="artist-day">Lauantain pääesiintyjä</span>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h2>🎵 Artistit & Ohjelma</h2>
            
            <h3>Perjantai 25.7.2025 - Main Stage</h3>
            <ul>
                <li><strong>16:30-17:15</strong> - The Rising Storm</li>
                <li><strong>17:45-18:30</strong> - Electric Thunder</li>
                <li><strong>19:00-19:45</strong> - Metal Mayhem</li>
                <li><strong>20:30-21:30</strong> - Rock Legends</li>
                <li><strong>22:00-23:30</strong> - HEADLINER: Finnish Rock Masters</li>
            </ul>

            <h3>Lauantai 26.7.2025 - Main Stage</h3>
            <ul>
                <li><strong>14:30-15:15</strong> - New Wave Rockers</li>
                <li><strong>15:45-16:30</strong> - Classic Revival</li>
                <li><strong>17:00-17:45</strong> - Heavy Metal Heroes</li>
                <li><strong>18:30-19:30</strong> - Progressive Power</li>
                <li><strong>20:00-21:00</strong> - Alternative Assault</li>
                <li><strong>21:30-23:00</strong> - HEADLINER: International Rock Stars</li>
            </ul>
        </section>

        <section>
            <h2>📅 Tapahtuman tiedot</h2>
            <p><strong>Päivämäärät:</strong> <?php echo escape(EVENT_DATE); ?></p>
            <p><strong>Paikka:</strong> <?php echo escape(EVENT_LOCATION); ?></p>
            <p><strong>Ovet aukeavat:</strong> Perjantai klo 16:00, Lauantai klo 14:00</p>
            <p><strong>Ikäraja:</strong> K-18</p>
        </section>

        <section>
            <h2>🎫 Lipputyypit ja hinnat</h2>
            
            <div>
                <h3>📅 Päivälippu - 35€</h3>
                <ul>
                    <li>Pääsy festivaalialueelle yhtenä päivänä</li>
                    <li>Kaikki konsertit valitsemanasi päivänä</li>
                    <li>Pääsy ruoka- ja juoma-alueille</li>
                </ul>
            </div>

            <div>
                <h3>🎫 Kahden päivän lippu - 60€</h3>
                <ul>
                    <li>Pääsy festivaalialueelle molempina päivinä</li>
                    <li>Kaikki konsertit ja tapahtumat</li>
                    <li>Pääsy ruoka- ja juoma-alueille</li>
                </ul>
            </div>
            
            <div>
                <h3>⭐ VIP-lippu - 120€</h3>
                <ul>
                    <li>Kaikki viikonloppulipun edut</li>
                    <li>VIP-katsomo päälavalla</li>
                    <li>Backstage-pääsy artistien tapaamisiin</li>
                    <li>Oma VIP-baari ja ruoka-alue</li>
                    <li>Ilmainen Thunderstorm t-paita</li>
                    <li>Meet & Greet pääesiintyjien kanssa</li>
                </ul>
            </div>

            <?php if (!$is_logged_in): ?>
            <p><strong><a href="register.php">Rekisteröidy ja osta liput täältä!</a></strong></p>
            <?php else: ?>
            <p><strong><a href="profile.php">Mene profiiliin ostaaksesi liput!</a></strong></p>
            <?php endif; ?>
        </section>

        <section>
            <h2>🏪 Festivaalialue</h2>
            <p><strong>Paikka:</strong> Ratina, Tampere - Ratinan stadion ja ympäristö</p>
            
            <h3>Alueella:</h3>
            <ul>
                <li><strong>Main Stage</strong> - Esiintyjät</li>
                <li><strong>Food Court</strong> - 15 erilaista ruokakojua</li>
                <li><strong>Rock Bar</strong> - Juomat ja cocktailit</li>
                <li><strong>Merchandise Area</strong> - Bändien tuotteet</li>
                <li><strong>Chill Zone</strong> - Levähdysalue</li>
            </ul>
        </section>

        <section>
            <h2>🚗 Saapuminen ja pysäköinti</h2>
            <p><strong>Osoite:</strong> Ratinan stadion, Ratina, 33100 Tampere</p>
            
            <h3>Julkisilla:</h3>
            <ul>
                <li><strong>Bussit:</strong> Linja 1, 4, 5 - pysäkki "Ratina"</li>
                <li><strong>Raitiovaunu:</strong> Linja 1 - "Ratinan stadion"</li>
            </ul>
            
            <h3>Autolla:</h3>
            <ul>
                <li><strong>Pysäköinti:</strong> Ratinan parkkihalli (5€/päivä)</li>
            </ul>
        </section>

        <section>
            <h2>⚠️ Tärkeää tietoa</h2>
            
            <h3>Kiellettyä alueella:</h3>
            <ul>
                <li>Omat juomat ja ruoat</li>
                <li>Lasipullot ja -astiat</li>
                <li>Ammattikamerat</li>
                <li>Koirat (paitsi opaskoirat)</li>
            </ul>

            <h3>Mukaan kannattaa ottaa:</h3>
            <ul>
                <li>Henkilöllisyystodistus</li>
                <li>Sadetakki</li>
                <li>Mukavat kengät</li>
                <li>Aurinkovoide</li>
                <li>Käteistä rahaa</li>
            </ul>
        </section>

        <section>
            <h2>🎸 Nähdään festivaalilla!</h2>
            <p>Thunderstorm Rock Festival tarjoaa upean viikonlopun täynnä parasta rock-musiikkia, 
               hyvää ruokaa ja mahtavaa tunnelmaa. Tervetuloa kokemaan unohtumaton rock-elämys!</p>
            
            <p><strong>Seuraa meitä sosiaalisessa mediassa:</strong></p>
            <ul>
                <li>Facebook: @ThunderstormRockFestival</li>
                <li>Instagram: @thunderstormrock</li>
                <li>Twitter: @TRockFest</li>
            </ul>
        </section>

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