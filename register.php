<?php
declare(strict_types=1);

// Registration also gets loaded directly, so it needs its own session check.
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

class RegistrationService {

    public static function validateInput(
        string $name,
        string $email,
        string $password,
        string $confirmPassword
    ): array {
        $errors = [];

        // Leave password spaces alone, they might be intentional.
        $name = trim($name);
        $email = strtolower(trim($email));

        if (strlen($name) < 2 || strlen($name) > 80) {
            $errors[] = "Name must be between 2 and 80 bytes.";
        }

        if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address of up to 255 characters.";
        }

        if (self::emailExists($email)) {
            $errors[] = "This email is already registered.";
        }

        // bcrypt only uses the first 72 bytes.
        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors[] = "Password must be between 8 and 72 bytes.";
        }

        // A null byte would make password_hash fail.
        if (strpos($password, "\0") !== false) {
            $errors[] = "Password must not contain null bytes.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        }

        return $errors;
    }

    public static function emailExists(string $email): bool {
        $email = strtolower(trim($email));

        return isset($_SESSION["demo_users"][$email]);
    }

    public static function registerUser(
        string $name,
        string $email,
        string $password
    ): void {
        // Check again here in case this gets called without the form.
        $errors = self::validateInput($name, $email, $password, $password);
        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        $name = trim($name);
        $email = strtolower(trim($email));

        if (!isset($_SESSION["demo_users"])) {
            $_SESSION["demo_users"] = [];
        }

        $user = [
            // Fine for this demo while accounts cannot be deleted.
            "id" => count($_SESSION["demo_users"]) + 1,
            "name" => $name,
            "email" => $email,
            "password_hash" => password_hash($password, PASSWORD_DEFAULT),
            "role" => "student"
        ];

        $_SESSION["demo_users"][$email] = $user;
    }
}

require_once __DIR__ . '/includes/db.php';
$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $name = trim(input('name'));
    $email = strtolower(trim(input('email')));
    $errors = RegistrationService::validateInput($name, $email, input('password'), input('confirm_password'));
    if ($errors === []) {
        RegistrationService::registerUser($name, $email, input('password'));
        header('Location: login.php?registered=1');
        exit;
    }
}
$pageTitle = 'Register';
