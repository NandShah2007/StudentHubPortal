<?php
// Sends each user to the correct dashboard for their role.
require_once __DIR__ . '/includes/auth.php';
require_login();

if (($_SESSION['role'] ?? '') === 'Admin') {
    header('Location: admin_dashboard.php');
    exit;
}
if (($_SESSION['role'] ?? '') === 'Student') {
    header('Location: student_dashboard.php');
    exit;
}

destroy_session();
header('Location: login.php?denied=1');
exit;
