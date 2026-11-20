<?php
/*
 * login.php
 * ------------------------------------------------------------
 * One login page for both account types:
 *   login.php                -> Student login
 *   login.php?role=organiser -> Club Organiser login
 *
 * The form sends the email, password and role to
 * actions/login_process.php (written by the back-end).
 * ------------------------------------------------------------
 */
session_start();

// Which login are we showing? Anything other than "organiser" = student
$role = (isset($_GET['role']) && $_GET['role'] === 'organiser') ? 'organiser' : 'student';
$isOrganiser = $role === 'organiser';

$roleLabel  = $isOrganiser ? 'Club Organiser' : 'Student';
$switchLink = $isOrganiser ? 'login.php' : 'login.php?role=organiser';
$switchText = $isOrganiser ? 'Student? Click here!' : 'Club Organizer? Click here!';

// login_process.php can send the user back with ?error=... if login fails
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $roleLabel ?> Login | Taylor's Club Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

    <div class="auth-card animate">
        <div class="auth-header">
            <a href="index.php">
                <img src="images/logo.png" alt="Taylor's University" class="auth-logo" onerror="this.remove()">
            </a>
            <span class="role-pill"><?= $roleLabel ?></span>
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Log in to follow clubs and join events.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="actions/login_process.php" method="POST">
            <!-- Tells the back-end which type of account is logging in -->
            <input type="hidden" name="role" value="<?= $role ?>">

            <div class="form-row">
                <label for="email">Email Address:</label>
                <div class="input-wrap">
                    <input class="input-line" type="email" id="email" name="email"
                           placeholder="you@sd.taylors.edu.my" autocomplete="email" required>
                </div>
            </div>

            <div class="form-row">
                <label for="password">Password:</label>
                <div class="input-wrap">
                    <input class="input-line" type="password" id="password" name="password"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="toggle-password" data-target="password">Show</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Login</button>
                <br>
                <a href="<?= $switchLink ?>" class="switch-role"><?= $switchText ?></a>
                <p class="auth-footnote">Don't have an account? <a href="register.php<?= $isOrganiser ? '?role=organiser' : '' ?>">Register</a></p>
            </div>
        </form>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
