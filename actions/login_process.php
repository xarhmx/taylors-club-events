<?php
/*
 * actions/login_process.php
 * ------------------------------------------------------------
 * Checks the email + password from login.php.
 *
 * Security:
 *  - password_verify() against the bcrypt hash (no plain passwords)
 *  - same error for "wrong email" and "wrong password", so attackers
 *    can't find out which emails have accounts
 *  - account locked for 15 minutes after 5 wrong passwords
 *  - new session ID after login (stops session fixation)
 *  - optional "Remember me" cookie
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();

$email    = strtolower(trim(input('email')));
$password = input('password');
$remember = input('remember') === '1';
$loginTab = input('role') === 'organiser' ? 'login.php?role=organiser' : 'login.php';

function loginFailed($message, $email, $loginTab)
{
    keepOldInput(['email' => $email]);   // refill the email (never the password)
    flash('error', $message);
    redirect($loginTab);
}

if ($email === '' || $password === '') {
    loginFailed('Please enter your email and password.', $email, $loginTab);
}

$stmt = $pdo->prepare('SELECT user_id, password, role, failed_logins, locked_until FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Locked because of too many wrong passwords?
if ($user && $user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
    $minutes = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
    loginFailed("Too many wrong attempts. Please try again in $minutes minute(s).", $email, $loginTab);
}

// Check the password. If the email doesn't exist we still run password_verify on a
// dummy hash so both cases take the same time (attackers can't tell the difference).
$hash = $user ? $user['password'] : '$2y$10$S3XW.6xKqqLqZDlBL5dxwuUt2UfrnMdsWq1g5s6ElaKcCoI5JWo02';
$correct = password_verify($password, $hash) && $user;

if (!$correct) {
    if ($user) {
        $failed = $user['failed_logins'] + 1;
        if ($failed >= MAX_LOGIN_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_MINUTES * 60);
            $pdo->prepare('UPDATE users SET failed_logins = 0, locked_until = ? WHERE user_id = ?')
                ->execute([$lockedUntil, $user['user_id']]);
        } else {
            $pdo->prepare('UPDATE users SET failed_logins = ? WHERE user_id = ?')
                ->execute([$failed, $user['user_id']]);
        }
    }
    loginFailed('Email or password is incorrect.', $email, $loginTab);
}

// Success: reset the counter (and upgrade the hash if PHP has a stronger default now)
$pdo->prepare('UPDATE users SET failed_logins = 0, locked_until = NULL WHERE user_id = ?')
    ->execute([$user['user_id']]);
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $user['user_id']]);
}

logInUser((int) $user['user_id'], $remember);
flash('success', 'Welcome back!');

// Go back to the page they wanted, or a sensible default
$next = $_SESSION['after_login'] ?? ($user['role'] === 'organiser' ? 'clubs.php' : 'index.php');
unset($_SESSION['after_login']);
redirect(in_array($next, safePages(), true) ? $next : 'index.php');
