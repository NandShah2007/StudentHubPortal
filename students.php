<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';
require_role('Admin');

$search     = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$status     = trim($_GET['status'] ?? 'Active');
if (!in_array($status, ['Active', 'Inactive', 'All'], true)) {
    $status = 'Active';
}

$courses = $conn->query("SELECT DISTINCT department FROM students WHERE department <> '' ORDER BY department")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT student_id, full_name, username, email, department, role, status FROM students WHERE 1 = 1";
$params = [];
$types = '';

if ($status !== 'All') {
    $sql .= ' AND status = ?';
    $params[] = $status;
    $types .= 's';
}
if ($search !== '') {
    $sql .= ' AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
    $types .= 'sss';
}
if ($department !== '') {
    $sql .= ' AND department = ?';
    $params[] = $department;
    $types .= 's';
}
$sql .= ' ORDER BY student_id DESC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Manage Students';
$activeNav = 'students.php';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Student Management</h1>

<div class="btn-row">
    <a class="btn" href="add_student.php">+ Add New Student</a>
    <a class="btn secondary" href="export_students.php">Export CSV</a>
</div>

<form method="GET" action="students.php" class="filter-bar">
    <div>
        <label for="search">Search name, email or username</label>
        <input type="text" id="search" name="search" value="<?= h($search) ?>" maxlength="100">
    </div>
    <div>
        <label for="department">Course / Department</label>
        <select id="department" name="department">
            <option value="">All courses</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= h($c['department']) ?>" <?= $department === $c['department'] ? 'selected' : '' ?>><?= h($c['department']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (['Active', 'Inactive', 'All'] as $s): ?>
                <option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s === 'All' ? 'All statuses' : h($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <button type="submit" class="btn">Search / Filter</button>
        <a class="btn secondary" href="students.php">Reset</a>
    </div>
</form>

<p><strong>Records found:</strong> <?= count($students) ?></p>

<div class="card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>ID</th><th>Full name</th><th>Username</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$students): ?>
                <tr><td colspan="8">No student records found.</td></tr>
            <?php else: ?>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= (int) $s['student_id'] ?></td>
                        <td><?= h($s['full_name']) ?></td>
                        <td><?= h($s['username']) ?></td>
                        <td><?= h($s['email']) ?></td>
                        <td><?= h($s['department']) ?></td>
                        <td><?= h($s['role']) ?></td>
                        <td><span class="badge <?= $s['status'] === 'Active' ? 'ok' : 'err' ?>"><?= h($s['status']) ?></span></td>
                        <td>
                            <a class="btn small" href="edit_student.php?id=<?= (int) $s['student_id'] ?>">Edit</a>
                            <?php if ($s['status'] === 'Active' && $s['role'] === 'Student'): ?>
                                <form method="POST" action="delete_student.php" class="inline-form"
                                      onsubmit="return confirm('Deactivate this student? The record is kept but the status becomes Inactive.');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="student_id" value="<?= (int) $s['student_id'] ?>">
                                    <button type="submit" class="btn danger small">Deactivate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
