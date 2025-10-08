<?php
/**
 * CSRF Protection Functions
 * Thunderstorm Rock Festival 2025
 */

/**
 * Luo uusi CSRF-token sessioniin
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Tarkista CSRF-token
 * @param string $token Token joka tarkistetaan
 * @return bool True jos token on validi
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Luo HTML input field CSRF-tokenille
 * @return string Hidden input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Tarkista POST-request CSRF-token
 * Lopettaa suorituksen jos token ei kelpaa
 */
function verify_csrf_or_die() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($token)) {
            http_response_code(403);
            die('CSRF-token virheellinen. Yritä uudelleen.');
        }
    }
}
?>