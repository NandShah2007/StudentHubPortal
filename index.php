<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

// Show the next 3 upcoming events on the home page.
$events = [];
try {
    $res = $conn->query(
        "SELECT event_id, title, event_date, location, category
         FROM events
         WHERE event_date >= CURDATE()
         ORDER BY event_date ASC
         LIMIT 3"
    );
    $events = $res->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    error_log('Home events query failed: ' . $e->getMessage());
}

$pageTitle = 'Home';
$activeNav = 'index.php';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <h1>Welcome to Student Hub Portal</h1>
    <p>Your college portal for student accounts, campus events and event registration &mdash; all in one place.</p>
    <div class="btn-row" style="justify-content:center;">
        <?php if (is_logged_in()): ?>
            <a class="btn secondary" href="dashboard.php">Go to my dashboard</a>
        <?php else: ?>
            <a class="btn secondary" href="registration.php">Create an account</a>
            <a class="btn" style="background:#A67C52;" href="login.php">Login</a>
        <?php endif; ?>
    </div>
</section>

<section aria-labelledby="features-title">
    <h2 id="features-title" class="center">What you can do</h2>
    <div class="grid grid-3">
        <article class="card">
            <h3>Student accounts</h3>
            <p>Register, log in securely and manage your own profile and password.</p>
        </article>
        <article class="card">
            <h3>Campus events</h3>
            <p>Browse workshops, competitions and seminars and register with one click.</p>
        </article>
        <article class="card">
            <h3>Admin tools</h3>
            <p>Administrators manage students, events and registrations and can review an audit log.</p>
        </article>
    </div>
</section>

<section aria-labelledby="upcoming-title">
    <h2 id="upcoming-title" class="center">Upcoming events</h2>
    <?php if (!$events): ?>
        <p class="center">No upcoming events right now. Please check again soon.</p>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($events as $ev): ?>
                <article class="event-card">
                    <div class="body">
                        <h3><?= h($ev['title']) ?></h3>
                        <p class="meta"><strong>Date:</strong> <?= h(date('d M Y', strtotime($ev['event_date']))) ?></p>
                        <p class="meta"><strong>Location:</strong> <?= h($ev['location']) ?></p>
                        <p class="meta"><span class="badge"><?= h($ev['category']) ?></span></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="center">
            <a class="btn" href="<?= is_logged_in() ? 'events.php' : 'login.php' ?>">
                <?= is_logged_in() ? 'View all events' : 'Login to register for events' ?>
            </a>
        </p>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
