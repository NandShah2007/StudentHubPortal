<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

csrf_check();

function login_fail(string $message): void
{
    $_SESSION['login_error'] = $message;
    header('Location: login.php');
    exit;
}

// Very simple brute-force protection: 5 wrong tries in 5 minutes = wait.
$window = 300;
$attempts = array_filter(
    $_SESSION['login_attempts'] ?? [],
    function ($t) use ($window) { return $t > time() - $window; }
);
if (count($attempts) >= 5) {
    login_fail('Too many failed attempts. Please wait 5 minutes and try again.');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    login_fail('Please enter your email and password.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    login_fail('Invalid email or password.');
}

try {
    $stmt = $conn->prepare(
        "SELECT student_id, full_name, email, password_hash, role, status
         FROM students
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log('Authentication query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to process login right now.');
}

if (
    !$user ||
    empty($user['password_hash']) ||
    !password_verify($password, $user['password_hash'])
) {
    $attempts[] = time();
    $_SESSION['login_attempts'] = array_values($attempts);
    login_fail('Invalid email or password.');
}

// Deactivated accounts cannot log in.
if (($user['status'] ?? 'Active') !== 'Active') {
    login_fail('This account is inactive. Please contact the administrator.');
}

// Prevent session fixation: new session ID after a successful login.
$oldSessionId = session_id();
session_regenerate_id(true);
$newSessionId = session_id();

$_SESSION = [];
$_SESSION['student_id']    = (int) $user['student_id'];
$_SESSION['full_name']     = $user['full_name'];
$_SESSION['email']         = $user['email'];
$_SESSION['role']          = $user['role'];
$_SESSION['last_activity'] = time();
$_SESSION['session_regenerated'] = ($oldSessionId !== $newSessionId);
$_SESSION['login_success'] = true;

header('Location: dashboard.php');
exit;
