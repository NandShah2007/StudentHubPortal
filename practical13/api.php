<?php
// Practical 13 JSON API (Admin only).
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function respond($success, $message, $data = null, $status = 200)
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// ---------- Security checks (server side) ----------
if (empty($_SESSION['student_id'])) {
    respond(false, 'Please log in first.', null, 401);
}
if (($_SESSION['last_activity'] ?? 0) < time() - SESSION_TIMEOUT) {
    respond(false, 'Your session expired. Please log in again.', null, 401);
}
if (($_SESSION['role'] ?? '') !== 'Admin') {
    respond(false, 'Forbidden: administrators only.', null, 403);
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/db.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // LIST students
    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT id, full_name, email, department, created_at
             FROM practical13_students
             ORDER BY id DESC'
        );
        respond(true, 'Students loaded successfully.', $stmt->fetchAll());
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        respond(false, 'Method not allowed.', null, 405);
    }

    // CSRF: every write must send the token in the X-CSRF-Token header
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        respond(false, 'Security token missing or invalid. Refresh the page.', null, 419);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(false, 'Invalid JSON request.', null, 400);
    }

    $action = $input['action'] ?? '';

    // ADD
    if ($action === 'add') {
        $name = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $department = trim($input['department'] ?? '');

        if ($name === '' || $email === '' || $department === '') {
            respond(false, 'All fields are required.', null, 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(false, 'Please enter a valid email address.', null, 422);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO practical13_students (full_name, email, department)
             VALUES (:name, :email, :department)'
        );
        $stmt->execute(['name' => $name, 'email' => $email, 'department' => $department]);

        respond(true, 'Student added successfully.', ['id' => (int) $pdo->lastInsertId()], 201);
    }

    // EDIT
    if ($action === 'edit') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $department = trim($input['department'] ?? '');

        if (!$id || $id < 1) {
            respond(false, 'Valid student ID is required.', null, 422);
        }
        if ($name === '' || $email === '' || $department === '') {
            respond(false, 'All fields are required.', null, 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(false, 'Please enter a valid email address.', null, 422);
        }

        $check = $pdo->prepare('SELECT id FROM practical13_students WHERE id = ?');
        $check->execute([$id]);
        if (!$check->fetch()) {
            respond(false, 'Student not found.', null, 404);
        }

        $stmt = $pdo->prepare(
            'UPDATE practical13_students
             SET full_name = :name, email = :email, department = :department
             WHERE id = :id'
        );
        $stmt->execute(['name' => $name, 'email' => $email, 'department' => $department, 'id' => $id]);

        respond(true, 'Student updated successfully.');
    }

    // DELETE
    if ($action === 'delete') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            respond(false, 'Valid student ID is required.', null, 422);
        }

        $stmt = $pdo->prepare('DELETE FROM practical13_students WHERE id = ?');
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            respond(false, 'Student not found.', null, 404);
        }
        respond(true, 'Student deleted successfully.');
    }

    respond(false, 'Unknown action.', null, 400);

} catch (PDOException $e) {
    error_log($e->getMessage());
    if ($e->getCode() === '23000') {
        respond(false, 'This email address is already registered.', null, 409);
    }
    respond(false, 'A database error occurred.', null, 500);
} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(false, 'An unexpected server error occurred.', null, 500);
}
