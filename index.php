<?php
/*
 * index.php - Home page
 * ------------------------------------------------------------
 * Welcome section + three panels:
 *   Featured Clubs & Societies | Upcoming events | Following Clubs
 * Clicking an event opens its Event Details pop-up.
 * ------------------------------------------------------------
 */
$pageTitle  = 'Home';
$activePage = 'home';

require 'includes/functions.php';
require 'includes/sample_data.php';   // TODO (back-end): replace with database queries

$featuredClubs  = array_slice($clubs, 0, 5);
$upcoming       = array_slice(upcomingEvents($events), 0, 5);
$followingClubs = array_filter($clubs, fn($c) => in_array($c['club_id'], $followedClubIds));

require 'includes/header.php';
?>

<div class="container">

    <!-- Welcome section -->
    <section class="hero animate">
        <h1 class="hero-title"><span>Welcome!</span></h1>
        <p class="hero-subtitle">Discover Taylor's clubs &amp; societies, follow the ones you love and never miss an event.</p>
        <div class="hero-actions">
            <a href="clubs.php" class="btn btn-primary">Browse clubs</a>
            <a href="calendar.php" class="btn btn-outline">View calendar</a>
        </div>
    </section>

    <div class="home-grid">

        <!-- Featured Clubs & Societies -->
        <section class="card animate" style="--i: 1">
            <h2 class="card-title">Featured Clubs &amp; Societies</h2>
            <div class="card-body">
                <ul class="item-list">
                    <?php foreach ($featuredClubs as $club): ?>
                        <li>
                            <a class="item-row" href="clubs.php?club=<?= (int) $club['club_id'] ?>">
                                <span class="club-avatar" style="--hue: <?= clubHue($club['club_id']) ?>"><?= initials($club['club_name']) ?></span>
                                <span class="item-text">
                                    <span class="item-name"><?= e($club['club_name']) ?></span>
                                    <span class="item-meta"><?= e($club['category']) ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="clubs.php" class="card-footer-link">See all clubs →</a>
            </div>
        </section>

        <!-- Upcoming events -->
        <section class="card animate" style="--i: 2">
            <h2 class="card-title">Upcoming events</h2>
            <div class="card-body">
                <?php if (empty($upcoming)): ?>
                    <p class="empty-state">No upcoming events yet.</p>
                <?php else: ?>
                    <ul class="item-list">
                        <?php foreach ($upcoming as $event): ?>
                            <?php $club = findClub($clubs, $event['club_id']); ?>
                            <li>
                                <button type="button" class="item-row" data-open="event-<?= (int) $event['event_id'] ?>">
                                    <span class="date-badge">
                                        <span class="day"><?= date('j', strtotime($event['event_date'])) ?></span>
                                        <span class="month"><?= date('M', strtotime($event['event_date'])) ?></span>
                                    </span>
                                    <span class="item-text">
                                        <span class="item-name"><?= e($event['event_name']) ?></span>
                                        <span class="item-meta"><?= e($club['club_name']) ?> · <?= niceTime($event['event_time']) ?></span>
                                    </span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="calendar.php" class="card-footer-link">Open calendar →</a>
            </div>
        </section>

        <!-- Following Clubs & Societies -->
        <section class="card animate" style="--i: 3">
            <h2 class="card-title">Following Clubs &amp; Societies</h2>
            <div class="card-body">
                <?php if (empty($followingClubs)): ?>
                    <p class="empty-state">You aren't following any clubs yet.</p>
                <?php else: ?>
                    <ul class="item-list">
                        <?php foreach ($followingClubs as $club): ?>
                            <li>
                                <a class="item-row" href="clubs.php?club=<?= (int) $club['club_id'] ?>">
                                    <span class="club-avatar" style="--hue: <?= clubHue($club['club_id']) ?>"><?= initials($club['club_name']) ?></span>
                                    <span class="item-text">
                                        <span class="item-name"><?= e($club['club_name']) ?></span>
                                        <span class="item-meta"><?= e($club['category']) ?></span>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="profile.php" class="card-footer-link">Manage in profile →</a>
            </div>
        </section>

    </div>
</div>

<?php require 'includes/event_modals.php'; ?>

<?php require 'includes/footer.php'; ?>
