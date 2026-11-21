<?php
/*
 * includes/header.php
 * ------------------------------------------------------------
 * Shared top part of every page: <head>, navigation bar and
 * one-time (flash) messages.
 *
 * The page must include includes/data.php BEFORE this file.
 * Before including this file, a page can set:
 *   $pageTitle  - text shown in the browser tab
 *   $activePage - 'home', 'clubs', 'calendar' or 'profile'
 * ------------------------------------------------------------
 */
$pageTitle  = $pageTitle  ?? 'Home';
$activePage = $activePage ?? '';
$flash      = takeFlash();

// Returns ' active' for the link of the page we are on
function navActive($page, $activePage)
{
    return $page === $activePage ? ' active' : '';
}

// CACHE BUSTING: adds ?v=<last modified time> to CSS/JS links.
// Browsers keep these files cached (fast), but as soon as a file
// changes its link changes too, so the new version is downloaded.
function asset($path)
{
    $file = __DIR__ . '/../' . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Taylor's Clubopedia</title>

    <!-- Inter font (same font used in the Figma design) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

<!-- One-time message from the last action (read by js/script.js) -->
<div id="flashData" hidden
     data-type="<?= e($flash['type']) ?>"
     data-message="<?= e($flash['message']) ?>"
     data-open="<?= e($flash['open']) ?>"></div>

<header class="site-header">
    <nav class="navbar container">
        <a href="index.php" class="brand" aria-label="Home">
            <img src="images/logo.png" alt="" class="brand-logo">
            <span class="brand-text">Club<strong>opedia</strong></span>
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

            <?php if ($currentUser !== null): ?>
                <!-- Log out is a form (POST + CSRF token), not a plain link -->
                <form action="logout.php" method="POST" class="nav-auth">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-outline">Log out</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline nav-auth">Sign in</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="page">
