<?php
/*
 * profile.php - Profile page
 * ------------------------------------------------------------
 * User info (photo, name, email, student ID, phone),
 * "Clubs in" with the user's position, plus their events.
 * ------------------------------------------------------------
 */
$pageTitle  = 'Profile';
$activePage = 'profile';

require 'includes/functions.php';
require 'includes/data.php';             // loads everything from the database

$user       = requireLogin();   // guests are sent to the login page
$myClubs    = clubsWithPositions($clubs, $clubMembers, $followedClubIds, $user['user_id']);
$myEvents   = array_filter($events, fn($ev) => in_array($ev['event_id'], $rsvpEventIds));
$myUpcoming = upcomingEvents($myEvents);
$myPast     = pastEvents($myEvents);

require 'includes/header.php';
?>

<div class="container">

    <div class="profile-title-row animate">
        <h1 class="page-title">Profile</h1>
        <a href="edit_profile.php" class="btn btn-outline btn-sm">Edit Profile</a>
        <form action="logout.php" method="POST" class="inline-form">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-dark btn-sm">Log out</button>
        </form>
    </div>

    <!-- User info -->
    <section class="profile-header animate" style="--i: 1">
        <div class="profile-avatar"><?= avatarHtml($user) ?></div>

        <dl class="profile-info">
            <div><dt>Name</dt><dd><?= e($user['name']) ?></dd></div>
            <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
            <div><dt>Student ID</dt><dd><?= e($user['student_id']) ?></dd></div>
            <div><dt>Phone</dt><dd><?= $user['phone'] !== '' ? e($user['phone']) : '<span class="muted">Not added</span>' ?></dd></div>
        </dl>

        <span class="badge profile-role"><?= $user['role'] === 'organiser' ? 'Club Organiser' : 'Student' ?></span>
    </section>

    <!-- Clubs in -->
    <section class="profile-section animate" style="--i: 2">
        <h2 class="section-title">Clubs in:</h2>
        <?php if (empty($myClubs)): ?>
            <p class="muted">You haven't joined or followed any clubs yet. <a class="text-link" href="clubs.php">Browse clubs →</a></p>
        <?php else: ?>
            <ol class="clubs-in-list">
                <?php foreach ($myClubs as $row): ?>
                    <li>
                        <a href="clubs.php?club=<?= (int) $row['club']['club_id'] ?>">
                            <span class="club-avatar club-avatar-xs" style="--hue: <?= clubHue($row['club']['club_id']) ?>"><?= initials($row['club']['club_name']) ?></span>
                            <?= e($row['club']['club_name']) ?>
                            <span class="muted">(<?= e($row['position']) ?>)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>

    <div class="profile-grid">

        <!-- Upcoming events the user RSVPed to -->
        <section class="card animate" style="--i: 3">
            <h2 class="card-title">My Upcoming Events</h2>
            <div class="card-body">
                <?php if (empty($myUpcoming)): ?>
                    <p class="empty-state">No upcoming RSVPs. <a class="text-link" href="calendar.php">Find events →</a></p>
                <?php else: ?>
                    <ul class="item-list">
                        <?php foreach ($myUpcoming as $event): ?>
                            <li>
                                <button type="button" class="item-row" data-open="event-<?= (int) $event['event_id'] ?>">
                                    <span class="date-badge">
                                        <span class="day"><?= date('j', strtotime($event['event_date'])) ?></span>
                                        <span class="month"><?= date('M', strtotime($event['event_date'])) ?></span>
                                    </span>
                                    <span class="item-text">
                                        <span class="item-name"><?= e($event['event_name']) ?></span>
                                        <span class="item-meta"><?= niceTime($event['event_time']) ?> · <?= e($event['location']) ?></span>
                                    </span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <!-- Past events (rate / give feedback) -->
        <section class="card animate" style="--i: 4">
            <h2 class="card-title">Past Events</h2>
            <div class="card-body">
                <?php if (empty($myPast)): ?>
                    <p class="empty-state">No past events yet.</p>
                <?php else: ?>
                    <ul class="item-list">
                        <?php foreach ($myPast as $event): ?>
                            <?php
                                $club  = findClub($clubs, $event['club_id']);
                                $rated = (bool) array_filter($feedback, fn($f) => $f['event_id'] == $event['event_id'] && $f['user_id'] == $user['user_id']);
                            ?>
                            <li>
                                <button type="button" class="item-row" data-open="event-<?= (int) $event['event_id'] ?>">
                                    <span class="item-text">
                                        <span class="item-name"><?= e($event['event_name']) ?></span>
                                        <span class="item-meta"><?= e($club['club_name']) ?> · <?= niceDate($event['event_date']) ?></span>
                                    </span>
                                    <span class="rate-link"><?= $rated ? 'Rated ✓' : 'Rate ★' ?></span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>

<?php require 'includes/event_modals.php'; ?>

<?php require 'includes/footer.php'; ?>
