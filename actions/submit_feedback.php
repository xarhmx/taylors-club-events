<?php
/*
 * actions/submit_feedback.php
 * ------------------------------------------------------------
 * Star rating + comment form for past events.
 * Receives: event_id, rating (1-5), comment (optional)
 *
 * Rules (from the back-end draft's canReview):
 *  - the event must already have happened
 *  - the user must have RSVPed to it
 *  - one review per user per event (sending again updates it)
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();
$user = requireLogin();

$eventId = inputId('event_id');
$rating  = filter_var(input('rating'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$comment = trim(input('comment'));

$stmt = $pdo->prepare('SELECT event_date FROM events WHERE event_id = ?');
$stmt->execute([$eventId]);
$eventDate = $stmt->fetchColumn();

if ($eventDate === false) {
    flash('error', 'That event could not be found.');
    redirectBack('profile.php');
}

reopenPopup('event-' . $eventId);

$stmt = $pdo->prepare('SELECT 1 FROM rsvps WHERE user_id = ? AND event_id = ?');
$stmt->execute([$user['user_id'], $eventId]);
$wentToEvent = (bool) $stmt->fetchColumn();

if ($eventDate >= date('Y-m-d')) {
    flash('error', 'You can rate this event after it has happened.');
} elseif (!$wentToEvent) {
    flash('error', 'Only people who RSVPed can rate this event.');
} elseif ($rating === false) {
    flash('error', 'Please choose 1 to 5 stars.');
} elseif (textLength($comment) > 500) {
    flash('error', 'Comments can be at most 500 characters.');
} else {
    $stmt = $pdo->prepare('SELECT feedback_id FROM feedback WHERE user_id = ? AND event_id = ?');
    $stmt->execute([$user['user_id'], $eventId]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $pdo->prepare('UPDATE feedback SET rating = ?, comment = ? WHERE feedback_id = ?')
            ->execute([$rating, $comment, $existingId]);
    } else {
        $pdo->prepare('INSERT INTO feedback (user_id, event_id, rating, comment) VALUES (?, ?, ?, ?)')
            ->execute([$user['user_id'], $eventId, $rating, $comment]);
    }
    flash('success', 'Thanks for your feedback!');
}

redirectBack('profile.php');
