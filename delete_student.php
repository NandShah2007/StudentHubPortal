<?php
// "Delete" = deactivate. The record is kept, status becomes Inactive.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/practical14/log_action.php';
require_role('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: students.php');
    exit;
}
csrf_check();

$id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    flash_set('error', 'Invalid student ID.');
    header('Location: students.php');
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE students SET status = 'Inactive' WHERE student_id = ? AND status = 'Active' AND role = 'Student'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();

    if ($changed > 0) {
        logAction('DEACTIVATE_STUDENT', 'Marked student ID ' . $id . ' as Inactive');
        flash_set('success', 'Student successfully marked as Inactive.');
    } else {
        flash_set('error', 'No active student was changed. The record may not exist or is already inactive.');
    }
} catch (mysqli_sql_exception $e) {
    error_log('Deactivate student failed: ' . $e->getMessage());
    flash_set('error', 'Could not deactivate the student.');
}

header('Location: students.php');
exit;
