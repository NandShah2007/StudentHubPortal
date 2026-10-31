
CREATE DATABASE IF NOT EXISTS student_hub_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE student_hub_db;

CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(150) NOT NULL
);

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'Registered',

    CONSTRAINT fk_registration_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_registration_event
        FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON DELETE CASCADE,

    CONSTRAINT uq_student_event
        UNIQUE (student_id, event_id)
);

INSERT IGNORE INTO students
(student_id, full_name, email, department)
VALUES
(1, 'Aarav Patel', 'aarav@example.com', 'Computer Science'),
(2, 'Diya Shah', 'diya@example.com', 'Information Technology'),
(3, 'Rohan Mehta', 'rohan@example.com', 'Computer Science'),
(4, 'Anaya Desai', 'anaya@example.com', 'Business Administration'),
(5, 'Kabir Joshi', 'kabir@example.com', 'Information Technology');

INSERT IGNORE INTO events
(event_id, title, description, event_date, location)
VALUES
(1, 'Web Development Workshop', 'Learn HTML, CSS and PHP', '2026-11-10', 'Computer Lab'),
(2, 'Coding Competition', 'A student programming contest', '2026-11-15', 'Main Auditorium'),
(3, 'Tech Fest', 'Technology exhibitions and activities', '2026-11-20', 'College Campus'),
(4, 'Career Seminar', 'Career guidance for students', '2026-11-25', 'Seminar Hall');

INSERT IGNORE INTO registrations
(registration_id, student_id, event_id, status)
VALUES
(1, 1, 1, 'Registered'),
(2, 2, 1, 'Registered'),
(3, 3, 2, 'Registered'),
(4, 4, 3, 'Registered'),
(5, 5, 4, 'Registered'),
(6, 1, 3, 'Registered');
