<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_role('Student');

$studentId = (int) $_SESSION['student_id'];

// ---------- Cancel a registration (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $regId = filter_input(INPUT_POST, 'registration_id', FILTER_VALIDATE_INT);

    if ($regId && $regId > 0) {
        try {
            // student_id in the WHERE clause: a student can only cancel their OWN registration.
            $stmt = $conn->prepare(
                "UPDATE registrations SET status = 'Cancelled'
                 WHERE registration_id = ? AND student_id = ? AND status = 'Registered'"
            );
            $stmt->bind_param('ii', $regId, $studentId);
            $stmt->execute();
            $changed = $stmt->affected_rows;
            $stmt->close();

            if ($changed > 0) {
                flash_set('success', 'Your registration was cancelled.');
            } else {
                flash_set('error', 'Nothing was changed. The registration may not exist or is already cancelled.');
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Cancel registration failed: ' . $e->getMessage());
            flash_set('error', 'Could not cancel the registration.');
        }
    } else {
        flash_set('error', 'Invalid registration.');
    }

    header('Location: my_registrations.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT r.registration_id, r.registered_at, r.status, e.title, e.event_date, e.location, e.category
     FROM registrations r
     INNER JOIN events e ON e.event_id = r.event_id
     WHERE r.student_id = ?
     ORDER BY e.event_date ASC"
);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$today = date('Y-m-d');
$pageTitle = 'My Registrations';
$activeNav = 'my_registrations.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">My Registrations</h1>

<?php if (!$rows): ?>
    <div class="alert info">You have not registered for any event yet. <a href="events.php">Browse events</a>.</div>
<?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table class="data">
                <caption class="hint" style="text-align:left;caption-side:top;">Your event registrations</caption>
                <thead>
                    <tr><th>Event</th><th>Category</th><th>Date</th><th>Location</th><th>Registered on</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= h($r['title']) ?></td>
                        <td><?= h($r['category']) ?></td>
                        <td><?= h(date('d M Y', strtotime($r['event_date']))) ?></td>
                        <td><?= h($r['location']) ?></td>
                        <td><?= h($r['registered_at']) ?></td>
                        <td><span class="badge <?= $r['status'] === 'Registered' ? 'ok' : 'err' ?>"><?= h($r['status']) ?></span></td>
                        <td>
                            <?php if ($r['status'] === 'Registered' && $r['event_date'] >= $today): ?>
                                <form method="POST" action="my_registrations.php" class="inline-form"
                                      onsubmit="return confirm('Cancel this registration?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="registration_id" value="<?= (int) $r['registration_id'] ?>">
                                    <button type="submit" class="btn danger small">Cancel</button>
                                </form>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
