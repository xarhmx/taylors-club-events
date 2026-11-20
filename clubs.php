<?php
/*
 * clubs.php - Clubs page
 * ------------------------------------------------------------
 * Students:   View Details (pop-up) + Follow+ on every club.
 * Organisers: their OWN club shows Edit Details (edit in place)
 *             + Add Event (pop-up). Other clubs look the same as
 *             for students, so organisers can still follow them.
 * ------------------------------------------------------------
 */
$pageTitle  = 'Clubs';
$activePage = 'clubs';

require 'includes/functions.php';
require 'includes/sample_data.php';   // TODO (back-end): replace with database queries
require 'includes/event_form.php';

// Show the organiser's own club(s) first
usort($clubs, fn($a, $b) => isOrganiserOf($b, $currentUser) <=> isOrganiserOf($a, $currentUser));

// List of categories for the filter buttons (no duplicates)
$categories = array_unique(array_column($clubs, 'category'));
sort($categories);

require 'includes/header.php';
?>

<div class="container">

    <div class="page-head animate">
        <h1 class="page-title">Clubs</h1>
        <p class="page-subtitle">Find your people. Browse all Taylor's clubs and societies.</p>
    </div>

    <!-- Category filter -->
    <div class="clubs-toolbar animate" style="--i: 1">
        <button type="button" class="filter-chip active" data-category="All">All</button>
        <?php foreach ($categories as $category): ?>
            <button type="button" class="filter-chip" data-category="<?= e($category) ?>"><?= e($category) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="club-list">
        <?php foreach ($clubs as $index => $club): ?>
            <?php
                $clubId      = (int) $club['club_id'];
                $isOwnClub   = isOrganiserOf($club, $currentUser);
                $isFollowing = in_array($clubId, $followedClubIds);
            ?>
            <article class="card club-card animate<?= $isOwnClub ? ' is-own' : '' ?>" id="club-card-<?= $clubId ?>"
                     data-category="<?= e($club['category']) ?>" style="--i: <?= min($index + 2, 8) ?>">

                <h2 class="club-name">
                    <?= $index + 1 ?>. <?= e($club['club_name']) ?>
                    <?php if ($isOwnClub): ?><span class="badge badge-dark">Your club</span><?php endif; ?>
                </h2>

                <?php if ($isOwnClub): ?>
                    <!-- ORGANISER VIEW: the details can be edited in place.
                         Sent to actions/update_club.php -->
                    <form class="club-edit-form" action="actions/update_club.php" method="POST">
                        <input type="hidden" name="club_id" value="<?= $clubId ?>">
                        <div class="club-content">
                            <div class="club-avatar club-image" style="--hue: <?= clubHue($clubId) ?>"><?= initials($club['club_name']) ?></div>
                            <div class="club-info">
                                <p class="club-category">
                                    Category:
                                    <span class="view-only badge"><?= e($club['category']) ?></span>
                                    <select class="edit-only select" name="category" aria-label="Category">
                                        <?php foreach ($categoryOptions as $option): ?>
                                            <option <?= $option === $club['category'] ? 'selected' : '' ?>><?= e($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <p class="club-description view-only"><?= e($club['description']) ?></p>
                                <textarea class="edit-only input-line club-description-input" name="description" rows="2"
                                          maxlength="500" aria-label="Club description"><?= e($club['description']) ?></textarea>
                            </div>
                        </div>

                        <div class="club-actions">
                            <button type="button" class="btn btn-outline btn-sm view-only" data-edit-toggle>Edit Details</button>
                            <button type="submit" class="btn btn-dark btn-sm edit-only">Save Details</button>
                            <button type="button" class="btn btn-outline btn-sm edit-only" data-edit-toggle>Cancel</button>
                            <button type="button" class="btn btn-outline btn-sm" data-open="add-event-<?= $clubId ?>">Add Event</button>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- STUDENT VIEW -->
                    <div class="club-content">
                        <!-- Placeholder image. With real images use:
                             <img src="images/clubs/<?= e($club['image'] ?? '') ?>" alt=""> inside this div -->
                        <div class="club-avatar club-image" style="--hue: <?= clubHue($clubId) ?>"><?= initials($club['club_name']) ?></div>
                        <div class="club-info">
                            <p class="club-category">Category: <span class="badge"><?= e($club['category']) ?></span></p>
                            <p class="club-description"><?= e($club['description']) ?></p>
                        </div>
                    </div>

                    <div class="club-actions">
                        <button type="button" class="btn btn-outline btn-sm" data-open="club-<?= $clubId ?>">View Details</button>

                        <!-- Sent to actions/follow_club.php -->
                        <form class="follow-form" action="actions/follow_club.php" method="POST">
                            <input type="hidden" name="club_id" value="<?= $clubId ?>">
                            <input type="hidden" name="action" value="<?= $isFollowing ? 'unfollow' : 'follow' ?>">
                            <button type="submit" class="btn btn-outline btn-sm btn-follow<?= $isFollowing ? ' is-following' : '' ?>">
                                <?= $isFollowing ? 'Following ✓' : 'Follow+' ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

</div>

<!-- ============ Pop-ups ============ -->

<?php foreach ($clubs as $club): ?>
    <?php
        $clubId    = (int) $club['club_id'];
        $members   = array_filter($clubMembers, fn($m) => $m['club_id'] == $clubId);
        $head      = array_values(array_filter($members, fn($m) => $m['position'] === 'Club Head'))[0] ?? null;
        $assistant = array_values(array_filter($members, fn($m) => $m['position'] === 'Assistant Head'))[0] ?? null;
        $others    = array_filter($members, fn($m) => !in_array($m['position'], ['Club Head', 'Assistant Head']));
        $clubUpcoming = upcomingEvents(eventsForClub($events, $clubId));
    ?>

    <!-- Club details pop-up (opened by "View Details") -->
    <dialog class="popup" id="club-<?= $clubId ?>" aria-labelledby="club-<?= $clubId ?>-title">
        <button type="button" class="popup-close" data-close aria-label="Close">&times;</button>
        <p class="popup-kicker"><span class="badge"><?= e($club['category']) ?></span></p>
        <h2 class="popup-title" id="club-<?= $clubId ?>-title"><?= e($club['club_name']) ?></h2>

        <dl class="member-list">
            <div><dt>Club Head:</dt><dd><?= $head ? e($head['name']) : '<span class="muted">To be announced</span>' ?></dd></div>
            <div><dt>Club Assistant Head:</dt><dd><?= $assistant ? e($assistant['name']) : '<span class="muted">To be announced</span>' ?></dd></div>
            <?php foreach ($others as $member): ?>
                <div><dt><?= e($member['position']) ?>:</dt><dd><?= e($member['name']) ?></dd></div>
            <?php endforeach; ?>
        </dl>

        <h3 class="popup-subtitle">Upcoming Events:</h3>
        <?php if (empty($clubUpcoming)): ?>
            <p class="muted">No upcoming events yet.</p>
        <?php else: ?>
            <ol class="popup-event-list">
                <?php foreach ($clubUpcoming as $event): ?>
                    <li>
                        <button type="button" class="link-row" data-open="event-<?= (int) $event['event_id'] ?>">
                            <strong><?= e($event['event_name']) ?></strong> / <?= niceDate($event['event_date']) ?>
                            <span class="item-meta">• <?= niceTime($event['event_time']) ?> at <?= e($event['location']) ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </dialog>

    <?php if (isOrganiserOf($club, $currentUser)) {
        eventFormDialog('add-event-' . $clubId, $clubId);   // "Add Event" pop-up
    } ?>
<?php endforeach; ?>

<?php require 'includes/event_modals.php'; ?>

<?php require 'includes/footer.php'; ?>
