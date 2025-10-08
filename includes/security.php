<?php
/**
 * Security & Validation Functions
 * Thunderstorm Rock Festival 2025
 */

/**
 * Escapoi HTML-output XSS-suojaukseen
 * @param string $string Teksti joka escapoidaan
 * @return string Turvallinen teksti
 */
function escape($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escapoi JavaScript-output
 * @param string $string Teksti joka escapoidaan
 * @return string JSON-encoded turvallinen teksti
 */
function escape_js($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Puhdista ja validoi email
 * @param string $email Sähköpostiosoite
 * @return string|false Puhdistettu email tai false jos virheellinen
 */
function validate_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validoi käyttäjätunnus
 * @param string $username Käyttäjätunnus
 * @return bool True jos kelvollinen
 */
function validate_username($username) {
    // 3-50 merkkiä, kirjaimet, numerot, alaviiva, väliviiva
    return preg_match('/^[a-zA-Z0-9_-]{3,50}$/u', $username) === 1;
}

/**
 * Validoi salasana (6+ merkkiä)
 * @param string $password Salasana
 * @param array &$errors Virheviestit (referenssillä)
 * @return bool True jos kelvollinen
 */
function validate_password($password, &$errors = []) {
    if (strlen($password) < 6) {
        $errors[] = "Salasanan on oltava vähintään 6 merkkiä";
        return false;
    }
    return true;
}

/**
 * Puhdista string input
 * @param string $input Syöte
 * @return string Puhdistettu syöte
 */
function sanitize_string($input) {
    return trim(strip_tags($input));
}

/**
 * Tarkista onko käyttäjä kirjautunut
 * @return bool True jos kirjautunut
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Tarkista onko käyttäjä admin
 * @return bool True jos admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Pakota kirjautuminen - ohjaa login-sivulle jos ei kirjautunut
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Pakota admin-oikeudet
 */
function require_admin() {
    if (!is_admin()) {
        header("Location: login.php");
        exit();
    }
}
?>