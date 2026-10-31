# Apache Virtual Host Setup (studenthub.local) - XAMPP on Windows

Your normal `http://localhost/StudentHubPortal/` keeps working. This only ADDS a second address.
Make a copy of each file before editing it (for example `httpd-vhosts.conf.bak`).

## Step 1 - Add the virtual host

Open **Notepad as Administrator**, then open:
`C:\xampp\apache\conf\extra\httpd-vhosts.conf`

Add this at the END of the file. The first block keeps plain `localhost` working:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "C:/xampp/htdocs"
</VirtualHost>

<VirtualHost *:80>
    ServerName studenthub.local
    DocumentRoot "C:/xampp/htdocs/StudentHubPortal"

    <Directory "C:/xampp/htdocs/StudentHubPortal">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/studenthub-error.log"
    CustomLog "logs/studenthub-access.log" common
</VirtualHost>
```

## Step 2 - Make sure vhosts are enabled

Open `C:\xampp\apache\conf\httpd.conf` and check this line has NO `#` in front:

```apache
Include conf/extra/httpd-vhosts.conf
```

(In most XAMPP versions it is already enabled. If it starts with `#`, remove the `#`.)

## Step 3 - Edit the Windows hosts file

Open **Notepad as Administrator**, then open `C:\Windows\System32\drivers\etc\hosts`
(change the file type filter to "All files"). Add this line at the end and save:

```
127.0.0.1   studenthub.local
```

## Step 4 - Restart Apache and test

1. XAMPP Control Panel: **Stop** Apache, then **Start** it again.
2. Open Chrome: `http://studenthub.local/`
3. The Student Hub home page should open. Also test `http://localhost/StudentHubPortal/`.

## If it does not work

* Apache will not start: run `C:\xampp\apache\bin\httpd.exe -t` in Command Prompt to see the line with the mistake.
* "Server not found": check the hosts file line and that you saved it as Administrator.
* Shows the XAMPP dashboard instead: the `ServerName localhost` block in Step 1 is missing, or Apache was not restarted.
* Chrome still fails: open `chrome://net-internals/#dns` and click "Clear host cache".

The portal uses relative links everywhere, so it works with `http://studenthub.local/` and
`http://localhost/StudentHubPortal/` without changing any code.
