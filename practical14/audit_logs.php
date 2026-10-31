<?php
require_once __DIR__ . '/rbac.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

$logs = $pdo->query(
    'SELECT id, user_id, user_name, action, details, ip_address, created_at
     FROM audit_logs
     ORDER BY created_at DESC, id DESC
     LIMIT 100'
)->fetchAll();

$base = '../';
$pageTitle = 'Audit Logs';
$activeNav = 'practical14/audit_logs.php';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Audit Logs</h1>
<p class="lead">The latest 100 recorded administrator actions.</p>

<section class="card">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>IP address</th><th>Date and time</th></tr>
            </thead>
            <tbody>
            <?php if (!$logs): ?>
                <tr><td colspan="6" style="text-align:center;">No audit records yet. Add, edit or deactivate a student to create one.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= (int) $log['id'] ?></td>
                        <td><?= h($log['user_name']) ?></td>
                        <td><span class="badge"><?= h($log['action']) ?></span></td>
                        <td><?= h($log['details'] ?? '') ?></td>
                        <td><?= h($log['ip_address'] ?? '') ?></td>
                        <td><?= h($log['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
