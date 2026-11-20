<?php
/*
 * includes/validation.php
 * ------------------------------------------------------------
 * Server-side checks for form data. The browser also checks
 * (required, maxlength ...) but that can be bypassed, so PHP
 * always checks again before saving anything.
 * ------------------------------------------------------------
 */

// "2026-12-01" -> true, "2026-02-31" or "hello" -> false
function isValidDate($date)
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

// "14:30" -> true, "25:00" -> false
function isValidTime($time)
{
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time);
}

// Checks the Add/Edit Event form. Returns a list of problems (empty = OK).
function validateEvent($values, $isNew)
{
    $errors = [];

    if (textLength($values['event_name']) < 3 || textLength($values['event_name']) > 150) {
        $errors[] = 'Event name must be 3 to 150 characters.';
    }
    if (!isValidDate($values['event_date'])) {
        $errors[] = 'Please choose a valid date.';
    } elseif ($isNew && $values['event_date'] < date('Y-m-d')) {
        $errors[] = 'New events cannot be in the past.';
    }
    if (!isValidTime($values['event_time'])) {
        $errors[] = 'Please choose a valid time.';
    }
    if (textLength($values['location']) < 2 || textLength($values['location']) > 150) {
        $errors[] = 'Venue must be 2 to 150 characters.';
    }
    if (textLength($values['description']) > 1000) {
        $errors[] = 'Description can be at most 1000 characters.';
    }
    return $errors;
}

// Reads the event fields from the submitted form
function eventInput()
{
    return [
        'event_name'  => trim(input('event_name')),
        'event_date'  => trim(input('event_date')),
        'event_time'  => substr(trim(input('event_time')), 0, 5),
        'location'    => trim(input('location')),
        'description' => trim(input('description')),
    ];
}
