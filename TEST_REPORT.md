# TEST REPORT - StudentHubPortal (Practical 15)

**Student name:** \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_  **Date tested:** \_\_\_\_\_\_\_\_\_\_\_\_  **Browser:** Chrome \_\_\_\_\_\_  **XAMPP:** 8.2.12

> How to use this file: run each test yourself. Write \\\\\\\*\\\\\\\*Pass\\\\\\\*\\\\\\\* or \\\\\\\*\\\\\\\*Fail\\\\\\\*\\\\\\\* in the \\\\\\\*Result\\\\\\\* column only
> after you have really tested it, and add a short note if something fails.
> Everything is left as \\\\\\\*\\\\\\\*Not tested\\\\\\\*\\\\\\\* on purpose, so the report only contains your real results.

Demo logins: Admin `admin@studenthub.local` / `Admin@12345`, Student `student@studenthub.local` / `Student@12345`.
Base URL: `http://localhost/StudentHubPortal/` (or `http://studenthub.local/`).

## 1\. Functional tests

|ID|Test|Steps|Expected result|Result|Notes|
|-|-|-|-|-|-|
|F01|Homepage loads|Open the base URL|Home page with menu: Home, About, Contact, Login, Register|Pass||
|F02|About and Contact pages|Click About, then Contact|Both pages open in the same theme|Pass||
|F03|Contact form|Submit valid form; then submit empty form|Success message; then error messages. Message appears in `data/contact\\\\\\\_submissions.csv`|Pass||
|F04|Student registration|Register page: fill valid data|Redirect to login with "Account created"|Pass||
|F05|Duplicate registration|Register again with the same email|Error: already registered|Pass||
|F06|Weak password|Register with a 5-character password|Error: at least 8 characters|Pass||
|F07|Student login|Login as demo Student|Student dashboard opens|Pass||
|F08|Admin login|Login as demo Admin|Admin dashboard with statistics|Pass||
|F09|Invalid password|Student email + wrong password|"Invalid email or password."|Pass||
|F10|Invalid email|Unknown email + any password|"Invalid email or password."|Pass||
|F11|Student dashboard|Check page|Name, email, department, counts, working links|Pass||
|F12|Admin dashboard|Check page|Live counts match phpMyAdmin tables|Pass||
|F13|Student list|Admin > Manage Students|Table shows students; search and filter work|Pass||
|F14|Add student|Add a new student|Success message; row appears; audit log `ADD\\\\\\\_STUDENT`|Pass||
|F15|Edit student|Edit a name|Success; audit log `EDIT\\\\\\\_STUDENT`|Pass||
|F16|Deactivate student|Click Deactivate|Status Inactive; audit log `DEACTIVATE\\\\\\\_STUDENT`|Pass||
|F17|Inactive login blocked|Try to login as the deactivated student|"This account is inactive"|Pass||
|F18|Export CSV|Click Export CSV|`student\\\\\\\_records.csv` downloads|Pass||
|F19|Add event|Admin > Manage Events > add event (with and without poster)|Event listed; audit log `ADD\\\\\\\_EVENT`|Pass||
|F20|Edit event|Change title/date, replace poster|Updated; old poster file removed|Pass||
|F21|Delete event|Delete an event|Removed; its registrations removed|Pass||
|F22|View events (student)|Student > View Events|Event cards shown; search works|Pass||
|F23|Event registration|Click Register on an event|Success; badge "You are registered"|Pass||
|F24|Duplicate event registration|Try to register twice|Not possible (badge shown / error)|Pass||
|F25|My Registrations|Student > My Registrations|The registered event is listed|Pass||
|F26|Cancel registration|Click Cancel|Status Cancelled; can register again|Pass||
|F27|Manage Registrations|Admin > Manage Registrations|List, filter by event, cancel/restore/delete work|Pass||
|F28|Profile and password change|Student > Profile > change password; log in with new one|Works; wrong current password rejected|Pass||
|F29|Logout|Click Logout|Login page "logged out"; Back button does not show protected data|Pass||

## 2\. AJAX tests (Practical 13)

Open `practical13/` as Admin, press **F12 > Network > Fetch/XHR**.

