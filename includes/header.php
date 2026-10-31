<?php
// Shared page header + navigation.
// Before including, a page may set:
//   $pageTitle  - text for the browser tab
//   $base       - '' (root pages) or '../' (pages inside a sub-folder)
//   $activeNav  - the nav link to highlight, e.g. 'students.php'
require_once __DIR__ . '/auth.php';

$base      = $base ?? '';
$pageTitle = $pageTitle ?? 'Student Hub Portal';
$activeNav = $activeNav ?? '';
$role      = is_logged_in() ? ($_SESSION['role'] ?? '') : '';

if ($role === 'Admin') {
    $nav = [
        ['Dashboard', 'admin_dashboard.php'],
        ['Manage Students', 'students.php'],
        ['Manage Events', 'events/index.php'],
        ['Manage Registrations', 'manage_registrations.php'],
        ['Audit Logs', 'practical14/audit_logs.php'],
        ['AJAX Demo', 'practical13/index.php'],
    ];
} elseif ($role === 'Student') {
    $nav = [
        ['Dashboard', 'student_dashboard.php'],
        ['View Events', 'events.php'],
        ['My Registrations', 'my_registrations.php'],
        ['Profile', 'profile.php'],
    ];
} else {
    $nav = [
        ['Home', 'index.php'],
        ['About', 'about.php'],
        ['Contact', 'contact.php'],
        ['Login', 'login.php'],
        ['Register', 'registration.php'],
    ];
}

$homeLink = $role === 'Admin' ? 'admin_dashboard.php' : ($role === 'Student' ? 'student_dashboard.php' : 'index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> - Student Hub</title>
    <link rel="stylesheet" href="<?= h($base) ?>assets/css/portal.css">
    <?php if (!empty($extraHead)) { echo $extraHead; } ?>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="site-header">
    <div class="nav-wrap">
        <a class="brand" href="<?= h($base . $homeLink) ?>">Student Hub</a>
        <button type="button" class="nav-toggle" id="navToggle"
                aria-expanded="false" aria-controls="navLinks">Menu</button>
        <nav aria-label="Main navigation" id="navWrap">
            <ul class="nav-links" id="navLinks">
                <?php foreach ($nav as $item): ?>
                    <li>
                        <a href="<?= h($base . $item[1]) ?>"
                           <?= $activeNav === $item[1] ? 'class="active" aria-current="page"' : '' ?>>
                            <?= h($item[0]) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if ($role !== ''): ?>
                    <li class="nav-logout"><a href="<?= h($base) ?>logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main id="main">
<?= flash_render() ?>
