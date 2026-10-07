# SJBCE Election Portal

Student election/voting system for St. John Bosco College of Education.
PHP (vanilla, PDO) + MySQL 8 + HTML/CSS/JS. No framework required.

Developed by Oliv-Tech.

## Theme
Navy blue, red, and white — see `assets/css/style.css` for the full token
system (`:root` variables). Candidate cards use a "ballot stub" design:
photo on one side, a perforated divider, and details on the other.

## How access works (important — read this first)

There is **no public self-registration**. Students are added to the system
by an administrator, from the official school roster, via CSV import
(admin → Import Students). This is a deliberate security choice: only
students on that list can ever gain access, so outsiders cannot register
themselves.

Students don't get a password up front. To vote, they:
1. Go to "Get Access Code" and enter their email.
2. Receive a 6-digit one-time code by email (valid 10 minutes).
3. Enter the code, then set a password.

That password is **only valid for the election it was set for**. Once that
election ends (or a new one starts), the student must go through the
access-code process again. A leaked or reused old password can't be used
to vote in a future election.

## Setup

1. **Create the database**
   ```
   mysql -u root -p < schema.sql
   ```
   This single file creates everything: all 10 tables, fully up to date.
   (If you're upgrading an older install that used the separate
   `add_login_attempts_table.sql` / `add_graduated_status.sql` /
   `add_otp_system.sql` scripts, you don't need to re-run this —
   your database already has everything from those.)

2. **Configure the database connection**
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sjbce_election');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   ```

3. **Configure email sending**
   Edit `config/mail.php` with a Gmail address and an App Password
   (instructions are in the comments at the top of that file). This
   powers the OTP access-code emails.

   Note: some free hosting providers block outgoing email entirely,
   regardless of correct credentials. If codes aren't arriving, confirm
   your host allows outbound SMTP before assuming the config is wrong.

4. **Create your first admin account**
   There's no UI for the very first admin (only an existing admin can
   create new ones). A one-time, self-deleting setup script is the way
   to do this:
   - Take `create_admin.php` from the project's setup tools (see below)
   - Place it in the project root (same folder as `index.php`)
   - Edit the `$fullName`, `$email`, and `$password` values inside it
   - Visit it once in your browser
   - It creates the admin account and **deletes itself automatically**
     the moment it succeeds — no manual cleanup step to forget.
   - Re-run the same script anytime to reset that admin's password —
     it updates the existing account instead of creating a duplicate.

5. **File permissions**
   Make sure the web server can write to `assets/uploads/candidates/`.

6. **Serve the app**
   Point your web server's document root at this folder. `BASE_URL` is
   auto-detected from wherever the project actually lives on disk — no
   manual configuration needed, and it works correctly whether the site
   sits at your domain's root or in a subfolder.

## Full feature list

**Admin**
- Login, dashboard with live statistics
- Create, edit, and reopen elections; schedule start/end times
- Start / Pause / End elections; run multiple at once
- Manage positions and candidates (photo upload, phone, manifesto)
- **Import Students** — bulk CSV upload of the official roster
- **Promote / Graduate** — one-click yearly workflow: promote students up
  a level, or mark Level 400 as graduated (removes login/voting access
  permanently while keeping their full voting history intact)
- Registered-student management (suspend/reactivate)
- Live results with tie-aware winner declaration, CSV export
- Manage other administrators (Super Admin only)
- Full audit log (Super Admin only)

**Student access (OTP-based, no self-registration)**
- Request access code by email
- Verify code, set a password valid for the current election only
- Profile management

**Voter panel**
- Dashboard shows every activated election immediately, regardless of
  device clock — decided entirely by the server's time. If voting hasn't
  started yet, it shows an "opens at" countdown with disabled vote
  buttons instead of hiding the election.
- Live countdown to polls closing, live vote count
- One vote per position — enforced at the database level (not just app
  logic), verified to hold even under simulated concurrent requests
- **Results page** — live "Leading" standings while voting is open,
  clear "Winner" (or "Tie", handled correctly) once an election ends

**Security**
- BCrypt password hashing throughout
- CSRF tokens on every form
- SQL injection protection via prepared statements everywhere
- XSS protection via output escaping
- Login rate limiting (5 failed attempts/account or 20/IP within 15 min)
- Full audit trail

## What's not included (optional/advanced, flagged in the original brief)

- PDF export of results (CSV export is implemented)
- SMS-based OTP delivery (email OTP is fully implemented)
- QR code verification
- Dark mode, multi-language support
- Real-time auto-refreshing vote counts (updates on page reload)

## Automatic backups

Not something the app does from inside itself — set up a cron job on
the server, e.g.:
```
0 2 * * * mysqldump -u root -p'yourpass' sjbce_election > /backups/sjbce_$(date +\%F).sql
```
