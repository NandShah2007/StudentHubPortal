<?php
// Database connection for the WHOLE portal (one database: student_hub_db).
// $conn = MySQLi  (used by student pages, events, authentication)
// $pdo  = PDO     (used by registration, audit logs, AJAX API)
//
// XAMPP default login is root with an empty password.
// If you change your MySQL password, change it here.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$dbname = "student_hub_db";
$username = "root";
$password = "";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("MySQLi connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Is MySQL running and is the database imported?");
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("PDO connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Is MySQL running and is the database imported?");
}
