<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/practical14/log_action.php';
require_role('Admin');

// ---------- Change status / delete (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $regId  = filter_input(INPUT_POST, 'registration_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if (!$regId || $regId < 1) {
        flash_set('error', 'Invalid registration.');
    } else {
        try {
            if ($action === 'cancel' || $action === 'restore') {
                $newStatus = $action === 'cancel' ? 'Cancelled' : 'Registered';
                $stmt = $conn->prepare('UPDATE registrations SET status = ? WHERE registration_id = ?');
                $stmt->bind_param('si', $newStatus, $regId);
                $stmt->execute();
                $stmt->close();
                logAction('REGISTRATION_' . strtoupper($newStatus), 'Registration ID ' . $regId . ' set to ' . $newStatus);
                flash_set('success', 'Registration status updated to ' . $newStatus . '.');
            } elseif ($action === 'delete') {
                $stmt = $conn->prepare('DELETE FROM registrations WHERE registration_id = ?');
                $stmt->bind_param('i', $regId);
                $stmt->execute();
                $stmt->close();
                logAction('DELETE_REGISTRATION', 'Deleted registration ID ' . $regId);
                flash_set('success', 'Registration deleted.');
            } else {
                flash_set('error', 'Unknown action.');
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Registration update failed: ' . $e->getMessage());
            flash_set('error', 'Could not update the registration.');
        }
    }

    header('Location: manage_registrations.php');
    exit;
}

// ---------- List ----------
$eventFilter = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT) ?: 0;
$events = $conn->query('SELECT event_id, title FROM events ORDER BY event_date ASC')->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT r.registration_id, r.registered_at, r.status,
               s.full_name, s.email, e.title, e.event_date
        FROM registrations r
        INNER JOIN students s ON s.student_id = r.student_id
        INNER JOIN events e ON e.event_id = r.event_id";
if ($eventFilter > 0) {
    $sql .= ' WHERE r.event_id = ?';
}
$sql .= ' ORDER BY r.registered_at DESC, r.registration_id DESC';

$stmt = $conn->prepare($sql);
if ($eventFilter > 0) {
    $stmt->bind_param('i', $eventFilter);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Manage Registrations';
$activeNav = 'manage_registrations.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Manage Registrations</h1>

<form method="GET" action="manage_registrations.php" class="filter-bar">
    <div>
        <label for="event_id">Filter by event</label>
        <select id="event_id" name="event_id">
            <option value="0">All events</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?= (int) $ev['event_id'] ?>" <?= $eventFilter === (int) $ev['event_id'] ? 'selected' : '' ?>><?= h($ev['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <button type="submit" class="btn">Filter</button>
        <a class="btn secondary" href="manage_registrations.php">Reset</a>
    </div>
</form>

<p><strong>Records found:</strong> <?= count($rows) ?></p>

<div class="card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>ID</th><th>Student</th><th>Email</th><th>Event</th><th>Event date</th><th>Registered at</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="8">No registrations found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int) $r['registration_id'] ?></td>
                        <td><?= h($r['full_name']) ?></td>
                        <td><?= h($r['email']) ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td><?= h(date('d M Y', strtotime($r['event_date']))) ?></td>
                        <td><?= h($r['registered_at']) ?></td>
                        <td><span class="badge <?= $r['status'] === 'Registered' ? 'ok' : 'err' ?>"><?= h($r['status']) ?></span></td>
                        <td>
                            <form method="POST" action="manage_registrations.php" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="registration_id" value="<?= (int) $r['registration_id'] ?>">
                                <?php if ($r['status'] === 'Registered'): ?>
                                    <button type="submit" name="action" value="cancel" class="btn secondary small">Cancel</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="restore" class="btn small">Restore</button>
                                <?php endif; ?>
                            </form>
                            <form method="POST" action="manage_registrations.php" class="inline-form"
                                  onsubmit="return confirm('Permanently delete this registration?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="registration_id" value="<?= (int) $r['registration_id'] ?>">
                                <button type="submit" name="action" value="delete" class="btn danger small">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
