# StudentHubPortal

A college student portal built with **PHP, MySQL/MariaDB, HTML, CSS and JavaScript**, running on **XAMPP (Apache)**.
Students can register, log in, view events and register for them. Administrators manage students, events and
registrations, see live statistics and review an audit log.

This is the integrated final project for **Practical 15** (combining Practicals 1 to 14).

## Features

|Area|What it does|
|-|-|
|Public site|Home, About, Contact (form saved to a protected CSV file), Login, Register|
|Authentication|Registration, login, logout, password hashing, session timeout, role-based redirect|
|Student area|Dashboard, View Events, Register / Cancel, My Registrations, Profile + change password|
|Admin area|Dashboard with live statistics, Manage Students (add / edit / deactivate / export CSV), Manage Events (with poster upload), Manage Registrations, Audit Logs|
|Practical 13|AJAX student manager (Fetch API + PHP JSON API) at `practical13/` (admin only)|
|Practical 14|RBAC (`practical14/rbac.php`, 403 page) and audit logging (`practical14/log\\\_action.php`)|
|Design|One shared cream and brown theme (`assets/css/portal.css`), responsive menu for mobile|

## Technologies

PHP 8.2, MariaDB 10.4 / MySQL, Apache 2.4 (XAMPP 8.2.12), HTML5, CSS3, JavaScript (Fetch API).

## Requirements

* Windows with **XAMPP** (Apache + MySQL + PHP 8.x) installed in `C:\\\\xampp`
* A browser (Chrome recommended)
* Optional: Git for uploading to GitHub

## Installation

1. **Copy the project** so the files are directly inside:
`C:\\\\xampp\\\\htdocs\\\\StudentHubPortal\\\\`  (you should see `index.php` inside that folder).
2. Open **XAMPP Control Panel** and **Start** Apache and MySQL.
3. **Create the database:** open [http://localhost/phpmyadmin](http://localhost/phpmyadmin), click **New**, name it `student\\\_hub\\\_db`,
collation `utf8mb4\\\_unicode\\\_ci`, click **Create**.
4. **Import the data:** click `student\\\_hub\\\_db`, open the **Import** tab, choose `studenthub.sql`
(in the project root), click **Go**. You should see 5 tables:
`students, events, registrations, audit\\\_logs, practical13\\\_students`.
5. **Check the database settings** in `db.php` (default XAMPP: user `root`, empty password, database `student\\\_hub\\\_db`).
6. Open the website: [http://localhost/StudentHubPortal/](http://localhost/StudentHubPortal/)

Optional virtual host (`http://studenthub.local/`): follow `docs/VIRTUAL\\\_HOST\\\_SETUP.md`.

## Demo logins (fake data)

|Role|Email|Password|
|-|-|-|
|Admin|`admin@studenthub.local`|`Admin@12345`|
|Student|`student@studenthub.local`|`Student@12345`|

You can also create a new student account on the Register page. Change the demo passwords if you ever
put this project on a real server.

## Testing instructions

1. Run `tools\\\\check\\\_php\\\_syntax.bat` (double-click). It must say "no syntax errors".
2. Follow every row in `TEST\\\_REPORT.md` and write **Pass** or **Fail** yourself.
3. Check each item in `SECURITY\\\_CHECKLIST.md`.
4. For the AJAX test: open Chrome on `practical13/` as Admin, press **F12 > Network > Fetch/XHR**, then add,
edit and delete a record and watch the `api.php` requests.
5. For responsive tests: **F12 > Toggle device toolbar** and test 1280 px, 768 px and 375 px widths.
6. To test the session timeout quickly: in `includes/bootstrap.php` change `600` to `20`, log in, wait 25 seconds,
click any link, then change it back.

## Project folder structure

```
StudentHubPortal/
├── index.php, about.php, contact.php          Public pages
├── registration.php, login.php, authenticate.php, logout.php, dashboard.php
├── student\\\_dashboard.php, events.php, my\\\_registrations.php, profile.php   Student pages
├── admin\\\_dashboard.php, students.php, add\\\_student.php, edit\\\_student.php,
│   delete\\\_student.php, export\\\_students.php, manage\\\_registrations.php      Admin pages
├── db.php                      The ONE database connection ($conn MySQLi, $pdo PDO)
├── studenthub.sql              Full database dump (import this)
├── includes/                   bootstrap.php (session), auth.php (login/CSRF/403), header.php, footer.php
├── assets/css/portal.css       Shared cream/brown theme
├── assets/js/nav.js            Mobile menu
├── events/                     Practical 12 - admin event management + poster upload
├── practical13/                Practical 13 - AJAX (index.php, api.php, script.js)
├── practical14/                Practical 14 - rbac.php, 403.php, log\\\_action.php, audit\\\_logs.php
├── data/                       Contact messages (blocked from the web by .htaccess)
├── database/                   upgrade\\\_existing\\\_db.sql (optional)
├── docs/                       VIRTUAL\\\_HOST\\\_SETUP.md, GITHUB\\\_GUIDE.md
├── legacy\\\_practical9/          Old static pages (archive)
├── Screenshots/                Put your real screenshots here
├── tools/                      check\\\_php\\\_syntax.bat
├── README.md, TEST\\\_REPORT.md, SECURITY\\\_CHECKLIST.md
└── .gitignore
```

## How the modules were integrated

* **One database.** The old `event\\\_db` is no longer used. Events live in `student\\\_hub\\\_db.events`
(with new `category` and `poster` columns), so students can register for the events the admin creates.
* **One login.** Every protected page uses `includes/auth.php` (`require\\\_login()` / `require\\\_role()`),
so the checks are always done on the server.
* **One layout.** `includes/header.php` builds the menu for Guest, Student or Admin, so only working links are shown.

## Known limits (honest notes)

* The session timeout is 10 minutes (the original 120 seconds is too short for normal use). Change it in `includes/bootstrap.php`.
* Login throttling is simple (session based). A production site would also limit by IP address in the database.
* Logout is a normal link (GET), which is acceptable for this project.
* Written for local XAMPP use; for a real server also use HTTPS, a strong database password and `display\\\_errors=Off`.

