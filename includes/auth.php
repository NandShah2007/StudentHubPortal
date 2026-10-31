<?php
// Shared helpers: escaping, CSRF tokens, flash messages, login checks, 403 page.
require_once __DIR__ . '/bootstrap.php';

/** Escape text before printing it in HTML (stops XSS). */
function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------------- CSRF ---------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_valid(?string $token = null): bool
{
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Stop the request if the CSRF token is wrong. Call on every POST handler. */
function csrf_check(): void
{
    if (!csrf_valid()) {
        http_response_code(419);
        exit('Security check failed (invalid or expired form token). Go back, refresh the page and try again.');
    }
}

/* ---------------- Flash messages ---------------- */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type === 'error' ? 'error' : 'success', 'message' => $message];
}

function flash_render(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $role = $f['type'] === 'error' ? 'alert' : 'status';
    return '<div class="alert ' . h($f['type']) . '" role="' . $role . '">' . h($f['message']) . '</div>';
}

/* ---------------- Login / roles ---------------- */
function is_logged_in(): bool
{
    return !empty($_SESSION['student_id']);
}

function destroy_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax'
        ]);
    }
    session_destroy();
}

/**
 * Must be logged in, and not timed out.
 * $base = path back to the portal root ('' for root pages, '../' for sub-folders).
 */
function require_login(string $base = ''): void
{
    if (!is_logged_in()) {
        header('Location: ' . $base . 'login.php?denied=1');
        exit;
    }

    $last = $_SESSION['last_activity'] ?? 0;
    if (!$last || (time() - $last) >= SESSION_TIMEOUT) {
        destroy_session();
        header('Location: ' . $base . 'login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

/** Must be logged in AND have the given role ('Admin' or 'Student'). Otherwise 403. */
function require_role(string $role, string $base = ''): void
{
    require_login($base);
    if (($_SESSION['role'] ?? '') !== $role) {
        show_403($base);
    }
}

/** Shows the 403 Forbidden page inside the normal layout and stops. */
function show_403(string $base = ''): void
{
    http_response_code(403);
    $pageTitle = '403 Forbidden';
    require __DIR__ . '/header.php';
    ?>
    <section class="card narrow center">
        <h1 style="font-size:4rem;margin:0;">403</h1>
        <h2>Access Forbidden</h2>
        <p>You do not have permission to open this page.</p>
        <p><a class="btn" href="<?= h($base) ?>dashboard.php">Back to my dashboard</a></p>
    </section>
    <?php
    require __DIR__ . '/footer.php';
    exit;
}
