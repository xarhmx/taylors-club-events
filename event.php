<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$event = $eventRepository->find(requestedId());
// Old or edited links can point to an event that does not exist.
if ($event === null) { notFound('This event does not exist.'); }
$club = SampleData::getClubById($event['club_id']);
$summary = $participation->ratingSummary($event['id']);
$pageTitle = $event['title'];
