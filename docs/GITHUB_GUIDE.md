# GitHub upload guide (Windows, beginner)

Your repository already exists: https://github.com/NandShah2007/StudentHubPortal (it has an "Initial commit").

## A. Put the new files in your project folder

1. **Backup first.** Copy `C:\xampp\htdocs\StudentHubPortal` to `C:\Backups\StudentHubPortal_old`.
2. Open `C:\xampp\htdocs\StudentHubPortal`. Turn on **View > Show > Hidden items** and keep the hidden `.git` folder.
3. Delete everything else inside that folder (NOT the `.git` folder).
4. Copy everything from the new `StudentHubPortal` folder into it, so `index.php` and `.git` are side by side.

## B. Check for secrets before uploading

* Open `db.php`: password must be empty (XAMPP) or a fake one - never your real password.
* Check `data/` has no `contact_submissions.csv` (it is in `.gitignore`).
* Check `events/uploads/` has no private images.

## C. Commit and push (Command Prompt inside the project folder)

```
git status
git add .
git commit -m "Integrated StudentHubPortal - Practical 15"
git push origin main
```

If Git asks who you are (first time only):

```
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
```

If the push says "rejected", run `git pull origin main --allow-unrelated-histories` once, then `git push origin main`.

## D. Verify

Open https://github.com/NandShah2007/StudentHubPortal and check: `README.md` is shown, `studenthub.sql` is there,
`Screenshots/` has your images, and `data/contact_submissions.csv` is **not** there.
Copy the repository link for your submission.
