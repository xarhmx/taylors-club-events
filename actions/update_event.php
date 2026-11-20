<?php
/*
 * actions/update_event.php
 * ------------------------------------------------------------
 * "Edit Event" pop-up (organisers only).
 * Receives: event_id, event_name, event_date, event_time, location, description
 * The club is taken from the database, not from the form, so an
 * organiser can't move an event into someone else's club.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
requirePost();
checkCsrf();
$user = requireLogin();

$eventId = inputId('event_id');
$stmt = $pdo->prepare('SELECT club_id FROM events WHERE event_id = ?');
$stmt->execute([$eventId]);
$clubId = $stmt->fetchColumn();

if ($clubId === false || !userOrganisesClub($user, $clubId)) {
    flash('error', 'You can only edit events from your own club.');
    redirectBack('clubs.php');
}

$values = eventInput();
$errors = validateEvent($values, false);

if ($errors) {
    keepOldInput(['form' => 'edit-event-' . $eventId] + $values);
    reopenPopup('edit-event-' . $eventId);
    flash('error', implode(' ', $errors));
    redirectBack('clubs.php');
}

$stmt = $pdo->prepare('UPDATE events
                       SET event_name = ?, description = ?, event_date = ?, event_time = ?, location = ?
                       WHERE event_id = ?');
$stmt->execute([$values['event_name'], $values['description'], $values['event_date'],
                $values['event_time'], $values['location'], $eventId]);

reopenPopup('event-' . $eventId);
flash('success', 'Event updated.');
redirectBack('clubs.php');
