<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$isStudent = ($_SESSION['role'] ?? '') === 'Student';
$studentId = (int) $_SESSION['student_id'];

// ---------- Register for an event (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (!$isStudent) {
        show_403();
    }

    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

    if (!$eventId || $eventId < 1) {
        flash_set('error', 'Invalid event.');
        header('Location: events.php');
        exit;
    }

    try {
        // The event must exist and must not be in the past.
        $stmt = $conn->prepare('SELECT event_id, title FROM events WHERE event_id = ? AND event_date >= CURDATE()');
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) {
            flash_set('error', 'That event does not exist or has already finished.');
            header('Location: events.php');
            exit;
        }

        $stmt = $conn->prepare('SELECT registration_id, status FROM registrations WHERE student_id = ? AND event_id = ?');
        $stmt->bind_param('ii', $studentId, $eventId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing && $existing['status'] === 'Registered') {
            flash_set('error', 'You are already registered for this event.');
        } elseif ($existing) {
            // Re-register after a cancellation.
            $stmt = $conn->prepare("UPDATE registrations SET status = 'Registered', registered_at = NOW() WHERE registration_id = ?");
            $rid = (int) $existing['registration_id'];
            $stmt->bind_param('i', $rid);
            $stmt->execute();
            $stmt->close();
            flash_set('success', 'You are registered for "' . $event['title'] . '" again.');
        } else {
            $stmt = $conn->prepare("INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'Registered')");
            $stmt->bind_param('ii', $studentId, $eventId);
            $stmt->execute();
            $stmt->close();
            flash_set('success', 'Registration successful for "' . $event['title'] . '".');
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Event registration failed: ' . $e->getMessage());
        flash_set('error', 'Could not complete the registration. Please try again.');
    }

    header('Location: events.php');
    exit;
}

// ---------- List events (GET) ----------
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$cats = $conn->query("SELECT DISTINCT category FROM events WHERE category <> '' ORDER BY category")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT e.event_id, e.title, e.description, e.event_date, e.location, e.category, e.poster,
               r.status AS reg_status
        FROM events e
        LEFT JOIN registrations r ON r.event_id = e.event_id AND r.student_id = ?
        WHERE (e.title LIKE ? OR e.location LIKE ?)";
$types  = 'iss';
$term   = '%' . $search . '%';
$params = [$studentId, $term, $term];

if ($category !== '') {
    $sql .= ' AND e.category = ?';
    $types .= 's';
    $params[] = $category;
}
$sql .= ' ORDER BY e.event_date ASC';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$today = date('Y-m-d');
$pageTitle = 'Events';
$activeNav = 'events.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Campus Events</h1>

<form method="GET" action="events.php" class="filter-bar">
    <div>
        <label for="search">Search title or location</label>
        <input type="text" id="search" name="search" value="<?= h($search) ?>" maxlength="100">
    </div>
    <div>
        <label for="category">Category</label>
        <select id="category" name="category">
            <option value="">All categories</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= h($c['category']) ?>" <?= $category === $c['category'] ? 'selected' : '' ?>><?= h($c['category']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <button type="submit" class="btn">Search</button>
        <a class="btn secondary" href="events.php">Reset</a>
    </div>
</form>

<?php if (!$events): ?>
    <div class="alert info">No events found.</div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($events as $ev):
            $past = $ev['event_date'] < $today; ?>
            <article class="event-card">
                <?php if (!empty($ev['poster'])): ?>
                    <img class="poster" src="events/uploads/<?= h($ev['poster']) ?>" alt="Poster for <?= h($ev['title']) ?>">
                <?php else: ?>
                    <div class="poster-empty" aria-hidden="true">&#127979;</div>
                <?php endif; ?>
                <div class="body">
                    <h2 style="font-size:1.2rem;margin:0 0 6px;"><?= h($ev['title']) ?></h2>
                    <p class="meta"><span class="badge"><?= h($ev['category']) ?></span></p>
                    <p class="meta"><strong>Date:</strong> <?= h(date('d M Y', strtotime($ev['event_date']))) ?></p>
                    <p class="meta"><strong>Location:</strong> <?= h($ev['location']) ?></p>
                    <?php if (!empty($ev['description'])): ?>
                        <p><?= h($ev['description']) ?></p>
                    <?php endif; ?>

                    <div class="actions">
                        <?php if ($past): ?>
                            <span class="badge">Event finished</span>
                        <?php elseif ($ev['reg_status'] === 'Registered'): ?>
                            <span class="badge ok">You are registered</span>
                        <?php elseif ($isStudent): ?>
                            <form method="POST" action="events.php" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="event_id" value="<?= (int) $ev['event_id'] ?>">
                                <button type="submit" class="btn small">
                                    <?= $ev['reg_status'] === 'Cancelled' ? 'Register again' : 'Register' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge">Admins cannot register</span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
