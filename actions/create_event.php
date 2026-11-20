<?php
/*
 * actions/create_event.php
 * ------------------------------------------------------------
 * "Add Event" pop-up on clubs.php (organisers only).
 * Receives: club_id, event_name, event_date, event_time, location, description
 *
 * Security: the server checks the user really organises this club,
 * so hiding the button is not the only protection.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
requirePost();
checkCsrf();
$user = requireLogin();

$clubId = inputId('club_id');
if (!userOrganisesClub($user, $clubId)) {
    flash('error', 'Only the organiser of this club can add events.');
    redirectBack('clubs.php');
}

$values = eventInput();
$errors = validateEvent($values, true);

if ($errors) {
    keepOldInput(['form' => 'add-event-' . $clubId] + $values);   // refill the pop-up
    reopenPopup('add-event-' . $clubId);
    flash('error', implode(' ', $errors));
    redirectBack('clubs.php');
}

$stmt = $pdo->prepare('INSERT INTO events (club_id, event_name, description, event_date, event_time, location, created_by)
                       VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$clubId, $values['event_name'], $values['description'], $values['event_date'],
                $values['event_time'], $values['location'], $user['user_id']]);

reopenPopup('event-' . $pdo->lastInsertId());   // show the new event
flash('success', 'Event created!');
redirectBack('clubs.php');
