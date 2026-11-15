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

// Find one club in a list of clubs by its id
function findClub($clubs, $id)
{
    foreach ($clubs as $club) {
        if ($club['id'] == $id) {
            return $club;
        }
    }
    return null;
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

// Events happening today or later, soonest first
function upcomingEvents($events)
{
    $today = date('Y-m-d');
    $list = array_filter($events, fn($ev) => $ev['date'] >= $today);
    usort($list, fn($a, $b) => strcmp($a['date'] . $a['time'], $b['date'] . $b['time']));
    return $list;
}

// Events that already happened, most recent first
function pastEvents($events)
{
    $today = date('Y-m-d');
    $list = array_filter($events, fn($ev) => $ev['date'] < $today);
    usort($list, fn($a, $b) => strcmp($b['date'], $a['date']));
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
