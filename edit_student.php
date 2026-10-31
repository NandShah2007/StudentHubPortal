<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/practical14/log_action.php';
require_role('Admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    flash_set('error', 'Invalid student ID.');
    header('Location: students.php');
    exit;
}

$stmt = $conn->prepare('SELECT student_id, full_name, username, email, department, role, status FROM students WHERE student_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    flash_set('error', 'Student record not found.');
    header('Location: students.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $status     = $_POST['status'] ?? $student['status'];

    // Never let an admin lock themselves out.
    if ((int) $student['student_id'] === (int) $_SESSION['student_id']) {
        $status = 'Active';
    }

    if ($full_name === '' || $username === '' || $email === '' || $department === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = 'Username must be 3-50 letters, numbers or underscores.';
    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {
        $error = 'Invalid status.';
    } else {
        try {
            $check = $conn->prepare('SELECT student_id FROM students WHERE (email = ? OR username = ?) AND student_id <> ? LIMIT 1');
            $check->bind_param('ssi', $email, $username, $id);
            $check->execute();
            $dup = $check->get_result()->fetch_assoc();
            $check->close();

            if ($dup) {
                $error = 'That email or username is already used by another account.';
            } else {
                $upd = $conn->prepare('UPDATE students SET full_name = ?, username = ?, email = ?, department = ?, status = ? WHERE student_id = ?');
                $upd->bind_param('sssssi', $full_name, $username, $email, $department, $status, $id);
                $upd->execute();
                $upd->close();

                logAction('EDIT_STUDENT', 'Updated student ID: ' . $id);
                flash_set('success', 'Student details updated successfully.');
                header('Location: students.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Update student failed: ' . $e->getMessage());
            $error = 'Could not update the student. Please check the details and try again.';
        }
    }

    // Show what the admin typed again.
    $student['full_name'] = $full_name;
    $student['username'] = $username;
    $student['email'] = $email;
    $student['department'] = $department;
    $student['status'] = $status;
}

$pageTitle = 'Edit Student';
$activeNav = 'students.php';
require __DIR__ . '/includes/header.php';
?>
<section class="card narrow">
    <h1 class="page-title">Edit Student Details</h1>
    <?php if ($error !== ''): ?><div class="alert error" role="alert"><?= h($error) ?></div><?php endif; ?>

    <form method="POST" action="edit_student.php">
        <?= csrf_field() ?>
        <input type="hidden" name="student_id" value="<?= (int) $student['student_id'] ?>">

        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= h($student['full_name']) ?>" maxlength="100" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= h($student['username']) ?>" maxlength="50" pattern="[A-Za-z0-9_]{3,50}" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= h($student['email']) ?>" maxlength="150" required>

        <label for="department">Course / Department</label>
        <input type="text" id="department" name="department" value="<?= h($student['department']) ?>" maxlength="100" required>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="Active" <?= $student['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= $student['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>

        <div class="btn-row">
            <button type="submit" class="btn">Save Changes</button>
            <a href="students.php" class="btn secondary">Cancel</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
