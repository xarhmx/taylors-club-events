<?php
/*
 * actions/rsvp_event.php
 * ------------------------------------------------------------
 * RSVP / Cancel RSVP button in the event pop-up.
 * Receives: event_id, action ('rsvp' or 'cancel')
 * Only upcoming events can be RSVPed.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();
$user = requireLogin();

$eventId = inputId('event_id');
$action  = input('action');

$stmt = $pdo->prepare('SELECT event_name, event_date FROM events WHERE event_id = ?');
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event || !in_array($action, ['rsvp', 'cancel'], true)) {
    flash('error', 'That event could not be found.');
    redirectBack('calendar.php');
}

reopenPopup('event-' . $eventId);   // show the event again after the page reloads

if ($event['event_date'] < date('Y-m-d')) {
    flash('error', 'This event has already happened.');
    redirectBack('calendar.php');
}

if ($action === 'rsvp') {
    $stmt = $pdo->prepare('SELECT 1 FROM rsvps WHERE user_id = ? AND event_id = ?');
    $stmt->execute([$user['user_id'], $eventId]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT INTO rsvps (user_id, event_id) VALUES (?, ?)')
            ->execute([$user['user_id'], $eventId]);
    }
    flash('success', "You're going to {$event['event_name']}!");
} else {
    $pdo->prepare('DELETE FROM rsvps WHERE user_id = ? AND event_id = ?')
        ->execute([$user['user_id'], $eventId]);
    flash('success', 'RSVP cancelled.');
}

redirectBack('calendar.php');
