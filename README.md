# Taylor's Club Events

A learning project using intermediate PHP: strict types, typed static methods, associative arrays, foreach loops, nullable return values, sessions and password hashing. There is no framework, ORM, inheritance hierarchy or dependency container.

## Run

Open this folder through MAMP, or run `php -S localhost:8000` from the project folder and visit http://localhost:8000. PHP 7.4 or newer is required. Register an account, log in, explore clubs, follow clubs and book events. My profile lists your bookings. Feedback becomes available once a booked event has started; event records contain start times only.

`Data.php` supplies the sample clubs and events. Accounts, RSVPs, follows and feedback are stored in the browser's PHP session. Logging out keeps these records so you can log back in. Expiring or clearing the session removes them. Capacity and duplicate checks apply only within this session, not across visitors.

`includes/db.php` is shared setup, not a live database connection. The SQL dump is a future integration reference; its sample accounts are not login accounts for this demo. Its password values are plaintext and must be replaced with password hashes before any database-backed login is implemented.

New accounts always receive the student role. To review the protected organiser dashboard locally, a developer can set an existing demo account's role to `organiser` in server-side session data before logging in again. There is intentionally no public role selector. Organisers can create events for any sample club in this demo. Event editing and persistent club ownership require a future database implementation.

All forms that change session data use POST and CSRF tokens. Displayed text is escaped. Authentication uses `password_hash`, `password_verify` and session ID regeneration. The project retains the existing `Rspv.php` filename for its RSVP page.

## Intermediate structure

`EventRepository` combines seed records and session-created events. `EventService` receives that repository through its constructor and handles search, sorting, field validation and organiser-only creation. `ParticipationService` owns booking capacity, duplicate booking handling, review eligibility and rating summaries. Controllers handle HTTP requests and redirects; templates render the results. Domain exceptions report rejected operations without mixing business rules into HTML. The event search supports club and date filters.

Run the focused business-rule checks with `php tests/services.php`.

## Frontend handoff

HTML templates have been removed for the frontend team. Successful GET requests currently have an empty body; PHP controllers still prepare their variables and process POST requests. The CSS is retained for reference but is not loaded by the backend.

Add your templates after the controller code, keeping request handling before any output so redirects still work. Existing form field names are unchanged. Every POST form must send `csrf_token` with the value from `csrfToken()`; escape it with `escape()` when rendering the hidden input. The old HTML helper `tokenField()` was removed. Session setup remains in `includes/db.php`.

`profile.php` provides `$events` and `$followedClubs`; `clubs.php` provides `$clubs`; the dashboard provides `$events` and `$eventSummaries` keyed by event ID. Other controllers retain their event, error and form values. The header, footer and event list files are empty PHP placeholders ready for the frontend team.
