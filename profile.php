<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$id = (int) $_SESSION['student_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare('SELECT password_hash FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($current, (string) $row['password_hash'])) {
        $errors[] = 'Your current password is not correct.';
    }
    if (strlen($new) < 8) {
        $errors[] = 'The new password must be at least 8 characters long.';
    }
    if ($new !== $confirm) {
        $errors[] = 'New password and confirm password do not match.';
    }

    if (!$errors) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE students SET password_hash = ? WHERE student_id = ?');
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        $stmt->close();

        session_regenerate_id(true);
        flash_set('success', 'Your password was changed successfully.');
        header('Location: profile.php');
        exit;
    }
}

$stmt = $conn->prepare('SELECT full_name, username, email, department, role, status FROM students WHERE student_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'My Profile';
$activeNav = 'profile.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">My Profile</h1>

<section class="card medium">
    <h2>Account details</h2>
    <table class="data">
        <tbody>
            <tr><th scope="row">Full name</th><td><?= h($me['full_name'] ?? '') ?></td></tr>
            <tr><th scope="row">Username</th><td><?= h($me['username'] ?? '') ?></td></tr>
            <tr><th scope="row">Email</th><td><?= h($me['email'] ?? '') ?></td></tr>
            <tr><th scope="row">Department</th><td><?= h($me['department'] ?? '') ?></td></tr>
            <tr><th scope="row">Role</th><td><?= h($me['role'] ?? '') ?></td></tr>
            <tr><th scope="row">Status</th><td><?= h($me['status'] ?? '') ?></td></tr>
        </tbody>
    </table>
    <p class="hint">To change your name, email or department, please contact an administrator.</p>
</section>

<section class="card medium">
    <h2>Change password</h2>
    <?php if ($errors): ?>
        <div class="alert error" role="alert">
            <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="profile.php">
        <?= csrf_field() ?>
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>

        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password" required>

        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>

        <button type="submit" class="btn block">Change password</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
