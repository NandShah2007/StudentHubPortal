<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_role('Admin');

function count_of(mysqli $conn, string $sql): int
{
    return (int) $conn->query($sql)->fetch_assoc()['c'];
}

$totalStudents  = count_of($conn, "SELECT COUNT(*) AS c FROM students WHERE role = 'Student'");
$activeStudents = count_of($conn, "SELECT COUNT(*) AS c FROM students WHERE role = 'Student' AND status = 'Active'");
$totalEvents    = count_of($conn, 'SELECT COUNT(*) AS c FROM events');
$totalRegs      = count_of($conn, "SELECT COUNT(*) AS c FROM registrations WHERE status = 'Registered'");
$totalLogs      = count_of($conn, 'SELECT COUNT(*) AS c FROM audit_logs');
$totalAdmins    = count_of($conn, "SELECT COUNT(*) AS c FROM students WHERE role = 'Admin'");

$recent = $conn->query(
    "SELECT r.registration_id, s.full_name, e.title, r.registered_at, r.status
     FROM registrations r
     INNER JOIN students s ON s.student_id = r.student_id
     INNER JOIN events e ON e.event_id = r.event_id
     ORDER BY r.registered_at DESC, r.registration_id DESC
     LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

$showLogin = !empty($_SESSION['login_success']);
unset($_SESSION['login_success']);

$pageTitle = 'Admin Dashboard';
$activeNav = 'admin_dashboard.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Admin Dashboard</h1>
<?php if ($showLogin): ?>
    <div class="alert success" role="status">Admin login successful.</div>
<?php endif; ?>
<p class="lead">Welcome, <?= h($_SESSION['full_name']) ?>. Live statistics from the database:</p>

<div class="grid grid-4">
    <div class="stat"><div class="num"><?= $totalStudents ?></div><div class="label">Students (<?= $activeStudents ?> active)</div></div>
    <div class="stat"><div class="num"><?= $totalEvents ?></div><div class="label">Events</div></div>
    <div class="stat"><div class="num"><?= $totalRegs ?></div><div class="label">Active registrations</div></div>
    <div class="stat"><div class="num"><?= $totalLogs ?></div><div class="label">Audit log entries</div></div>
</div>

<section class="card" style="margin-top:22px;">
    <h2>Quick actions</h2>
    <div class="btn-row">
        <a class="btn" href="students.php">Manage Students</a>
        <a class="btn" href="events/index.php">Manage Events</a>
        <a class="btn" href="manage_registrations.php">Manage Registrations</a>
        <a class="btn" href="practical14/audit_logs.php">Audit Logs</a>
        <a class="btn secondary" href="practical13/index.php">AJAX Demo</a>
    </div>
    <p class="hint">Administrator accounts: <?= $totalAdmins ?></p>
</section>

<section class="card">
    <h2>Recent registrations</h2>
    <?php if (!$recent): ?>
        <p>No registrations yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>ID</th><th>Student</th><th>Event</th><th>Registered at</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><?= (int) $r['registration_id'] ?></td>
                        <td><?= h($r['full_name']) ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td><?= h($r['registered_at']) ?></td>
                        <td><span class="badge <?= $r['status'] === 'Registered' ? 'ok' : 'err' ?>"><?= h($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
