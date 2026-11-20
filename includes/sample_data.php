<?php
/*
 * includes/sample_data.php
 * ------------------------------------------------------------
 * TEMPORARY dummy data so the front-end can be built and tested
 * before the database is connected.
 *
 * The keys match the column names in database/taylors_club_events.sql
 * (club_id, club_name, event_name, event_date ...). Later, the back-end
 * replaces each array below with a SELECT query that returns the same
 * columns, so the HTML in the pages does not need to change.
 *
 * DEMO SWITCH: add ?as=organiser or ?as=student to any page URL to
 * preview the site as that type of user (remembered until you switch).
 * ------------------------------------------------------------
 */

define('USING_SAMPLE_DATA', true);   // shows the demo bar in the footer

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['as']) && in_array($_GET['as'], ['student', 'organiser'])) {
    $_SESSION['demo_as'] = $_GET['as'];
}

// Event dates are relative to today so the demo always has upcoming events
function daysFromToday($days)
{
    return date('Y-m-d', strtotime("$days days"));
}

/* ---------- users ---------- */
$users = [
    1 => ['user_id' => 1, 'name' => 'John Doe',     'email' => 'johndoe@gmail.com',   'role' => 'student',
          'student_id' => '0362514', 'phone' => '012-345 6789', 'profile_photo' => null],
    2 => ['user_id' => 2, 'name' => 'Aisha Rahman', 'email' => 'aisha@sd.taylors.edu.my', 'role' => 'organiser',
          'student_id' => '0358821', 'phone' => '',             'profile_photo' => null],
];

// The logged-in user. Later this comes from $_SESSION['user_id'] + the users table.
$currentUser = ($_SESSION['demo_as'] ?? 'student') === 'organiser' ? $users[2] : $users[1];

/* ---------- clubs ---------- */
$clubs = [
    ['club_id' => 1, 'club_name' => 'Computer Science Society', 'category' => 'Academic',   'image' => null, 'organiser_id' => 2,
     'description' => 'Coding nights, hackathons and tech talks for anyone curious about computing.'],
    ['club_id' => 2, 'club_name' => 'Tamil Society',            'category' => 'Cultural',   'image' => null, 'organiser_id' => null,
     'description' => 'Celebrating Tamil culture through festivals, music, food and friendship.'],
    ['club_id' => 3, 'club_name' => 'Game Development Club',    'category' => 'Technology', 'image' => null, 'organiser_id' => null,
     'description' => 'Design, build and playtest games together, from weekend game jams to workshops.'],
    ['club_id' => 4, 'club_name' => 'Photography Club',         'category' => 'Arts',       'image' => null, 'organiser_id' => null,
     'description' => 'Photo walks, editing sessions and exhibitions around campus and the city.'],
    ['club_id' => 5, 'club_name' => 'Badminton Club',           'category' => 'Sports',     'image' => null, 'organiser_id' => null,
     'description' => 'Weekly training sessions and friendly matches for every skill level.'],
    ['club_id' => 6, 'club_name' => 'Debate Society',           'category' => 'Academic',   'image' => null, 'organiser_id' => null,
     'description' => 'Sharpen your public speaking and compete in inter-university tournaments.'],
];

// Categories an organiser can pick from when editing their club
$categoryOptions = ['Academic', 'Arts', 'Community', 'Cultural', 'Sports', 'Technology'];

/* ---------- club_members (NEW table: committee positions) ---------- */
$clubMembers = [
    ['club_id' => 1, 'user_id' => 2,    'name' => 'Aisha Rahman', 'position' => 'Club Head'],
    ['club_id' => 1, 'user_id' => null, 'name' => 'Daniel Lim',   'position' => 'Assistant Head'],
    ['club_id' => 1, 'user_id' => null, 'name' => 'Priya Nair',   'position' => 'Treasurer'],
    ['club_id' => 1, 'user_id' => 1,    'name' => 'John Doe',     'position' => 'Event Crew'],
    ['club_id' => 2, 'user_id' => null, 'name' => 'Kavin Raj',    'position' => 'Club Head'],
    ['club_id' => 2, 'user_id' => null, 'name' => 'Meera Devi',   'position' => 'Assistant Head'],
    ['club_id' => 3, 'user_id' => null, 'name' => 'Marcus Tan',   'position' => 'Club Head'],
    ['club_id' => 3, 'user_id' => null, 'name' => 'Sofia Chen',   'position' => 'Assistant Head'],
    ['club_id' => 3, 'user_id' => 1,    'name' => 'John Doe',     'position' => 'Secretary'],
    ['club_id' => 4, 'user_id' => null, 'name' => 'Hana Yusof',   'position' => 'Club Head'],
    ['club_id' => 5, 'user_id' => null, 'name' => 'Ryan Ong',     'position' => 'Club Head'],
    ['club_id' => 5, 'user_id' => null, 'name' => 'Nur Iman',     'position' => 'Assistant Head'],
    ['club_id' => 6, 'user_id' => null, 'name' => 'Ethan Wong',   'position' => 'Club Head'],
];

