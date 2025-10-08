<?php
/**
 * Rate Limiting Functions
 * Thunderstorm Rock Festival 2025
 * Estää liikaa yrityksiä samalta IP-osoitteelta
 */

/**
 * Tarkista rate limit tietylle toiminnolle
 * 
 * @param string $action Toiminnon nimi (esim. 'login', 'register', 'contact')
 * @param int $max_attempts Maksimi yritysten määrä
 * @param int $time_window Aikaikkunan pituus sekunneissa
 * @return mixed True jos OK, array jos estetty
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 900) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    
    // Alusta data jos ei ole
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time(),
            'blocked_until' => 0
        ];
    }
    
    $data = $_SESSION[$key];
    
    // Tarkista onko vielä estetty
    if ($data['blocked_until'] > time()) {
        $wait_seconds = $data['blocked_until'] - time();
        return [
            'blocked' => true,
            'wait_minutes' => ceil($wait_seconds / 60),
            'wait_seconds' => $wait_seconds
        ];
    }
    
    // Nollaa jos aikaikkunan vanhentunut
    if (time() - $data['first_attempt'] > $time_window) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time(),
            'blocked_until' => 0
        ];
        return true;
    }
    
    // Tarkista raja
    if ($data['attempts'] >= $max_attempts) {
        // Estä seuraavaksi 15 minuutiksi
        $_SESSION[$key]['blocked_until'] = time() + 900;
        
        return [
            'blocked' => true,
            'wait_minutes' => 15,
            'wait_seconds' => 900
        ];
    }
    
    // Lisää yritys
    $_SESSION[$key]['attempts']++;
    
    return true;
}

/**
 * Nollaa rate limit (onnistuneen toiminnon jälkeen)
 * 
 * @param string $action Toiminnon nimi
 */
function reset_rate_limit($action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Hae jäljellä olevat yritykset
 * 
 * @param string $action Toiminnon nimi
 * @param int $max_attempts Maksimi yritykset
 * @return int Jäljellä olevat yritykset
 */
function get_remaining_attempts($action, $max_attempts = 5) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    
    if (!isset($_SESSION[$key])) {
        return $max_attempts;
    }
    
    $attempts = $_SESSION[$key]['attempts'];
    return max(0, $max_attempts - $attempts);
}
?>