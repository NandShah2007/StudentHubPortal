<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'About';
$activeNav = 'about.php';
require __DIR__ . '/includes/header.php';
?>
<section class="card medium">
    <h1 class="page-title">About Student Hub Portal</h1>
    <p class="lead">Student Hub Portal is a college project that brings student management and event management together in one secure website.</p>

    <h2>Main features</h2>
    <ul>
        <li>Student registration, login, logout and automatic session timeout.</li>
        <li>Student dashboard, event list, event registration and "My Registrations".</li>
        <li>Admin dashboard with live statistics from the database.</li>
        <li>Admin tools: manage students, manage events (with poster upload) and manage registrations.</li>
        <li>AJAX student manager (Fetch API with a PHP JSON API).</li>
        <li>Role-based access control and an audit log of administrator actions.</li>
    </ul>

    <h2>Technology</h2>
    <p>PHP 8, MySQL / MariaDB, HTML, CSS and JavaScript, running on XAMPP (Apache).</p>

    <h2>Roles</h2>
    <p><strong>Student</strong> &mdash; can view events, register for events and manage their own profile.<br>
       <strong>Admin</strong> &mdash; can manage students, events, registrations and view audit logs.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
