<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$club = SampleData::getClubById(requestedId());
if ($club === null) { notFound('This club does not exist.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = requireLogin();
    checkCsrf();
    if (isset($_SESSION['following'][$user['id']][$club['id']])) {
        unset($_SESSION['following'][$user['id']][$club['id']]);
    } else {
        $_SESSION['following'][$user['id']][$club['id']] = true;
    }
    // Refreshing should not toggle the follow a second time.
    header('Location: club.php?id=' . $club['id']);
    exit;
}
$pageTitle = $club['name'];
$events = $eventRepository->forClub($club['id']);