/* ---------- events ---------- */
$events = [
    ['event_id' => 1, 'club_id' => 2, 'event_name' => 'Tamil Society Welcoming Party', 'event_date' => daysFromToday(6),  'event_time' => '18:00', 'location' => 'Grand Hall',
     'description' => 'Meet the committee and new members over food, music and games. Everyone is welcome!'],
    ['event_id' => 2, 'club_id' => 3, 'event_name' => 'Game Dev Workshop',             'event_date' => daysFromToday(6),  'event_time' => '14:00', 'location' => 'Lab C3.12',
     'description' => 'A hands-on intro to making your first 2D game in Godot. Bring a laptop.'],
    ['event_id' => 3, 'club_id' => 1, 'event_name' => 'Intro to Web Hackathon',        'event_date' => daysFromToday(10), 'event_time' => '09:00', 'location' => 'Library Level 2',
     'description' => 'Team up and build a website in one day. Mentors, snacks and prizes provided.'],
    ['event_id' => 4, 'club_id' => 4, 'event_name' => 'Night Photo Walk',              'event_date' => daysFromToday(13), 'event_time' => '19:30', 'location' => 'Lakeside',
     'description' => 'Learn long-exposure and low-light photography around the lake. Phones welcome.'],
    ['event_id' => 5, 'club_id' => 5, 'event_name' => 'Inter-Club Badminton Friendly', 'event_date' => daysFromToday(18), 'event_time' => '16:00', 'location' => 'Sports Complex',
     'description' => 'Friendly doubles matches against other clubs. Rackets available to borrow.'],
    ['event_id' => 6, 'club_id' => 6, 'event_name' => 'Public Speaking Bootcamp',      'event_date' => daysFromToday(25), 'event_time' => '10:00', 'location' => 'Lecture Theatre 4',
     'description' => 'Practical drills to speak with confidence, from structure to body language.'],
    // Past events
    ['event_id' => 7, 'club_id' => 1, 'event_name' => 'Python for Beginners',          'event_date' => daysFromToday(-8),  'event_time' => '15:00', 'location' => 'Lab C3.10',
     'description' => 'A beginner-friendly session covering Python basics through small exercises.'],
    ['event_id' => 8, 'club_id' => 3, 'event_name' => 'Mini Game Jam',                 'event_date' => daysFromToday(-14), 'event_time' => '10:00', 'location' => 'Block D',
     'description' => 'Make a tiny game in six hours around a surprise theme.'],
];

/* ---------- club_followers (for the current user) ---------- */
$followedClubIds = $currentUser['user_id'] === 1 ? [1, 3, 5] : [1, 4];

/* ---------- rsvps ---------- */
$rsvpEventIds   = $currentUser['user_id'] === 1 ? [2, 3, 5, 7, 8] : [3, 7];   // events the current user is going to
$attendeeCounts = [1 => 86, 2 => 34, 3 => 120, 4 => 22, 5 => 48, 6 => 40, 7 => 65, 8 => 30];

/* ---------- feedback ---------- */
$feedback = [
    ['event_id' => 7, 'user_id' => 3, 'name' => 'Priya Nair', 'rating' => 5, 'comment' => 'Super clear explanations, perfect for beginners.'],
    ['event_id' => 7, 'user_id' => 4, 'name' => 'Daniel Lim', 'rating' => 4, 'comment' => 'Great pace. Would love a follow-up session!'],
    ['event_id' => 8, 'user_id' => 1, 'name' => 'John Doe',   'rating' => 5, 'comment' => 'So much fun, made my first game!'],
];
