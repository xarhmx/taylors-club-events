<?php
/*
 * actions/delete_event.php
 * ------------------------------------------------------------
 * "Delete" button in the event pop-up (organisers only).
 * Receives: event_id
 * The event's RSVPs and feedback are removed automatically by the
 * database (ON DELETE CASCADE).
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();
$user = requireLogin();

$eventId = inputId('event_id');
$stmt = $pdo->prepare('SELECT club_id, event_name FROM events WHERE event_id = ?');
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event || !userOrganisesClub($user, $event['club_id'])) {
    flash('error', 'You can only delete events from your own club.');
    redirectBack('clubs.php');
}

$pdo->prepare('DELETE FROM events WHERE event_id = ?')->execute([$eventId]);

flash('success', "\"{$event['event_name']}\" was deleted.");
redirectBack('clubs.php');
