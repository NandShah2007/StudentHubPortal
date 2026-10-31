<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/../practical14/log_action.php';

$categories = ['General', 'Workshop', 'Competition', 'Seminar', 'Festival'];
$error = '';
$title = $date = $location = $description = '';
$category = 'General';

// ---------- CREATE ----------
if (isset($_POST['add'])) {
    csrf_check();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['event_date'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $category    = trim($_POST['category'] ?? 'General');

    $d = DateTime::createFromFormat('Y-m-d', $date);

    if ($title === '' || $location === '' || $date === '') {
        $error = 'Title, date and location are required.';
    } elseif (mb_strlen($title) > 150 || mb_strlen($location) > 150) {
        $error = 'Title and location must be 150 characters or fewer.';
    } elseif (!$d || $d->format('Y-m-d') !== $date) {
        $error = 'Please enter a valid date.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Invalid category.';
    } else {
        list($poster, $uploadError) = upload_poster($_FILES['poster'] ?? null);
        if ($uploadError) {
            $error = $uploadError;
        } else {
            try {
                $stmt = $conn->prepare(
                    'INSERT INTO events (title, description, event_date, location, category, poster) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('ssssss', $title, $description, $date, $location, $category, $poster);
                $stmt->execute();
                $newId = $conn->insert_id;
                $stmt->close();

                logAction('ADD_EVENT', 'Added event ID ' . $newId . ': ' . $title);
                flash_set('success', 'Event added successfully.');
                header('Location: index.php');
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log('Add event failed: ' . $e->getMessage());
                delete_poster($poster);
                $error = 'Could not save the event. Please try again.';
            }
        }
    }
}

// ---------- DELETE (POST only) ----------
if (isset($_POST['delete'])) {
    csrf_check();
    $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        try {
            $stmt = $conn->prepare('SELECT title, poster FROM events WHERE event_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row) {
                $stmt = $conn->prepare('DELETE FROM events WHERE event_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();

                delete_poster($row['poster']);
                logAction('DELETE_EVENT', 'Deleted event ID ' . $id . ': ' . $row['title']);
                flash_set('success', 'Event deleted (its registrations were removed too).');
            } else {
                flash_set('error', 'Event not found.');
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Delete event failed: ' . $e->getMessage());
            flash_set('error', 'Could not delete the event.');
        }
    } else {
        flash_set('error', 'Invalid event.');
    }
    header('Location: index.php');
    exit;
}

// ---------- READ (search + category filter) ----------
$search = trim($_GET['search'] ?? '');
$cat    = trim($_GET['category'] ?? '');

$sql = 'SELECT event_id, title, event_date, location, category, poster,
               (SELECT COUNT(*) FROM registrations r WHERE r.event_id = events.event_id AND r.status = \'Registered\') AS reg_count
        FROM events WHERE (title LIKE ? OR location LIKE ?)';
$term = '%' . $search . '%';
$types = 'ss';
$params = [$term, $term];
if ($cat !== '') {
    $sql .= ' AND category = ?';
    $types .= 's';
    $params[] = $cat;
}
$sql .= ' ORDER BY event_date DESC, event_id DESC';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$base = '../';
$pageTitle = 'Manage Events';
$activeNav = 'events/index.php';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Event Management</h1>

<section class="card">
    <h2>Add event</h2>
    <?php if ($error !== ''): ?><div class="alert error" role="alert"><?= h($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="index.php">
        <?= csrf_field() ?>
        <div class="grid grid-2">
            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= h($title) ?>" maxlength="150" required>
            </div>
            <div>
                <label for="event_date">Date</label>
                <input type="date" id="event_date" name="event_date" value="<?= h($date) ?>" required>
            </div>
            <div>
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= h($location) ?>" maxlength="150" required>
            </div>
            <div>
                <label for="category">Category</label>
                <select id="category" name="category">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= h($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <label for="description">Description (optional)</label>
        <textarea id="description" name="description" rows="3" maxlength="1000"><?= h($description) ?></textarea>

        <label for="poster">Poster image (optional)</label>
        <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-describedby="posterHint">
        <p class="hint" id="posterHint">JPG, PNG or WEBP, maximum 2 MB.</p>

        <button type="submit" name="add" value="1" class="btn block">Add Event</button>
    </form>
</section>

<section class="card">
    <h2>All events</h2>
    <form method="GET" action="index.php" class="filter-bar">
        <div>
            <label for="search">Search title or location</label>
            <input type="text" id="search" name="search" value="<?= h($search) ?>" maxlength="100">
        </div>
        <div>
            <label for="fcat">Category</label>
            <select id="fcat" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= h($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn">Search</button>
            <a class="btn secondary" href="index.php">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>Poster</th><th>Title</th><th>Date</th><th>Location</th><th>Category</th><th>Registered</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$events): ?>
                <tr><td colspan="7">No events found.</td></tr>
            <?php else: ?>
                <?php foreach ($events as $ev): ?>
                    <tr>
                        <td>
                            <?php if (!empty($ev['poster'])): ?>
                                <img class="thumb" src="uploads/<?= h($ev['poster']) ?>" alt="Poster for <?= h($ev['title']) ?>">
                            <?php else: ?>&mdash;<?php endif; ?>
                        </td>
                        <td><?= h($ev['title']) ?></td>
                        <td><?= h(date('d M Y', strtotime($ev['event_date']))) ?></td>
                        <td><?= h($ev['location']) ?></td>
                        <td><?= h($ev['category']) ?></td>
                        <td><?= (int) $ev['reg_count'] ?></td>
                        <td>
                            <a class="btn small" href="edit.php?id=<?= (int) $ev['event_id'] ?>">Edit</a>
                            <form method="POST" action="index.php" class="inline-form"
                                  onsubmit="return confirm('Delete this event and all its registrations?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="event_id" value="<?= (int) $ev['event_id'] ?>">
                                <button type="submit" name="delete" value="1" class="btn danger small">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
