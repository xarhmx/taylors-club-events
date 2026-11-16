<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
// Treat an empty search as all events for the selected filters.
$query = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$clubId = filter_input(INPUT_GET, 'club', FILTER_VALIDATE_INT) ?: 0;
$period = is_string($_GET['period'] ?? null) ? $_GET['period'] : 'upcoming';
// Fall back if the URL has an unknown period.
if (!in_array($period, ['upcoming', 'past', 'all'], true)) { $period = 'upcoming'; }
$events = $eventService->search($query, $clubId, $period);
$pageTitle = 'Upcoming events';
