<?php
/*
 * includes/functions.php
 * ------------------------------------------------------------
 * Small helper functions shared by every page.
 * ------------------------------------------------------------
 */

// Safely print text into HTML (stops XSS / broken HTML from user input)
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Find one club in a list of clubs by its club_id
function findClub($clubs, $clubId)
{
    foreach ($clubs as $club) {
        if ($club['club_id'] == $clubId) {
            return $club;
        }
    }
    return null;
}

// All events that belong to one club
function eventsForClub($events, $clubId)
{
    return array_filter($events, fn($ev) => $ev['club_id'] == $clubId);
}

// Is this user the organiser of this club?
function isOrganiserOf($club, $user)
{
    return $user['role'] === 'organiser' && $club['organiser_id'] == $user['user_id'];
}

// Turn "Computer Science Society" into "CS" (used for placeholder club images)
function initials($name)
{
    $result = '';
    foreach (preg_split('/\s+/', trim($name)) as $word) {
        if ($word !== '' && ctype_alpha($word[0])) {
            $result .= strtoupper($word[0]);
        }
        if (strlen($result) === 2) {
            break;
        }
    }
    return $result;
}

// A different colour for each club's placeholder image
function clubHue($clubId)
{
    return ($clubId * 53) % 360;
}

// Has this event already happened?
function isPast($event)
{
    return $event['event_date'] < date('Y-m-d');
}

// Events happening today or later, soonest first
function upcomingEvents($events)
{
    $list = array_filter($events, fn($ev) => !isPast($ev));
    usort($list, fn($a, $b) => strcmp($a['event_date'] . $a['event_time'], $b['event_date'] . $b['event_time']));
    return $list;
}

// Events that already happened, most recent first
function pastEvents($events)
{
    $list = array_filter($events, 'isPast');
    usort($list, fn($a, $b) => strcmp($b['event_date'], $a['event_date']));
    return $list;
}

// "2026-11-21" -> "Sat, 21 Nov 2026"
function niceDate($date)
{
    return date('D, j M Y', strtotime($date));
}

// "18:00" -> "6:00 PM"
function niceTime($time)
{
    return date('g:i A', strtotime($time));
}

// 4 -> "★★★★☆"
function stars($rating)
{
    $rating = max(0, min(5, (int) $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

// The round profile picture: the uploaded photo, or a person icon if there isn't one
function avatarHtml($user)
{
    if (!empty($user['profile_photo'])) {
        return '<img src="images/profiles/' . e($user['profile_photo']) . '" alt="">';
    }
    return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
         . '<circle cx="12" cy="8" r="4.2"/><path d="M3.5 21c0-4.4 3.8-7.5 8.5-7.5s8.5 3.1 8.5 7.5z"/></svg>';
}

// Clubs the user is in (follows or holds a position in), with their position
// e.g. [['club' => [...], 'position' => 'Secretary'], ...]
function clubsWithPositions($clubs, $clubMembers, $followedClubIds, $userId)
{
    $result = [];
    foreach ($clubs as $club) {
        $position = null;
        foreach ($clubMembers as $member) {
            if ($member['club_id'] == $club['club_id'] && $member['user_id'] == $userId) {
                $position = $member['position'];
            }
        }
        if ($position !== null || in_array($club['club_id'], $followedClubIds)) {
            $result[] = ['club' => $club, 'position' => $position ?? 'Member'];
        }
    }
    return $result;
}
