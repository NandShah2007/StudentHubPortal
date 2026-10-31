<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_role('Student');

$studentId = (int) $_SESSION['student_id'];

// Student profile
$stmt = $conn->prepare('SELECT full_name, email, department, username FROM students WHERE student_id = ?');
$stmt->bind_param('i', $studentId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Counts
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM registrations WHERE student_id = ? AND status = 'Registered'");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$myCount = (int) $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$upcomingTotal = (int) $conn->query('SELECT COUNT(*) AS c FROM events WHERE event_date >= CURDATE()')->fetch_assoc()['c'];

// Next 3 registered events
$stmt = $conn->prepare(
    "SELECT e.title, e.event_date, e.location
     FROM registrations r
     INNER JOIN events e ON e.event_id = r.event_id
     WHERE r.student_id = ? AND r.status = 'Registered' AND e.event_date >= CURDATE()
     ORDER BY e.event_date ASC
     LIMIT 3"
);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$next = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$showLogin = !empty($_SESSION['login_success']);
unset($_SESSION['login_success']);

$pageTitle = 'Student Dashboard';
$activeNav = 'student_dashboard.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Student Dashboard</h1>
<?php if ($showLogin): ?>
    <div class="alert success" role="status">Login successful. Welcome back!</div>
<?php endif; ?>

<section class="card">
    <h2>Welcome, <?= h($me['full_name'] ?? $_SESSION['full_name']) ?>!</h2>
    <p><strong>Email:</strong> <?= h($me['email'] ?? '') ?><br>
       <strong>Department:</strong> <?= h($me['department'] ?? '') ?><br>
       <strong>Role:</strong> Student</p>
</section>

<div class="grid grid-2">
    <div class="stat"><div class="num"><?= $myCount ?></div><div class="label">My active registrations</div></div>
    <div class="stat"><div class="num"><?= $upcomingTotal ?></div><div class="label">Upcoming events on campus</div></div>
</div>

<section class="card" style="margin-top:22px;">
    <h2>My next events</h2>
    <?php if (!$next): ?>
        <p>You have not registered for any upcoming event yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Event</th><th>Date</th><th>Location</th></tr></thead>
                <tbody>
                <?php foreach ($next as $row): ?>
                    <tr>
                        <td><?= h($row['title']) ?></td>
                        <td><?= h(date('d M Y', strtotime($row['event_date']))) ?></td>
                        <td><?= h($row['location']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <div class="btn-row">
        <a class="btn" href="events.php">View Events</a>
        <a class="btn secondary" href="my_registrations.php">My Registrations</a>
        <a class="btn secondary" href="profile.php">My Profile</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
