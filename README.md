# Taylor's Clubopedia

**One place to discover Taylor's University clubs and societies, follow the ones you like, and never miss their events.**

Taylor's Clubopedia is a PHP and MySQL web application built for the ITS64504 Web Application Programming group assignment. Students browse clubs, follow them, RSVP to events and rate the events they attended. Club organisers manage their club's details and events from the same pages.

---

## Why we built it

Club events at Taylor's are announced in many places: Instagram, WhatsApp groups, posters and word of mouth. Students miss events or hear about them too late, organisers can't tell how many people are coming, and there is no simple way to collect feedback afterwards. Clubopedia brings all of this into one website.

## Features

**Everyone (no account needed)**
- Browse all clubs and filter them by category
- View club details: committee members and upcoming events
- See every event on a monthly calendar

**Students**
- Register and log in, with an optional "Remember me for 30 days"
- Follow and unfollow clubs
- RSVP to upcoming events, or cancel, and see how many people are going
- Rate and review past events they attended
- Profile with clubs and positions, upcoming and past events
- Edit phone number and upload a profile photo

**Club organisers** (everything students can do, plus)
- Edit their club's category and description in place
- Create, edit and delete their club's events

## Tech stack

| Layer | Technology |
|-------|------------|
| Front-end | HTML5, CSS3 (Grid, Flexbox, media queries), vanilla JavaScript |
| Back-end | PHP 8 |
| Database | MySQL, accessed with PDO prepared statements |
| Local server | MAMP (Apache + PHP + MySQL), phpMyAdmin |
| Design | Figma |

No frameworks or libraries are used apart from the Inter font from Google Fonts.

## Getting started

1. Install [MAMP](https://www.mamp.info/) and clone this repository into MAMP's `htdocs` folder:
   ```
   cd C:\MAMP\htdocs
   git clone https://github.com/xarhmx/taylors-club-events.git
   ```
2. Start MAMP and make sure both **Apache** and **MySQL** are running.
3. Open phpMyAdmin, go to the **Import** tab and import `database/taylors_club_events.sql`. This creates the database, all tables and sample data. Importing again resets everything to the sample data.
4. Check the database settings in `includes/config.php`. MAMP's defaults are user `root`, password `root`, port `3306` on Windows (usually `8889` on Mac).
5. Open http://localhost/taylors-club-events/ (Mac: http://localhost:8888/taylors-club-events/).

### Test accounts

| Email | Password | Role |
|-------|----------|------|
| student@test.com | Student123 | Student |
| organiser@test.com | Organiser123 | Club Organiser (Computer Science Society) |

All other sample users (for example `daniel@test.com`) use the password `Password123`.

## Project structure

```
taylors-club-events/
├── index.php, clubs.php, calendar.php     Public pages
├── profile.php, edit_profile.php          Pages for logged-in users
├── login.php, register.php, logout.php    Accounts
├── actions/        Form handlers (POST only): login, register, follow, RSVP,
│                   feedback, create/update/delete event, update club, update profile
├── includes/       Shared code
│   ├── config.php        Settings (database, sessions, lockout, uploads)
│   ├── db.php            PDO database connection
│   ├── auth.php          Sessions, cookies, cache headers, CSRF, login helpers
│   ├── data.php          Loads the data each page displays
│   ├── validation.php    Server-side form checks
│   ├── functions.php     Display helpers
│   └── header.php, footer.php, event_form.php, event_modals.php
├── css/style.css   All styles
├── js/script.js    Calendar, pop-ups, filters, photo preview
├── database/       MySQL dump to import in phpMyAdmin
└── images/         Logo and uploaded profile photos
```

Every form follows the **Post/Redirect/Get** pattern: the form posts to a file in `actions/`, which checks and saves the data, then redirects back to the page with a one-time message stored in the session.

## Database

Eight related tables:

| Table | Stores |
|-------|--------|
| `users` | Accounts (password stored as a bcrypt hash), role, student ID, phone, photo |
| `clubs` | Clubs and their organiser |
| `club_members` | Committee positions such as Club Head and Assistant Head |
| `events` | Events and the club running them |
| `club_followers` | Which users follow which clubs |
| `rsvps` | Who is going to which event |
| `feedback` | Ratings (1 to 5) and comments for past events |
| `remember_tokens` | Hashed tokens for "Remember me" logins |

Unique keys stop duplicate follows, RSVPs and reviews, and deleting an event automatically removes its RSVPs and feedback.

## Security

| Area | What the app does |
|------|-------------------|
| Sessions | HttpOnly, SameSite cookie; new session ID on login and every 15 minutes; logout after 30 minutes of inactivity; session ends if the cookie is used in another browser |
| Cookies | "Remember me" uses a random selector and validator; only a SHA-256 hash is stored, each token is used once, and logout deletes it |
| Cache | Private pages are never cached (`Cache-Control: no-store`); CSS, JS and images are cached with version numbers so updates show immediately |
| Passwords | bcrypt hashing; same message for a wrong email or password; account locked for 15 minutes after 5 failed attempts |
| Forms | CSRF token on every form; action files only accept POST; every field validated on the server |
| Database | Prepared statements for every query (no SQL injection) |
| Output | All user content escaped (no XSS); Content Security Policy allows only our own scripts |
| Permissions | The server checks that an organiser owns the club before any change |
| Uploads | Max 2 MB, real JPG/PNG only, random file names, scripts blocked in the upload folder |
| Privacy | Messages travel in the session, never the URL; errors are logged, not shown; private folders and the `.sql` file can't be opened in the browser |

## Team

| Name | Role |
|------|------|
| Ariz & Frank | Front-end development |
| Ariz & Hamza | Back-end development |
| Frank | UI/UX design (Figma) |
| Ariz & Hamza | Database |

ITS64504 Web Application Programming · Taylor's University · 2026
