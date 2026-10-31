# SECURITY CHECKLIST - StudentHubPortal

**"In code"** = the feature is written in the source files listed. **"Tested by me"** = you tick it after doing the matching test in `TEST\_REPORT.md`.
Do not tick a box until you really tested it.

|#|Security feature|Where it is in the code|Tested by me|Test ID|
|-|-|-|-|-|
|1|Passwords stored hashed with `password\_hash()` (bcrypt) and checked with `password\_verify()`|`registration.php`, `add\_student.php`, `profile.php`, `authenticate.php`|\[ x]|S11|
|2|Prepared statements for database queries (MySQLi and PDO)|all pages that read/write the database; `practical13/api.php`|\[x ]|S06|
|3|Output escaping with `h()` / `htmlspecialchars()` (stops XSS); the AJAX page uses `textContent`|`includes/auth.php` and all views; `practical13/script.js`|\[ x]|S07|
|4|CSRF token on every POST form and on AJAX writes (`X-CSRF-Token`)|`csrf\_field()` / `csrf\_check()` in `includes/auth.php`; `practical13/api.php`|\[ x]|S08|
|5|Server-side role checks (`require\_login`, `require\_role`) on every protected page - not only hiding links|`includes/auth.php`; first lines of each protected page|\[ x]|S01-S04|
|6|HTTP 403 Forbidden page for wrong role|`show\_403()`, `practical14/403.php`|\[ x]|S02-S04|
|7|Session timeout after inactivity (default 10 minutes)|`includes/bootstrap.php`, `require\_login()`|\[ x]|S05|
|8|Session ID regenerated after login (prevents session fixation); cookie is HttpOnly + SameSite=Lax|`authenticate.php`, `includes/bootstrap.php`|\[ x]|F07|
|9|Logout destroys the session and cookie|`logout.php`, `destroy\_session()`|\[ x]|F29|
|10|Same error message for wrong email or wrong password (no user enumeration)|`authenticate.php`|\[x ]|F09, F10|
|11|Basic brute-force limit: 5 failed logins in 5 minutes|`authenticate.php`|\[ x]|S14|
|12|Inactive accounts cannot log in|`authenticate.php`|\[x ]|F17|
|13|Input validation (email, username pattern, password length, dates, allowed lists)|registration, student and event forms|\[x ]|F05, F06|
|14|File upload checks: real MIME type (finfo), image check, 2 MB limit, random file name, extension chosen by server, PHP blocked in upload folder|`events/upload.php`, `events/uploads/.htaccess`|\[x ]|S09, S10|
|15|Destructive actions use POST (not links) with confirmation|delete forms in `events/index.php`, `students.php`, `manage\_registrations.php`|\[x ]|F16, F21|
|16|A student can only cancel their OWN registration|`my\_registrations.php` (`student\_id` in the SQL WHERE)|\[x ]|F26|
|17|Audit log of admin actions (user, action, details, IP, time)|`practical14/log\_action.php`, `practical14/audit\_logs.php`|\[x ]|S13|
|18|CSV export and contact CSV protected against spreadsheet formula injection|`export\_students.php`, `contact.php`|\[x ]|F18|
|19|Stored contact messages are not downloadable from the web|`data/.htaccess`|\[x ]|S12|
|20|Database errors are logged, not shown to visitors|`db.php` and `catch` blocks use `error\_log()`|\[x ]|-|
|21|Admin cannot deactivate their own account by accident|`edit\_student.php`, `delete\_student.php` (role = Student only)|\[x ]|-|

## Known limits (be honest in your viva)

* Demo passwords are published in the README - change them for any real use.
* XAMPP default MySQL user is `root` with no password. Never use that on a real server.
* The project runs over HTTP locally. A real site needs HTTPS, then add `'secure' => true` to the session cookie in `includes/bootstrap.php`.
* No email verification, no password reset, no two-factor login.
* Brute-force limit is per browser session only.

## Files that were intentionally left out

* `test\_connection.php` (Practical 9 test page): it listed registrations and emails to anyone without a login. It is not needed in the final portal.
* `events/upload\_demo.php`: a demo upload page with no login check.

