<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('Admin', '../');

$base = '../';
$pageTitle = 'AJAX Demo (Practical 13)';
$activeNav = 'practical13/index.php';
$extraHead = '<meta name="csrf-token" content="' . h(csrf_token()) . '">';
$extraScripts = '<script src="script.js"></script>';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">AJAX Student Manager</h1>
<p class="lead">Practical 13: add, edit and delete records without reloading the page (Fetch API + PHP JSON API).</p>

<section class="card">
    <h2 id="formTitle">Add Student</h2>

    <form id="studentForm" class="ajax-form">
        <div>
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" placeholder="Full name" maxlength="100" required>
        </div>
        <div>
            <label for="email">Email address</label>
            <input type="email" id="email" placeholder="Email address" maxlength="150" required>
        </div>
        <div>
            <label for="department">Department</label>
            <input type="text" id="department" placeholder="Department" maxlength="100" required>
        </div>
        <div class="btn-row">
            <button type="submit" id="saveButton" class="btn">Add Student</button>
            <button type="button" id="cancelButton" class="btn secondary" hidden>Cancel Edit</button>
        </div>
    </form>

    <div id="message" role="status" aria-live="polite"></div>
</section>

<section class="card">
    <h2>Student List</h2>
    <p id="loading" hidden>Loading students...</p>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr>
            </thead>
            <tbody id="studentTable">
                <tr><td colspan="5">Loading students...</td></tr>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
