<?php
/**
 * Configuration File
 * Thunderstorm Rock Festival 2025
 * 
 * HUOM: Tämä tiedosto sisältää kaikki sovelluksen asetukset.
 * Muokkaa arvoja tarpeen mukaan.
 */

// ========================================
// SIVUSTON ASETUKSET
// ========================================
define('SITE_NAME', 'Thunderstorm Rock Festival');
define('SITE_TAGLINE', '2025 - Suomen rockaavain festivaali');
define('SITE_URL', 'http://localhost/thunderstorm'); // Muuta omaksi domainiksi
define('ADMIN_EMAIL', 'admin@thunderstormrock.fi');

// ========================================
// TAPAHTUMAN TIEDOT
// ========================================
define('EVENT_NAME', 'Thunderstorm Rock Festival 2025');
define('EVENT_DATE', '25.-26. Heinäkuuta 2025');
define('EVENT_LOCATION', 'Ratina, Tampere');

// ========================================
// TURVALLISUUSASETUKSET
// ========================================
// Salasana
define('PASSWORD_MIN_LENGTH', 6);

// Session timeout (sekunteina)
define('SESSION_TIMEOUT', 1800); // 30 minuuttia

// Rate limiting
define('MAX_LOGIN_ATTEMPTS', 5);      // Max kirjautumisyritykset
define('MAX_REGISTER_ATTEMPTS', 3);   // Max rekisteröintiyritykset
define('MAX_CONTACT_ATTEMPTS', 5);    // Max yhteystiedot-lähetykset
define('RATE_LIMIT_WINDOW', 900);     // Aikaikkunan pituus (15min)

// CSRF token uudistus
define('CSRF_TOKEN_EXPIRE', 3600);    // 1 tunti

// ========================================
// TIETOKANTA-ASETUKSET
// ========================================
// HUOM: Nämä otetaan database.php tiedostosta
// Tämä on vain dokumentointia varten

// ========================================
// LIPPUTYYPIT JA HINNAT
// ========================================
define('TICKET_TYPES', [
    'day' => [
        'name' => 'Päivälippu',
        'price' => 35,
        'description' => 'Pääsy yhtenä päivänä'
    ],
    'weekend' => [
        'name' => 'Kahden päivän lippu',
        'price' => 60,
        'description' => 'Pääsy molempina päivinä'
    ],
    'vip' => [
        'name' => 'VIP-lippu',
        'price' => 120,
        'description' => 'VIP-alue ja backstage-pääsy'
    ]
]);

// ========================================
// SÄHKÖPOSTIASETUKET (tuleville ominaisuuksille)
// ========================================
define('SMTP_ENABLED', false);        // Käytössä myöhemmin
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('EMAIL_FROM_ADDRESS', 'noreply@thunderstormrock.fi');
define('EMAIL_FROM_NAME', 'Thunderstorm Rock Festival');

// ========================================
// LOKITUSASETUKSET
// ========================================
define('ENABLE_ERROR_LOGGING', true);
define('ENABLE_SECURITY_LOGGING', true);
define('LOG_DIRECTORY', __DIR__ . '/../logs');

// ========================================
// KEHITYSYMPÄRISTÖ
// ========================================
// Aseta true vain kehityksen aikana!
define('DEVELOPMENT_MODE', true);

if (DEVELOPMENT_MODE) {
    // Näytä virheet kehityksen aikana
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Piilota virheet tuotannossa
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// ========================================
// AIKAVYÖHYKE
// ========================================
date_default_timezone_set('Europe/Helsinki');
?>