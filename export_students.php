<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/practical14/log_action.php';
require_role('Admin');

$result = $conn->query(
    "SELECT student_id, full_name, username, email, department, status
     FROM students WHERE role = 'Student' ORDER BY student_id ASC"
);

// Stops spreadsheet formula injection when the CSV is opened in Excel.
function csv_safe($v): string
{
    $v = (string) $v;
    return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
}

logAction('EXPORT_STUDENTS', 'Exported student CSV');

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="student_records.csv"');
header('Cache-Control: no-store, no-cache, must-revalidate');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Student ID', 'Full Name', 'Username', 'Email', 'Course/Department', 'Status']);

while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['student_id'],
        csv_safe($row['full_name']),
        csv_safe($row['username']),
        csv_safe($row['email']),
        csv_safe($row['department']),
        $row['status']
    ]);
}
fclose($out);
exit;
