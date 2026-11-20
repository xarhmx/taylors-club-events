<?php
/*
 * logout.php
 * ------------------------------------------------------------
 * Logs the user out. Only works from the Log out button (POST +
 * CSRF token), so a link or image on another site can't log
 * people out. (From the back-end draft.)
 *
 * Also deletes the "Remember me" cookie and its database token.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/includes/auth.php';
requirePost();
checkCsrf();

endUserSession(true);
flash('success', 'You have been logged out.');
redirect('login.php');
