<?php
/*
 * includes/header.php
 * ------------------------------------------------------------
 * Shared top part of every page: <head>, navigation bar.
 *
 * Before including this file, a page can set:
 *   $pageTitle  - text shown in the browser tab
 *   $activePage - 'home', 'clubs', 'calendar' or 'profile'
 *                 (highlights the matching nav link)
 * ------------------------------------------------------------
 */

// Start the session so we can check whether someone is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$pageTitle  = $pageTitle  ?? 'Home';
$activePage = $activePage ?? '';

// Returns ' active' for the link of the page we are on
function navActive($page, $activePage)
{
    return $page === $activePage ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Taylor's Club Events</title>

    <!-- Inter font (same font used in the Figma design) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar container">
        <!-- Logo: put the Taylor's logo from Figma at images/logo.png -->
        <a href="index.php" class="brand" aria-label="Home">
            <img src="images/logo.png" alt="" class="brand-logo" onerror="this.remove()">
            <span class="brand-text">Club<strong>Events</strong></span>
        </a>

        <!-- Hamburger button (only visible on small screens) -->
        <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="navMenu">
            <span></span><span></span><span></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <ul class="nav-links">
                <li><a href="clubs.php"    class="nav-link<?= navActive('clubs', $activePage) ?>">Clubs</a></li>
                <li><a href="calendar.php" class="nav-link<?= navActive('calendar', $activePage) ?>">Calendar</a></li>
                <li><a href="profile.php"  class="nav-link<?= navActive('profile', $activePage) ?>">Profile</a></li>
            </ul>

            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="btn btn-outline nav-auth">Log out</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline nav-auth">Sign in</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="page">
