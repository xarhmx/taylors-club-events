<?php
/*
 * includes/auth.php
 * ------------------------------------------------------------
 * Included at the top of EVERY page and action. It handles:
 *
 *   1. Error display      - no PHP/SQL details shown to visitors
 *   2. Security & cache headers
 *   3. Sessions           - secure session cookie, idle timeout,
 *                           session ID refresh, hijack check
 *   4. Cookies            - "Remember me" login cookie
 *   5. CSRF protection    - secret token on every form
 *   6. Login helpers      - currentUser(), requireLogin(), logInUser()
 *   7. Flash messages     - one-time messages after a redirect
 *   8. Small input helpers
 *
 * Based on the back-end draft (CSRF tokens, session_regenerate_id,
 * password_hash, POST-only logout) and connected to MySQL.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/db.php';


/* ============================================================
   1. Error display
   ============================================================ */
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// Anything that crashes shows a plain message instead of code / file paths
set_exception_handler(function (Throwable $exception) {
    error_log((string) $exception);
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo APP_DEBUG
        ? '<pre>' . htmlspecialchars((string) $exception) . '</pre>'
        : 'Sorry, something went wrong. Please go back and try again.';
});


/* ============================================================
   2. Security & cache headers (sent with every page)
   ============================================================ */
header_remove('X-Powered-By');                       // don't advertise the PHP version
header('X-Content-Type-Options: nosniff');           // browser must trust our file types
header('X-Frame-Options: DENY');                     // site can't be put inside another site (clickjacking)
header('Referrer-Policy: same-origin');              // our URLs are never sent to other websites
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
// Content Security Policy: only run scripts from our own site
header("Content-Security-Policy: default-src 'self'; script-src 'self'; "
     . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; "
     . "img-src 'self' data: blob:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");

// CACHE: pages contain personal data (profile, RSVPs ...), so the browser
// must not save them. After logging out, pressing Back will not show
// the old page. (CSS/JS/images ARE cached - see .htaccess.)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');


/* ============================================================
   3. Sessions
   ============================================================ */
function isHttps()
{
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');    // reject made-up session IDs
    ini_set('session.use_only_cookies', '1');   // session ID only in a cookie...
    ini_set('session.use_trans_sid', '0');      // ...never in the URL
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,           // session cookie disappears when the browser closes
        'path'     => '/',
        'httponly' => true,        // JavaScript can't read it (protects against XSS stealing it)
        'samesite' => 'Lax',       // not sent with forms from other websites (CSRF)
        'secure'   => isHttps(),   // HTTPS-only when the site uses HTTPS
    ]);
    session_cache_limiter('');     // we send our own cache headers above
    session_start();
}

// A short fingerprint of the browser. If it suddenly changes mid-session,
// someone may have copied the session cookie into another browser.
function browserFingerprint()
{
    return hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

// Idle timeout: log out after SESSION_IDLE_LIMIT seconds of no activity
if (isset($_SESSION['user_id'], $_SESSION['last_activity'])
    && time() - $_SESSION['last_activity'] > SESSION_IDLE_LIMIT) {
    endUserSession(false);   // a "Remember me" cookie (if any) will log them straight back in
    if (!isset($_COOKIE[REMEMBER_COOKIE])) {
        flash('error', 'You were logged out after 30 minutes of inactivity.');
    }
}

// Hijack check
if (isset($_SESSION['user_id']) && ($_SESSION['fingerprint'] ?? '') !== browserFingerprint()) {
    endUserSession(true);
}

$_SESSION['last_activity'] = time();

// Give the session a new ID every so often (limits session fixation/theft)
if (!isset($_SESSION['created_at'])) {
    $_SESSION['created_at'] = time();
} elseif (time() - $_SESSION['created_at'] > SESSION_REGENERATE_EVERY) {
    session_regenerate_id(true);
    $_SESSION['created_at'] = time();
}


/* ============================================================
   4. Cookies: "Remember me"
   ------------------------------------------------------------
   The cookie holds "selector:validator" (random values, NOT the
   password or user ID). The database only stores a SHA-256 hash of
   the validator, so a stolen database can't be used to log in.
   ============================================================ */
function setRememberCookie($value, $expires)
{
    setcookie(REMEMBER_COOKIE, $value, [
        'expires'  => $expires,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isHttps(),
    ]);
}

function createRememberToken($userId)
{
    global $pdo;
    $selector  = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $expires   = time() + REMEMBER_DAYS * 86400;

    $stmt = $pdo->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $selector, hash('sha256', $validator), date('Y-m-d H:i:s', $expires)]);

    setRememberCookie($selector . ':' . $validator, $expires);
}

function forgetRememberToken()
{
    global $pdo;
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (is_string($cookie) && strpos($cookie, ':') !== false) {
        [$selector] = explode(':', $cookie, 2);
        $pdo->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$selector]);
    }
    setRememberCookie('', time() - 3600);   // delete the cookie in the browser
    unset($_COOKIE[REMEMBER_COOKIE]);
}

