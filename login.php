<?php
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Go to the right dashboard.
if (is_logged_in() && isset($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$pageTitle = 'Login';
$activeNav = 'login.php';
require __DIR__ . '/includes/header.php';
?>
<section class="card narrow">
    <h1 class="page-title center">Login</h1>
    <p class="center">Enter your registered email and password.</p>

    <?php if ($error !== ''): ?>
        <div class="alert error" role="alert"><?= h($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['timeout'])): ?>
        <div class="alert error" role="alert">Your session expired. Please log in again.</div>
    <?php endif; ?>
    <?php if (isset($_GET['loggedout'])): ?>
        <div class="alert success" role="status">You have logged out successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['denied'])): ?>
        <div class="alert error" role="alert">Please log in to access that page.</div>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert success" role="status">Account created. You can log in now.</div>
    <?php endif; ?>

    <form action="authenticate.php" method="POST">
        <?= csrf_field() ?>
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" maxlength="150" autocomplete="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <button type="submit" class="btn block">Login</button>
    </form>

    <p class="center">New student? <a href="registration.php">Create an account</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
