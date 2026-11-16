<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the Log out button to log out.');
}
checkCsrf();
// Keep the demo account and bookings so logging back in still works.
unset($_SESSION['user']);
session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
header('Location: login.php');
exit;
