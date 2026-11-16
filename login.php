<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';

class LoginService {
    public static function findUserByEmail(string $email): ?array {
        // Match the normalization used during registration.
        $email = strtolower(trim($email));
        $user = $_SESSION['demo_users'][$email] ?? null;
        return is_array($user) ? $user : null;
    }
    public static function login(string $email, string $password): bool {
        $user = self::findUserByEmail($email);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        // Give the signed in user a fresh session ID.
        session_regenerate_id(true);
        // The pages only need the display fields.
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
}
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $email = trim(input('email'));
    if (LoginService::login($email, input('password'))) {
        header('Location: profile.php');
        exit;
    }
    $error = 'Email or password is incorrect.';
}
$pageTitle = 'Log in';
