<?php
/*
 * actions/register_process.php
 * ------------------------------------------------------------
 * Creates a new account from register.php.
 * Validation rules come from the back-end draft (RegistrationService),
 * now saved in the users table instead of the session.
 *
 * Security:
 *  - every field checked on the server
 *  - password stored only as a bcrypt hash (password_hash)
 *  - prepared statement for the INSERT
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();

$name      = trim(input('name'));
$studentId = trim(input('student_id'));
$email     = strtolower(trim(input('email')));
$password  = input('password');               // spaces in passwords are allowed, so no trim
$confirm   = input('confirm_password');
$role      = input('role') === 'organiser' ? 'organiser' : 'student';   // only these two are allowed

$errors = [];

if (textLength($name) < 2 || textLength($name) > 80) {
    $errors[] = 'Name must be 2 to 80 characters.';
}
if (!preg_match('/^[A-Za-z0-9]{5,20}$/', $studentId)) {
    $errors[] = 'Student ID must be 5 to 20 letters or numbers.';
}
if (strlen($email) > 191 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8 || strlen($password) > 72) {   // bcrypt only uses the first 72 bytes
    $errors[] = 'Password must be 8 to 72 characters.';
}
if (strpos($password, "\0") !== false) {                  // a null byte breaks password_hash
    $errors[] = 'Password contains an invalid character.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if (!$errors) {
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        $errors[] = 'This email is already registered.';
    }
}

$registerPage = $role === 'organiser' ? 'register.php?role=organiser' : 'register.php';

if ($errors) {
    keepOldInput(['name' => $name, 'student_id' => $studentId, 'email' => $email]);   // never the password
    flash('error', implode(' ', $errors));
    redirect($registerPage);
}

$stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, student_id) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $studentId]);

keepOldInput(['email' => $email]);
flash('success', 'Account created! You can log in now.');
redirect($role === 'organiser' ? 'login.php?role=organiser' : 'login.php');