|ID|Test|Expected result|Result|Notes|
|-|-|-|-|-|
|A01|Page load|GET `api.php` returns JSON `success: true`, table filled|pass||
|A02|Add record|POST `api.php` status 201; table refreshes without page reload|pass||
|A03|Edit record|POST status 200; row updated|pass||
|A04|Delete record|POST status 200; row removed|pass||
|A05|Validation|Add invalid email|Error message shown (status 422)|Not tested|
|A06|Direct API access without login|Open `.../practical13/api.php` in a private window|JSON `Please log in first.` status 401|Not tested|
|A07|API as Student|Login as Student, open `.../practical13/api.php`|JSON "Forbidden" status 403|Not tested|

## 3\. Security and access tests

|ID|Test|Expected result|Result|Notes|
|-|-|-|-|-|
|S01|Dashboard without login|Open `/student\\\\\\\_dashboard.php` in a private window|Redirect to login|Pass|
|S02|Student opens admin page|As Student open `/admin\\\\\\\_dashboard.php`|403 Forbidden page|Pass|
|S03|Student opens other admin URLs|`/students.php`, `/events/index.php`, `/manage\\\\\\\_registrations.php`, `/practical14/audit\\\\\\\_logs.php`, `/practical13/index.php`|403 each time|Pass|
|S04|Admin opens student page|As Admin open `/my\\\\\\\_registrations.php`|403|Pass|
|S05|Session timeout|Set timeout to 20 s (see README), wait, click a link|Redirect to login "session expired"|Pass|
|S06|SQL injection|Login email `' OR '1'='1` ; search box `' OR 1=1 --`|No login, no error leak, no extra rows|Pass|
|S07|XSS|Register a name `<script>alert(1)</script>`; view it in the admin student list|Shown as plain text, no popup|Pass|
|S08|CSRF|On a form, remove the hidden `csrf\\\\\\\_token` field with DevTools and submit|"Security check failed" (HTTP 419)|Pass|
|S09|File upload type|Upload a `.php` file renamed to `.jpg`, and a `.txt` file|Rejected|Not tested|
|S10|File upload size|Upload an image bigger than 2 MB|Rejected|Not tested|
|S11|Password hashing|phpMyAdmin > students > password\_hash|Starts with `$2y$`, no plain text|Not tested|
|S12|Contact data protected|Open `/data/contact\\\\\\\_submissions.csv` in browser|403 Forbidden|Not tested|
|S13|Audit logging|Admin > Audit Logs after tests F14-F16, F19-F21|One row per action with user, IP and time|Not tested|
|S14|Brute force|6 wrong passwords in a row|"Too many failed attempts"|Not tested|

## 4\. Responsive tests (Chrome F12 > Toggle device toolbar)

|ID|Page|Desktop 1280|Tablet 768|Mobile 375|Notes|
|-|-|-|-|-|-|
|R01|Home (menu, cards)|Not tested|Not tested|Pass||
|R02|Login / Register forms|Not tested|Not tested|Pass||
|R03|Student dashboard|Not tested|Not tested|Pass||
|R04|Admin dashboard (stat cards)|Not tested|Not tested|Pass||
|R05|Tables (students, registrations) scroll sideways, no page overflow|Not tested|Not tested|Pass||
|R06|Events list / cards|Not tested|Not tested|Pass||
|R07|Mobile "Menu" button opens and closes|-|-|Pass||

## 5\. Accessibility checklist (tick after you check)

* \[ ] Every form field has a visible `<label>`.
* \[ ] Skip link "Skip to main content" appears when pressing Tab on a page.
* \[ ] The whole site can be used with the keyboard only (Tab, Enter) and the focus outline is visible.
* \[ ] Images have `alt` text (event posters).
* \[ ] Error and success messages use `role="alert"` / `role="status"`.
* \[ ] Text colour contrast is readable (dark brown on cream/white).
* \[ ] Each page has a `<title>`, one main heading (`h1`) and `lang="en"`.
* \[ ] The page works when zoomed to 200%.
* \[ ] Chrome DevTools > Lighthouse > Accessibility score noted here: \_\_\_\_\_\_

## 6\. Database checks

|ID|Check|Result|Notes|
|-|-|-|-|
|D01|`studenthub.sql` imports into a NEW empty database (e.g. `test\\\\\\\_import`) without errors|Pass||
|D02|After import there are 5 tables with demo data|Pass||
|D03|Registrations reference valid students and events (foreign keys)|Pass||
|D04|Only one database is used (`student\\\\\\\_hub\\\\\\\_db`); `event\\\\\\\_db` not needed|Pass||

## 7\. Summary

All test that are Performed are pass
Signature: \_\_\_\_\_\_\_\_Nand Shah\_\_\_\_\_\_\_\_\_\_\_\_

