<?php
// Starts the session safely. Every page loads this (through includes/auth.php).
if (!defined('SESSION_TIMEOUT')) {
    // Seconds of inactivity before automatic logout.
    // 600 = 10 minutes. Change to 120 to test the timeout quickly.
    define('SESSION_TIMEOUT', 600);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
