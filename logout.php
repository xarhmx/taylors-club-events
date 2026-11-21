<?php
/*
 * logout.php
 * ------------------------------------------------------------
 * Logs the user out. Only works from the Log out button (POST +
 * CSRF token), so a link or image on another site can't log
 * people out. (From the back-end draft.)
 *
 * Also deletes the "Remember me" cookie and its database token,
 * and clears the browser's saved copies of our pages.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/includes/auth.php';
requirePost();
checkCsrf();

endUserSession(true);

// Tell the browser to throw away any pages it saved from this site,
// so pressing Back after logging out can't show a logged-in page.
header('Clear-Site-Data: "cache"');
flash('success', 'You have been logged out.');
redirect('login.php');
