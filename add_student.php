<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/practical14/log_action.php';
require_role('Admin');

$error = '';
$full_name = $username = $email = $department = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($full_name === '' || $username === '' || $email === '' || $department === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = 'Username must be 3-50 letters, numbers or underscores.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } else {
        try {
            $check = $conn->prepare('SELECT student_id FROM students WHERE email = ? OR username = ? LIMIT 1');
            $check->bind_param('ss', $email, $username);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $error = 'This email or username is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'Student';
                $status = 'Active';

                $stmt = $conn->prepare(
                    'INSERT INTO students (full_name, username, email, department, password_hash, role, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('sssssss', $full_name, $username, $email, $department, $hash, $role, $status);
                $stmt->execute();
                $newId = $conn->insert_id;
                $stmt->close();

                logAction('ADD_STUDENT', 'Added student ID: ' . $newId);
                flash_set('success', 'Student added successfully.');
                header('Location: students.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Add student failed: ' . $e->getMessage());
            $error = 'Could not add the student. Check the details and try again.';
        }
    }
}

$pageTitle = 'Add Student';
$activeNav = 'students.php';
require __DIR__ . '/includes/header.php';
?>
<section class="card narrow">
    <h1 class="page-title">Add New Student</h1>
    <?php if ($error !== ''): ?><div class="alert error" role="alert"><?= h($error) ?></div><?php endif; ?>

    <form method="POST" action="add_student.php">
        <?= csrf_field() ?>
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= h($full_name) ?>" maxlength="100" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= h($username) ?>" maxlength="50" pattern="[A-Za-z0-9_]{3,50}" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= h($email) ?>" maxlength="150" required>

        <label for="department">Course / Department</label>
        <input type="text" id="department" name="department" value="<?= h($department) ?>" maxlength="100" placeholder="e.g. Computer Science" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>

        <div class="btn-row">
            <button type="submit" class="btn">Add Student</button>
            <a href="students.php" class="btn secondary">Cancel</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
