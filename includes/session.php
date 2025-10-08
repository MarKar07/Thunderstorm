<?php
/**
 * Secure Session Management
 * Thunderstorm Rock Festival 2025
 */

/**
 * Aloita turvallinen sessio
 */
function secure_session_start() {
    // Jos sessio on jo käynnissä, älä aloita uudelleen
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    // Session-turvallisuusasetukset
    ini_set('session.cookie_httponly', 1);  // Ei JavaScript-pääsyä cookieihin
    ini_set('session.use_only_cookies', 1);  // Vain cookiet, ei URL-parametreja
    ini_set('session.cookie_samesite', 'Lax'); // CSRF-suojaus (Lax = toimii POST:lla)
    
    // Jos HTTPS käytössä, aseta secure flag
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Aloita sessio
    session_start();
    
    // Uudista session ID jos uusi sessio (login-jälkeen)
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        $_SESSION['created_at'] = time();
    }
    
    // Session timeout tarkistus (30 minuuttia)
    if (isset($_SESSION['last_activity'])) {
        $timeout = 1800; // 30 minuuttia sekunneissa
        
        if (time() - $_SESSION['last_activity'] > $timeout) {
            // Sessio vanhentunut
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['timeout_message'] = "Istunto vanhentunut. Kirjaudu uudelleen.";
        }
    }
    
    // Päivitä viimeisin aktiviteetti
    $_SESSION['last_activity'] = time();
    
    // IP-osoitteen tarkistus (estää session hijacking)
    if (isset($_SESSION['user_ip'])) {
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            // IP muuttunut - mahdollinen hyökkäys
            session_unset();
            session_destroy();
            session_start();
        }
    } else {
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Tuhoa sessio turvallisesti (uloskirjautuminen)
 */
function destroy_session() {
    // Tyhjennä session-muuttujat
    $_SESSION = array();
    
    // Tuhoa session-cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Tuhoa sessio
    session_destroy();
}

/**
 * Uudista session ID kirjautumisen yhteydessä
 */
function regenerate_session_on_login() {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
    $_SESSION['created_at'] = time();
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
}
?>