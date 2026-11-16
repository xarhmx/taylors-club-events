<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$events = $eventRepository->all();
// ISO dates sort correctly as strings, with time breaking ties.
usort($events, function (array $first, array $second): int {
    return strcmp($first['date'] . $first['time'], $second['date'] . $second['time']);
});
$pageTitle = 'Event calendar';
