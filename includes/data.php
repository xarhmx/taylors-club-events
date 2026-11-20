<?php
/*
 * includes/data.php
 * ------------------------------------------------------------
 * Loads everything the pages display from the database.
 * (Replaces the old sample_data.php - same variable names, so the
 * HTML in the pages did not need to change.)
 *
 *   $currentUser      logged-in user, or null for guests
 *   $clubs            all clubs
 *   $events           all events
 *   $clubMembers      committee members + positions
 *   $followedClubIds  clubs the current user follows
 *   $rsvpEventIds     events the current user is going to
 *   $attendeeCounts   [event_id => number of people going]
 *   $feedback         all ratings & comments
 *   $categoryOptions  categories an organiser can choose
 *   $oldInput         form values to refill after an error
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/auth.php';

$currentUser = currentUser();

$clubs = $pdo->query(
    'SELECT club_id, club_name, category, description, image, organiser_id
     FROM clubs
     ORDER BY club_id'
)->fetchAll();

$events = $pdo->query(
    'SELECT event_id, club_id, event_name, description, event_date, event_time, location, image, created_by
     FROM events
     ORDER BY event_date, event_time'
)->fetchAll();

// "14:00:00" from MySQL -> "14:00" for the time inputs
foreach ($events as $index => $event) {
    $events[$index]['event_time'] = substr($event['event_time'], 0, 5);
}

$clubMembers = $pdo->query(
    "SELECT m.club_id, m.user_id, u.name, m.position
     FROM club_members m
     JOIN users u ON u.user_id = m.user_id
     ORDER BY m.club_id,
              CASE m.position WHEN 'Club Head' THEN 1 WHEN 'Assistant Head' THEN 2 ELSE 3 END,
              u.name"
)->fetchAll();

// [event_id => count]
$attendeeCounts = [];
foreach ($pdo->query('SELECT event_id, COUNT(*) AS total FROM rsvps GROUP BY event_id') as $row) {
    $attendeeCounts[(int) $row['event_id']] = (int) $row['total'];
}

$feedback = $pdo->query(
    'SELECT f.event_id, f.user_id, u.name, f.rating, f.comment
     FROM feedback f
     JOIN users u ON u.user_id = f.user_id
     ORDER BY f.created_at DESC'
)->fetchAll();

// Things that depend on who is logged in
$followedClubIds = [];
$rsvpEventIds    = [];
if ($currentUser !== null) {
    $stmt = $pdo->prepare('SELECT club_id FROM club_followers WHERE user_id = ?');
    $stmt->execute([$currentUser['user_id']]);
    $followedClubIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    $stmt = $pdo->prepare('SELECT event_id FROM rsvps WHERE user_id = ?');
    $stmt->execute([$currentUser['user_id']]);
    $rsvpEventIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

$categoryOptions = CLUB_CATEGORIES;

// What the user typed before a form error (used to refill the Add/Edit Event pop-up)
$oldInput = takeOldInput();
