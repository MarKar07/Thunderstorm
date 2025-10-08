<?php
/**
 * Error Handling & Logging
 * Thunderstorm Rock Festival 2025
 */

/**
 * Loki virhe tiedostoon (älä näytä käyttäjälle teknisiä virheitä)
 * 
 * @param string $message Virheilmoitus
 * @param array $context Lisätietoja virheestä
 * @param string $level Virheen vakavuus (ERROR, WARNING, INFO)
 */
function log_error($message, $context = [], $level = 'ERROR') {
    // Varmista että logs-kansio on olemassa
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_id = $_SESSION['user_id'] ?? 'guest';
    $username = $_SESSION['username'] ?? 'guest';
    
    // Rakenna log-viesti
    $log_message = "[$timestamp] [$level] [$ip] [User: $username (ID: $user_id)]\n";
    $log_message .= "Message: $message\n";
    
    // Lisää konteksti jos on
    if (!empty($context)) {
        $log_message .= "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Lisää stack trace jos on vakava virhe
    if ($level === 'ERROR') {
        $log_message .= "Stack trace:\n" . debug_backtrace_string() . "\n";
    }
    
    $log_message .= str_repeat('-', 80) . "\n\n";
    
    // Kirjoita lokiin
    error_log($log_message, 3, $log_file);
}

/**
 * Luo debug backtrace string
 */
function debug_backtrace_string() {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    $result = '';
    
    foreach ($trace as $i => $call) {
        if ($i === 0) continue; // Ohita tämä funktio
        
        $file = $call['file'] ?? 'unknown';
        $line = $call['line'] ?? 0;
        $function = $call['function'] ?? 'unknown';
        
        $result .= "  #$i $file($line): $function()\n";
    }
    
    return $result;
}

/**
 * Käsittele virhe ja näytä käyttäjälle ystävällinen viesti
 * 
 * @param string $user_message Viesti käyttäjälle
 * @param string $technical_message Tekninen virhe (lokataan)
 * @param array $context Lisätietoja
 */
function handle_error($user_message, $technical_message = '', $context = []) {
    global $message, $message_type;
    
    // Näytä käyttäjälle ystävällinen viesti
    $message = $user_message;
    $message_type = "error";
    
    // Loki tekninen virhe
    if (!empty($technical_message)) {
        log_error($technical_message, $context);
    }
}

/**
 * Käsittele tietokantavirhe
 * 
 * @param PDOException $e PDO-poikkeus
 * @param string $user_message Viesti käyttäjälle
 */
function handle_db_error(PDOException $e, $user_message = "Tapahtui virhe. Yritä myöhemmin uudelleen.") {
    // Loki tekninen virhe
    log_error("Database error: " . $e->getMessage(), [
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    // Näytä käyttäjälle yleinen viesti
    handle_error($user_message, '');
}

/**
 * Loki onnistunut toiminto (security events)
 * 
 * @param string $action Toiminto (esim. 'login', 'register', 'password_change')
 * @param array $details Yksityiskohdat
 */
function log_security_event($action, $details = []) {
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_id = $_SESSION['user_id'] ?? 'guest';
    $username = $_SESSION['username'] ?? 'guest';
    
    $log_message = "[$timestamp] [SECURITY] [$ip] [User: $username (ID: $user_id)]\n";
    $log_message .= "Action: $action\n";
    
    if (!empty($details)) {
        $log_message .= "Details: " . json_encode($details, JSON_PRETTY_PRINT) . "\n";
    }
    
    $log_message .= str_repeat('-', 80) . "\n\n";
    
    error_log($log_message, 3, $log_file);
}

/**
 * Aseta ystävällinen virheilmoitus käyttäjälle
 * 
 * @param string $message Viesti
 * @param string $type Tyyppi (success, error, info)
 */
function set_message($message_text, $type = 'info') {
    global $message, $message_type;
    $message = $message_text;
    $message_type = $type;
}
?>