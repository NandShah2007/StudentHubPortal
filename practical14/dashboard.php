<?php
// Practical 14 dashboard URL kept for compatibility.
// The statistics now live on the main Admin Dashboard.
require_once __DIR__ . '/rbac.php';
requireAdmin();
header('Location: ../admin_dashboard.php');
exit;
