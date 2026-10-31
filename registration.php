<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$full_name = $username = $email = $department = '';

$departments = [
    'Computer Science',
    'Information Technology',
    'Business Administration',
    'Electronics',
    'Mechanical Engineering',
    'Other'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $username === '' || $email === '' || $department === '' || $password === '' || $confirm === '') {
        $errors[] = 'Please fill in all fields.';
    }
    if ($full_name !== '' && mb_strlen($full_name) > 100) {
        $errors[] = 'Full name must be 100 characters or fewer.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($username !== '' && !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $errors[] = 'Username must be 3-50 characters: letters, numbers and underscores only.';
    }
    if ($department !== '' && !in_array($department, $departments, true)) {
        $errors[] = 'Please choose a department from the list.';
    }
    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }

    if (!$errors) {
        try {
            $check = $pdo->prepare('SELECT student_id FROM students WHERE email = :email OR username = :username');
            $check->execute(['email' => $email, 'username' => $username]);

            if ($check->fetch()) {
                $errors[] = 'This email address or username is already registered.';
            } else {
                $insert = $pdo->prepare(
                    "INSERT INTO students (full_name, username, email, department, password_hash, role, status)
                     VALUES (:full_name, :username, :email, :department, :password_hash, 'Student', 'Active')"
                );
                $insert->execute([
                    'full_name'     => $full_name,
                    'username'      => $username,
                    'email'         => $email,
                    'department'    => $department,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT)
                ]);

                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'This email address or username is already registered.';
            } else {
                error_log('Registration error: ' . $e->getMessage());
                $errors[] = 'Registration could not be completed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
$activeNav = 'registration.php';
require __DIR__ . '/includes/header.php';
?>
<section class="card narrow">
    <h1 class="page-title center">Student Registration</h1>
    <p class="center">Create your Student Hub account.</p>

    <?php if ($errors): ?>
        <div class="alert error" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="registration.php" method="POST">
        <?= csrf_field() ?>

        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?= h($full_name) ?>" maxlength="100" autocomplete="name" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= h($username) ?>"
               minlength="3" maxlength="50" pattern="[A-Za-z0-9_]{3,50}"
               title="Use 3-50 letters, numbers or underscores." autocomplete="username" required aria-describedby="usernameHint">
        <p class="hint" id="usernameHint">3-50 letters, numbers or underscores.</p>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?= h($email) ?>" maxlength="150" autocomplete="email" required>

        <label for="department">Department</label>
        <select id="department" name="department" required>
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= h($dept) ?>" <?= $department === $dept ? 'selected' : '' ?>><?= h($dept) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required aria-describedby="pwHint">
        <p class="hint" id="pwHint">Minimum 8 characters.</p>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>

        <button type="submit" class="btn block">Create Account</button>
    </form>

    <p class="center">Already registered? <a href="login.php">Login</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
