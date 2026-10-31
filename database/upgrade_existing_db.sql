-- ============================================================
-- OPTIONAL: upgrade YOUR EXISTING student_hub_db without losing data.
-- Run in phpMyAdmin > student_hub_db > SQL tab.
-- (Use this ONLY if you want to keep your current data.
--  Otherwise just import ../studenthub.sql into a fresh database.)
-- Needs MariaDB 10.4 (XAMPP 8.2.12) - "IF NOT EXISTS" is supported.
-- BACK UP FIRST: phpMyAdmin > Export.
-- ============================================================
USE student_hub_db;

ALTER TABLE students
  ADD COLUMN IF NOT EXISTS role   VARCHAR(20) NOT NULL DEFAULT 'Student',
  ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'Active',
  ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE events
  ADD COLUMN IF NOT EXISTS category VARCHAR(50)  NOT NULL DEFAULT 'General',
  ADD COLUMN IF NOT EXISTS poster   VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_name VARCHAR(150) NOT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS practical13_students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  department VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OPTIONAL: copy old Practical 12 events from the separate event_db database.
-- Remove the two dashes at the start of the lines below to run it.
-- Also copy the old poster files from  htdocs\StudentHubPortal\events\uploads\
-- into this project's  events\uploads\  folder.
--
-- INSERT INTO student_hub_db.events (title, event_date, location, category, poster)
-- SELECT o.title, o.event_date, o.location, o.category, o.poster
-- FROM event_db.events o
-- WHERE NOT EXISTS (
--   SELECT 1 FROM student_hub_db.events n
--   WHERE n.title = o.title AND n.event_date = o.event_date
-- );

-- To create the demo Admin in your existing database, use the portal
-- Register page, then in phpMyAdmin set that account's role to 'Admin'.
