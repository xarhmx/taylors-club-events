<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$user = requireLogin();
$pageTitle = 'My profile';

$events = [];
foreach ($eventRepository->all() as $event) {
    if (isset($_SESSION['rsvps'][$user['id']][$event['id']])) { $events[] = $event; }
}
$followedClubs = [];
foreach (SampleData::getClubs() as $club) {
    if (isset($_SESSION['following'][$user['id']][$club['id']])) { $followedClubs[] = $club; }
}
