<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$user = requireLogin();
// Hiding the nav link alone would not protect this page.
if ($user['role'] !== 'organiser') {
    http_response_code(403);
    $pageTitle = 'Access restricted';
    echo 'Only organiser accounts can view this dashboard.';
    exit;
}
$errors = [];
$values = ['title' => '', 'club_id' => '', 'date' => '', 'time' => '', 'location' => '', 'description' => '', 'capacity' => '30'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    // Only take the fields this form actually uses.
    foreach ($values as $key => $value) { $values[$key] = input($key); }
    $errors = $eventService->validate($values);
    if ($errors === []) {
        $id = $eventService->create($values, $user);
        header('Location: event.php?id=' . $id);
        exit;
    }
}
$pageTitle = 'Organiser dashboard';

$events = $eventRepository->all();
$eventSummaries = [];
foreach ($events as $event) {
    $eventSummaries[$event['id']] = $participation->ratingSummary($event['id']);
}
