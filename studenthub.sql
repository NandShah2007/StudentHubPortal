-- ============================================================
-- StudentHub Portal - complete database export (Practical 15)
-- Database name to use: student_hub_db
--
-- HOW TO IMPORT (phpMyAdmin):
--   1. Click "New", create a database named  student_hub_db
--      (collation utf8mb4_unicode_ci).
--   2. Click that database, open the "Import" tab, choose this file, click Go.
--
-- WARNING: this file DROPS and re-creates the portal tables.
--          Do not import it into a database that holds data you want to keep.
--
-- DEMO LOGINS (fake data only, change them for real use):
--   Admin   : admin@studenthub.local   / Admin@12345
--   Student : student@studenthub.local / Student@12345
--   The 5 "sample" students (ids 3-7) have no password, so they cannot log in.
--   They only exist so the event registration lists have data.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS practical13_students;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;

-- ---------- students (users of the portal) ----------
CREATE TABLE students (
  student_id    INT(11) NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  department    VARCHAR(100) NOT NULL,
  username      VARCHAR(50) DEFAULT NULL,
  password_hash VARCHAR(255) DEFAULT NULL,
  role          VARCHAR(20) NOT NULL DEFAULT 'Student',
  status        VARCHAR(20) NOT NULL DEFAULT 'Active',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (student_id),
  UNIQUE KEY email (email),
  UNIQUE KEY uq_students_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- events (Practical 12 now lives in this database) ----------
CREATE TABLE events (
  event_id    INT(11) NOT NULL AUTO_INCREMENT,
  title       VARCHAR(150) NOT NULL,
  description TEXT DEFAULT NULL,
  event_date  DATE NOT NULL,
  location    VARCHAR(150) NOT NULL,
  category    VARCHAR(50) NOT NULL DEFAULT 'General',
  poster      VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- registrations (student <-> event) ----------
CREATE TABLE registrations (
  registration_id INT(11) NOT NULL AUTO_INCREMENT,
  student_id      INT(11) NOT NULL,
  event_id        INT(11) NOT NULL,
  registered_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status          VARCHAR(20) NOT NULL DEFAULT 'Registered',
  PRIMARY KEY (registration_id),
  UNIQUE KEY uq_student_event (student_id, event_id),
  KEY fk_registration_event (event_id),
  CONSTRAINT fk_registration_student FOREIGN KEY (student_id) REFERENCES students (student_id) ON DELETE CASCADE,
  CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events (event_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- audit_logs (Practical 14) ----------
CREATE TABLE audit_logs (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  user_id    INT(11) DEFAULT NULL,
  user_name  VARCHAR(150) NOT NULL,
  action     VARCHAR(100) NOT NULL,
  details    TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- practical13_students (Practical 13 AJAX demo) ----------
CREATE TABLE practical13_students (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  full_name  VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  department VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEMO DATA (fictional)
-- ============================================================
INSERT INTO students (student_id, full_name, email, department, username, password_hash, role, status) VALUES
(1, 'Portal Admin',  'admin@studenthub.local',   'Administration',         'admin',   '$2y$12$qKoSdWYvomI26lqqc83yPuhB1cGW0orI0Q3O04gCoMVPETqIM83z6', 'Admin',   'Active'),
(2, 'Demo Student',  'student@studenthub.local', 'Computer Science',       'student', '$2y$12$Bdtn9UyzOjFBKJP9vibntefMD/nkNACAikmCTRF8aiNfYI7ClVVRi', 'Student', 'Active'),
(3, 'Aarav Patel',   'aarav@example.com',        'Computer Science',       NULL, NULL, 'Student', 'Active'),
(4, 'Diya Shah',     'diya@example.com',         'Information Technology', NULL, NULL, 'Student', 'Active'),
(5, 'Rohan Mehta',   'rohan@example.com',        'Computer Science',       NULL, NULL, 'Student', 'Active'),
(6, 'Anaya Desai',   'anaya@example.com',        'Business Administration',NULL, NULL, 'Student', 'Active'),
(7, 'Kabir Joshi',   'kabir@example.com',        'Information Technology', NULL, NULL, 'Student', 'Active');

INSERT INTO events (event_id, title, description, event_date, location, category, poster) VALUES
(1, 'Web Development Workshop', 'Learn HTML, CSS and PHP', '2026-11-10', 'Computer Lab',    'Workshop',    NULL),
(2, 'Coding Competition',       'A student programming contest', '2026-11-15', 'Main Auditorium', 'Competition', NULL),
(3, 'Tech Fest',                'Technology exhibitions and activities', '2026-11-20', 'College Campus',  'Festival',    NULL),
(4, 'Career Seminar',           'Career guidance for students', '2026-11-25', 'Seminar Hall',    'Seminar',     NULL);

INSERT INTO registrations (registration_id, student_id, event_id, registered_at, status) VALUES
(1, 3, 1, '2026-10-24 08:10:18', 'Registered'),
(2, 4, 1, '2026-10-24 08:10:18', 'Registered'),
(3, 5, 2, '2026-10-24 08:10:18', 'Registered'),
(4, 6, 3, '2026-10-24 08:10:18', 'Registered'),
(5, 7, 4, '2026-10-24 08:10:18', 'Registered'),
(6, 3, 3, '2026-10-24 08:10:18', 'Registered'),
(7, 2, 2, '2026-10-25 09:00:00', 'Registered');

INSERT INTO practical13_students (id, full_name, email, department) VALUES
(1, 'Sample One',   'sample1@example.com', 'Computer Science'),
(2, 'Sample Two',   'sample2@example.com', 'Information Technology'),
(3, 'Sample Three', 'sample3@example.com', 'Business Administration');

ALTER TABLE students             AUTO_INCREMENT = 8;
ALTER TABLE events               AUTO_INCREMENT = 5;
ALTER TABLE registrations        AUTO_INCREMENT = 8;
ALTER TABLE practical13_students AUTO_INCREMENT = 4;

SET FOREIGN_KEY_CHECKS = 1;
