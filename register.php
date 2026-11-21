<?php
/*
 * register.php - Create an account
 * ------------------------------------------------------------
 * Same look as the login page. Sends the details to
 * actions/register_process.php, which checks everything again.
 * register.php?role=organiser pre-selects "Club Organiser".
 * ------------------------------------------------------------
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (currentUser() !== null) {
    redirect('index.php');
}

$role  = (isset($_GET['role']) && $_GET['role'] === 'organiser') ? 'organiser' : 'student';
$flash = takeFlash();
$old   = takeOldInput();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Taylor's Clubopedia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
</head>
<body class="auth-page">

    <div class="auth-card animate">
        <div class="auth-header">
            <a href="index.php">
                <img src="images/logo.png" alt="Taylor's University" class="auth-logo">
            </a>
            <h1 class="auth-title">Create your account</h1>
            <p class="auth-subtitle">Join Taylor's Clubopedia to follow clubs and RSVP to events.</p>
        </div>

        <?php if ($flash['message'] !== ''): ?>
            <div class="alert alert-error" role="alert"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <form action="actions/register_process.php" method="POST" id="registerForm">
            <?= csrfField() ?>

            <!-- Account type: Student or Club Organiser -->
            <fieldset class="role-switch">
                <legend class="visually-hidden">Account type</legend>
                <label>
                    <input type="radio" name="role" value="student" <?= $role === 'student' ? 'checked' : '' ?>>
                    <span>Student</span>
                </label>
                <label>
                    <input type="radio" name="role" value="organiser" <?= $role === 'organiser' ? 'checked' : '' ?>>
                    <span>Club Organiser</span>
                </label>
            </fieldset>

            <div class="form-row">
                <label for="name">Full Name:</label>
                <div class="input-wrap">
                    <input class="input-line" type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" maxlength="80" autocomplete="name" required>
                </div>
            </div>

            <div class="form-row">
                <label for="student_id">Student ID:</label>
                <div class="input-wrap">
                    <input class="input-line" type="text" id="student_id" name="student_id" value="<?= e($old['student_id'] ?? '') ?>" maxlength="20" pattern="[A-Za-z0-9]{5,20}"
                           inputmode="numeric" placeholder="e.g. 0362514" required>
                </div>
            </div>

            <div class="form-row">
                <label for="email">Email Address:</label>
                <div class="input-wrap">
                    <input class="input-line" type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" maxlength="191"
                           placeholder="you@sd.taylors.edu.my" autocomplete="email" required>
                </div>
            </div>

            <div class="form-row">
                <label for="password">Password:</label>
                <div class="input-wrap">
                    <input class="input-line" type="password" id="password" name="password"
                           minlength="8" maxlength="72" placeholder="At least 8 characters" autocomplete="new-password" required>
                    <button type="button" class="toggle-password" data-target="password">Show</button>
                </div>
            </div>

            <div class="form-row">
                <label for="confirm_password">Confirm:</label>
                <div class="input-wrap">
                    <input class="input-line" type="password" id="confirm_password" name="confirm_password"
                           minlength="8" maxlength="72" placeholder="Type it again" autocomplete="new-password" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Register</button>
                <p class="auth-footnote">Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </form>
    </div>

    <script src="js/script.js?v=<?= filemtime(__DIR__ . '/js/script.js') ?>"></script>
</body>
</html>
