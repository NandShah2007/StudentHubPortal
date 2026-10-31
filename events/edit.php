<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/../practical14/log_action.php';

$categories = ['General', 'Workshop', 'Competition', 'Seminar', 'Festival'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
}
if (!$id || $id < 1) {
    flash_set('error', 'Invalid event.');
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT event_id, title, description, event_date, location, category, poster FROM events WHERE event_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    flash_set('error', 'Event not found.');
    header('Location: index.php');
    exit;
}

$error = '';

if (isset($_POST['update'])) {
    csrf_check();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['event_date'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $category    = trim($_POST['category'] ?? 'General');
    $d = DateTime::createFromFormat('Y-m-d', $date);

    if ($title === '' || $location === '' || $date === '') {
        $error = 'Title, date and location are required.';
    } elseif (!$d || $d->format('Y-m-d') !== $date) {
        $error = 'Please enter a valid date.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Invalid category.';
    } else {
        list($newPoster, $uploadError) = upload_poster($_FILES['poster'] ?? null);
        if ($uploadError) {
            $error = $uploadError;
        } else {
            $poster = $newPoster ?: $event['poster'];   // keep the old poster if no new file
            try {
                $stmt = $conn->prepare(
                    'UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, category = ?, poster = ? WHERE event_id = ?'
                );
                $stmt->bind_param('ssssssi', $title, $description, $date, $location, $category, $poster, $id);
                $stmt->execute();
                $stmt->close();

                if ($newPoster) {
                    delete_poster($event['poster']);
                }
                logAction('EDIT_EVENT', 'Updated event ID ' . $id);
                flash_set('success', 'Event updated.');
                header('Location: index.php');
                exit;
            } catch (mysqli_sql_exception $e) {
                error_log('Update event failed: ' . $e->getMessage());
                delete_poster($newPoster);
                $error = 'Could not update the event.';
            }
        }
    }

    $event['title'] = $title;
    $event['description'] = $description;
    $event['event_date'] = $date;
    $event['location'] = $location;
    $event['category'] = $category;
}

$base = '../';
$pageTitle = 'Edit Event';
$activeNav = 'events/index.php';
require __DIR__ . '/../includes/header.php';
?>
<section class="card medium">
    <h1 class="page-title">Edit Event</h1>
    <?php if ($error !== ''): ?><div class="alert error" role="alert"><?= h($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="edit.php">
        <?= csrf_field() ?>
        <input type="hidden" name="event_id" value="<?= (int) $event['event_id'] ?>">

        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= h($event['title']) ?>" maxlength="150" required>

        <label for="event_date">Date</label>
        <input type="date" id="event_date" name="event_date" value="<?= h($event['event_date']) ?>" required>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= h($event['location']) ?>" maxlength="150" required>

        <label for="category">Category</label>
        <select id="category" name="category">
            <?php foreach ($categories as $c): ?>
                <option value="<?= h($c) ?>" <?= $event['category'] === $c ? 'selected' : '' ?>><?= h($c) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="1000"><?= h($event['description'] ?? '') ?></textarea>

        <?php if (!empty($event['poster'])): ?>
            <p>Current poster:<br><img src="uploads/<?= h($event['poster']) ?>" alt="Current poster" style="max-width:160px;border-radius:6px;"></p>
        <?php endif; ?>

        <label for="poster">Replace poster (optional)</label>
        <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <p class="hint">JPG, PNG or WEBP, maximum 2 MB.</p>

        <div class="btn-row">
            <button type="submit" name="update" value="1" class="btn">Save changes</button>
            <a href="index.php" class="btn secondary">Cancel</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
