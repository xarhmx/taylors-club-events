<?php
/*
 * includes/sample_data.php
 * ------------------------------------------------------------
 * TEMPORARY dummy data so the front-end can be built and tested
 * before the database is connected.
 *
 * Later, the back-end replaces these arrays with SELECT queries
 * from MySQL that return the SAME keys (id, name, category ...),
 * so the HTML in each page does not need to change.
 * ------------------------------------------------------------
 */

// Event dates are relative to today so the demo always has upcoming events
function daysFromToday($days)
{
    return date('Y-m-d', strtotime("$days days"));
}

$clubs = [
    ['id' => 1, 'name' => 'Computer Science Society', 'category' => 'Academic',
     'description' => 'Coding nights, hackathons and tech talks for anyone curious about computing.'],
    ['id' => 2, 'name' => 'Tamil Society', 'category' => 'Cultural',
     'description' => 'Celebrating Tamil culture through festivals, music, food and friendship.'],
    ['id' => 3, 'name' => 'Game Development Club', 'category' => 'Technology',
     'description' => 'Design, build and playtest games together, from weekend game jams to workshops.'],
    ['id' => 4, 'name' => 'Photography Club', 'category' => 'Arts',
     'description' => 'Photo walks, editing sessions and exhibitions around campus and the city.'],
    ['id' => 5, 'name' => 'Badminton Club', 'category' => 'Sports',
     'description' => 'Weekly training sessions and friendly matches for every skill level.'],
    ['id' => 6, 'name' => 'Debate Society', 'category' => 'Academic',
     'description' => 'Sharpen your public speaking and compete in inter-university tournaments.'],
];

$events = [
    ['id' => 1, 'club_id' => 2, 'title' => 'Tamil Society Welcoming Party', 'date' => daysFromToday(6),  'time' => '18:00', 'location' => 'Grand Hall',        'attendees' => 86],
    ['id' => 2, 'club_id' => 3, 'title' => 'Game Dev Workshop',             'date' => daysFromToday(6),  'time' => '14:00', 'location' => 'Lab C3.12',         'attendees' => 34],
    ['id' => 3, 'club_id' => 1, 'title' => 'Intro to Web Hackathon',        'date' => daysFromToday(10), 'time' => '09:00', 'location' => 'Library Level 2',   'attendees' => 120],
    ['id' => 4, 'club_id' => 4, 'title' => 'Night Photo Walk',              'date' => daysFromToday(13), 'time' => '19:30', 'location' => 'Lakeside',          'attendees' => 22],
    ['id' => 5, 'club_id' => 5, 'title' => 'Inter-Club Badminton Friendly', 'date' => daysFromToday(18), 'time' => '16:00', 'location' => 'Sports Complex',    'attendees' => 48],
    ['id' => 6, 'club_id' => 6, 'title' => 'Public Speaking Bootcamp',      'date' => daysFromToday(25), 'time' => '10:00', 'location' => 'Lecture Theatre 4', 'attendees' => 40],
    // Past events (used on the profile page)
    ['id' => 7, 'club_id' => 1, 'title' => 'Python for Beginners',          'date' => daysFromToday(-8),  'time' => '15:00', 'location' => 'Lab C3.10',        'attendees' => 65],
    ['id' => 8, 'club_id' => 3, 'title' => 'Mini Game Jam',                 'date' => daysFromToday(-14), 'time' => '10:00', 'location' => 'Block D',          'attendees' => 30],
];

// The "logged in" user. Later this comes from $_SESSION + the users table.
$currentUser = [
    'id'                => 1,
    'name'              => 'John Doe',
    'email'             => 'johndoe@gmail.com',
    'role'              => 'student',          // 'student' or 'organiser'
    'followed_club_ids' => [1, 3, 5],
    'rsvp_event_ids'    => [2, 3, 5, 7, 8],
];
