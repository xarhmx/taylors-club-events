<?php
/*
 * includes/config.php
 * ------------------------------------------------------------
 * Settings for the whole site. Change the database details here
 * to match your MAMP setup (MAMP → Preferences/Settings → Ports).
 * ------------------------------------------------------------
 */

// ---- Database (MAMP defaults) ----
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);              // Windows MAMP: 3306   |   Mac MAMP: usually 8889
define('DB_NAME', 'taylors_club_events');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// ---- Errors ----
// false = visitors never see PHP/SQL error details (safer).
// Set to true ONLY while fixing a problem on your own computer.
define('APP_DEBUG', false);

// ---- Sessions & cookies ----
define('SESSION_NAME', 'TCE_SESSION');          // name of the session cookie
define('SESSION_IDLE_LIMIT', 30 * 60);          // log out after 30 minutes of no activity
define('SESSION_REGENERATE_EVERY', 15 * 60);    // new session ID every 15 minutes
define('REMEMBER_COOKIE', 'tce_remember');      // "Remember me" cookie name
define('REMEMBER_DAYS', 30);                    // how long "Remember me" lasts

// ---- Login protection ----
define('MAX_LOGIN_ATTEMPTS', 5);                // wrong passwords before the account is locked
define('LOCKOUT_MINUTES', 15);                  // how long it stays locked

// ---- Club categories organisers can choose ----
define('CLUB_CATEGORIES', ['Academic', 'Arts', 'Community', 'Cultural', 'Sports', 'Technology']);

// ---- Uploads ----
define('MAX_PHOTO_BYTES', 2 * 1024 * 1024);     // 2MB profile photos
define('PROFILE_PHOTO_DIR', __DIR__ . '/../images/profiles/');
