<?php
/*
 * profile.php - Profile page
 * ------------------------------------------------------------
 * User info (picture, name, email) + followed clubs,
 * upcoming RSVPs and past events to rate.
 * ------------------------------------------------------------
 */
$pageTitle  = 'Profile';
$activePage = 'profile';

require 'includes/functions.php';
require 'includes/sample_data.php';   // TODO (back-end): replace with database queries

$user = $currentUser;

$followedClubs = array_filter($clubs, fn($c) => in_array($c['id'], $user['followed_club_ids']));
$myEvents      = array_filter($events, fn($ev) => in_array($ev['id'], $user['rsvp_event_ids']));
$myUpcoming    = upcomingEvents($myEvents);
$myPast        = pastEvents($myEvents);

require 'includes/header.php';
?>

<div class="container">

    <h1 class="page-title animate">Profile</h1>

    <!-- User info -->
    <section class="profile-header animate" style="--i: 1">
        <div class="profile-avatar" aria-hidden="true">
            <!-- Simple person icon -->
            <svg viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="8" r="4.2"/>
                <path d="M3.5 21c0-4.4 3.8-7.5 8.5-7.5s8.5 3.1 8.5 7.5z"/>
            </svg>
        </div>
        <div>
            <p class="profile-name"><?= e($user['name']) ?></p>
            <p class="profile-email"><?= e($user['email']) ?></p>
            <div class="profile-meta">
                <span class="badge"><?= $user['role'] === 'organiser' ? 'Club Organiser' : 'Student' ?></span>
                <a href="edit_profile.php" class="btn btn-outline btn-sm">Edit profile</a>
                <a href="logout.php" class="btn btn-dark btn-sm">Log out</a>
            </div>
        </div>
    </section>

    <div class="profile-grid">

        <!-- Clubs the user follows -->
        <section class="card animate" style="--i: 2">
            <h2 class="card-title">Clubs I Follow</h2>
            <div class="card-body">
                <?php if (empty($followedClubs)): ?>
                    <p class="empty-state">Not following any clubs yet.</p>
                <?php else: ?>
                    <ul class="item-list">
                        <?php foreach ($followedClubs as $club): ?>
                            <li>
                                <a class="item-row" href="club.php?id=<?= $club['id'] ?>">
                                    <span class="club-avatar" style="--hue: <?= ($club['id'] * 53) % 360 ?>"><?= initials($club['name']) ?></span>
                                    <span class="item-text">
                                        <span class="item-name"><?= e($club['name']) ?></span>
                                        <span class="item-meta"><?= e($club['category']) ?></span>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <!-- Upcoming events the user RSVPed to -->
        <section class="card animate" style="--i: 3">
            <h2 class="card-title">My Upcoming Events</h2>
            <div class="card-body">
                <?php if (empty($myUpcoming)): ?>
                    <p class="empty-state">No upcoming RSVPs.</p>
                <?php else: ?>
                    <ul class="item-list">
                        <?php foreach ($myUpcoming as $event): ?>
                            <?php $club = findClub($clubs, $event['club_id']); ?>
                            <li>
                                <a class="item-row" href="event.php?id=<?= $event['id'] ?>">
                                    <span class="date-badge">
                                        <span class="day"><?= date('j', strtotime($event['date'])) ?></span>
                                        <span class="month"><?= date('M', strtotime($event['date'])) ?></span>
                                    </span>
                                    <span class="item-text">
                                        <span class="item-name"><?= e($event['title']) ?></span>
                                        <span class="item-meta"><?= niceTime($event['time']) ?> · <?= e($event['location']) ?></span>
                                    </span>
                                </a>
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
                            <?php $club = findClub($clubs, $event['club_id']); ?>
                            <li>
                                <div class="item-row">
                                    <span class="item-text">
                                        <span class="item-name"><?= e($event['title']) ?></span>
                                        <span class="item-meta"><?= e($club['name']) ?> · <?= niceDate($event['date']) ?></span>
                                    </span>
                                    <a class="rate-link" href="event.php?id=<?= $event['id'] ?>#feedback">Rate ★</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>

<?php require 'includes/footer.php'; ?>
