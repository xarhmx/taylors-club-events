# Taylor's Club Events

Event management web app for Taylor's University clubs and societies.
ITS64504 Web Application Programming – Group Assignment

## Tech stack
HTML, CSS, JavaScript, PHP 8, MySQL (MAMP + phpMyAdmin)

## Setup
1. Clone this repo into MAMP's `htdocs` folder.
2. Start MAMP and open phpMyAdmin.
3. Import `database/taylors_club_events.sql` (creates the database, tables and sample data).
4. Check the database settings in `includes/config.php` (Windows MAMP port 3306`root`).
5. Visit http://localhost/taylors-club-events/ (Mac: http://localhost:8888/taylors-club-events/).

## Test accounts
| Email | Password | Role |
|-------|----------|------|
| student@test.com | Student123 | Student |
| organiser@test.com | Organiser123 | Club Organiser (Computer Science Society) |

Other sample users (e.g. daniel@test.com) use `Password123`.

## Features
- Register, log in (Student / Club Organiser), "Remember me", log out
- Browse clubs, filter by category, view club details (committee + upcoming events)
- Follow / unfollow clubs
- Event calendar; event details pop-up with RSVP / cancel RSVP and number going
- Rate and review past events you attended
- Profile with clubs and positions, upcoming and past events; edit phone and profile photo
- Organisers: edit their club's category/description, create, edit and delete their club's events

## Project structure
```
actions/      Form handlers (POST only): login, register, follow, RSVP, feedback,
              create/update/delete event, update club, update profile
includes/     config.php (settings), db.php (PDO connection), auth.php (sessions,
              cookies, cache, CSRF, login helpers), data.php (loads page data),
              validation.php, functions.php, header/footer, pop-up templates
css/ js/      Styles and front-end JavaScript
database/     MySQL dump to import in phpMyAdmin
images/       Logo, uploaded profile photos (images/profiles/)
```

## Security
**Sessions**
- Session ID only in an HttpOnly, SameSite=Lax cookie (never in the URL); strict mode rejects made-up IDs
- New session ID on login (stops session fixation) and every 15 minutes
- Automatic logout after 30 minutes of inactivity
- Session tied to the browser's fingerprint; a copied session cookie used in another browser is rejected

**Cookies**
- Optional "Remember me" cookie (30 days, HttpOnly, SameSite=Lax) using a random selector + validator; only a SHA-256 hash is stored in the database, each token is used once and replaced, and logout deletes it

**Cache**
- All PHP pages send `Cache-Control: no-store`, so personal pages can't be reopened with the Back button after logging out
- CSS, JS and images are cached by the browser (`.htaccess`), with `?v=<file time>` cache busting so updates appear immediately

**Passwords and login**
- Passwords hashed with `password_hash` (bcrypt) and checked with `password_verify`; hashes upgraded automatically
- Same error message for a wrong email or a wrong password; same response time for both
- Account locked for 15 minutes after 5 wrong passwords

**Requests and data**
- Prepared statements for every query (SQL injection)
- CSRF token on every form; actions only accept POST; logout is POST-only
- All output escaped with `htmlspecialchars` (XSS)
- Server-side validation of every field (lengths, dates, times, categories, ratings, phone)
- Server checks the user is the club's organiser before editing clubs or events (not just hidden buttons)
- Safe redirects: users can only be sent back to our own pages (no open redirects)

**Clean URLs**
- Error and success messages travel in the session (flash messages), not in the URL
- URLs only contain harmless ids (e.g. `clubs.php?club=3`); no emails, passwords or messages

**Uploads**
- Profile photos: max 2MB, must really be a JPG/PNG (checked from the file contents), saved under a random name, and `images/profiles/.htaccess` stops any script from running there

**Server**
- Security headers: Content-Security-Policy (only our own scripts run), X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy; `X-Powered-By` removed
- No folder listings; `includes/` and `database/` cannot be opened in the browser; `.sql`/`.md` files blocked
- Errors are logged, not shown to visitors (`APP_DEBUG` in `includes/config.php`)

## Team
| Name | Role |
|------|------|
|      | Front-end |
|      | Back-end |
|      | UI/UX (Figma) |
|      |  |
