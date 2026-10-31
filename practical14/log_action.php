<?php
// Practical 14: writes one row into the audit_logs table.
// Use:  logAction('ADD_STUDENT', 'Added student ID: 5');
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../db.php';

function logAction(string $action, string $details = ''): void
{
    global $pdo;

    // Only administrator actions are recorded.
    if (empty($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (user_id, user_name, action, details, ip_address)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $_SESSION['student_id'],
            $_SESSION['full_name'] ?? 'Admin',
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (PDOException $e) {
        // Logging must never break the page the admin is using.
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