// Logs the user in from the cookie if there's no active session
function loginFromRememberCookie()
{
    global $pdo;
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (!is_string($cookie) || !preg_match('/^[a-f0-9]{24}:[a-f0-9]{64}$/', $cookie)) {
        if ($cookie !== '') {
            forgetRememberToken();
        }
        return;
    }
    [$selector, $validator] = explode(':', $cookie, 2);

    $stmt = $pdo->prepare('SELECT token_id, user_id, token_hash, expires_at FROM remember_tokens WHERE selector = ?');
    $stmt->execute([$selector]);
    $token = $stmt->fetch();

    $valid = $token
        && hash_equals($token['token_hash'], hash('sha256', $validator))   // timing-safe compare
        && strtotime($token['expires_at']) > time();

    if (!$valid) {
        forgetRememberToken();
        return;
    }

    // Use each token once: delete it and give the browser a fresh one
    $pdo->prepare('DELETE FROM remember_tokens WHERE token_id = ?')->execute([$token['token_id']]);
    logInUser((int) $token['user_id'], true);
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE[REMEMBER_COOKIE])) {
    loginFromRememberCookie();
}


/* ============================================================
   5. CSRF protection
   ------------------------------------------------------------
   Every form includes a secret token that only our pages know.
   A fake form on another website can't include it, so its
   request is rejected.
   ============================================================ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Put csrfField() inside every <form method="POST"> (it prints a hidden input)
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// Call at the start of every action. Stops the request if the token is wrong.
function checkCsrf()
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        flash('error', 'Your session expired. Please try again.');
        redirect('index.php');
    }
}

// Actions only accept POST (links can't change data)
function requirePost()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('This page only accepts form submissions.');
    }
}


/* ============================================================
   6. Login helpers
   ============================================================ */

// The logged-in user's row from the database, or null for guests
function currentUser()
{
    global $pdo;
    static $user = false;   // remember it for the rest of this request

    if ($user === false) {
        $user = null;
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare('SELECT user_id, name, email, role, student_id, phone, profile_photo
                                   FROM users WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
            if ($user === null) {
                endUserSession(true);    // account no longer exists
            }
        }
    }
    return $user;
}

// Start a logged-in session
function logInUser($userId, $remember = false)
{
    session_regenerate_id(true);                        // new session ID = stops session fixation
    $_SESSION['user_id']       = $userId;
    $_SESSION['fingerprint']   = browserFingerprint();
    $_SESSION['last_activity'] = time();
    $_SESSION['created_at']    = time();
    $_SESSION['csrf_token']    = bin2hex(random_bytes(32));
    if ($remember) {
        createRememberToken($userId);
    }
}

// Log out (keeps the session itself so a flash message can still be shown)
function endUserSession($forgetCookie = true)
{
    if ($forgetCookie) {
        forgetRememberToken();
    }
    unset($_SESSION['user_id'], $_SESSION['fingerprint'], $_SESSION['after_login']);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pages that need an account: sends guests to the login page
function requireLogin()
{
    $user = currentUser();
    if ($user === null) {
        // Remember where to come back to (stored in the session, not the URL)
        $page = basename($_SERVER['SCRIPT_NAME']);
        if (in_array($page, safePages(), true)) {
            $_SESSION['after_login'] = $page;
        }
        flash('error', 'Please log in first.');
        redirect('login.php');
    }
    return $user;
}

// Is this user the organiser of the club? (checked on the SERVER, not just by hiding buttons)
function userOrganisesClub($user, $clubId)
{
    global $pdo;
    if ($user === null || $user['role'] !== 'organiser') {
        return false;
    }
    $stmt = $pdo->prepare('SELECT 1 FROM clubs WHERE club_id = ? AND organiser_id = ?');
    $stmt->execute([$clubId, $user['user_id']]);
    return (bool) $stmt->fetchColumn();
}


/* ============================================================
   7. Flash messages & redirects
   ------------------------------------------------------------
   Messages are kept in the session for ONE page load, so they
   never appear in the URL (no ?error=... links).
   ============================================================ */
function flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Re-open a pop-up (e.g. "event-5") on the next page load
function reopenPopup($popupId)
{
    $_SESSION['flash_open'] = $popupId;
}

// Read and clear the flash data
function takeFlash()
{
    $data = [
        'type'    => $_SESSION['flash']['type'] ?? '',
        'message' => $_SESSION['flash']['message'] ?? '',
        'open'    => $_SESSION['flash_open'] ?? '',
    ];
    unset($_SESSION['flash'], $_SESSION['flash_open']);
    return $data;
}

// Keep what the user typed so a form can be refilled after an error
function keepOldInput($values)
{
    $_SESSION['old_input'] = $values;
}

function takeOldInput()
{
    $old = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    return $old;
}

// The site's folder in the URL, e.g. "/taylors-club-events/" (works from actions/ too)
function baseUrl()
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if (substr($dir, -8) === '/actions') {
        $dir = substr($dir, 0, -8);
    }
    return $dir . '/';
}

// Pages we are allowed to send people back to (stops "open redirect" tricks)
function safePages()
{
    return ['index.php', 'clubs.php', 'calendar.php', 'profile.php', 'edit_profile.php'];
}

function redirect($page)
{
    header('Location: ' . baseUrl() . $page);
    exit;
}

// After an action, go back to the page the form was on
function redirectBack($fallback = 'index.php')
{
    $page = $_POST['return'] ?? '';
    redirect(in_array($page, safePages(), true) ? $page : $fallback);
}

// Hidden field that tells an action which page to go back to
function returnField()
{
    $page = basename($_SERVER['SCRIPT_NAME']);
    return '<input type="hidden" name="return" value="' . htmlspecialchars($page) . '">';
}


/* ============================================================
   8. Input helpers
   ============================================================ */

// A text value from a submitted form ('' if missing or not text)
function input($key)
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

// A positive whole number from the form, or 0
function inputId($key)
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? 0 : $value;
}

// Length in characters (works for any language)
function textLength($text)
{
    return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
}
