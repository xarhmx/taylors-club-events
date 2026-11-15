<?php
/*
 * clubs.php - Clubs page
 * ------------------------------------------------------------
 * Lists every club with its image, category and description,
 * plus "View Details" and "Follow+" buttons.
 * ------------------------------------------------------------
 */
$pageTitle  = 'Clubs';
$activePage = 'clubs';

require 'includes/functions.php';
require 'includes/sample_data.php';   // TODO (back-end): replace with database queries

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
            <?php $isFollowing = in_array($club['id'], $currentUser['followed_club_ids']); ?>

            <article class="card club-card animate" data-category="<?= e($club['category']) ?>" style="--i: <?= $index + 2 ?>">
                <h2 class="club-name"><?= $index + 1 ?>. <?= e($club['name']) ?></h2>

                <div class="club-content">
                    <!-- Placeholder image. With real images use:
                         <img src="images/clubs/<?= $club['id'] ?>.jpg" alt=""> inside this div -->
                    <div class="club-avatar club-image" style="--hue: <?= ($club['id'] * 53) % 360 ?>">
                        <?= initials($club['name']) ?>
                    </div>

                    <div class="club-info">
                        <p class="club-category">Category: <span class="badge"><?= e($club['category']) ?></span></p>
                        <p class="club-description"><?= e($club['description']) ?></p>
                    </div>
                </div>

                <div class="club-actions">
                    <a href="club.php?id=<?= $club['id'] ?>" class="btn btn-outline btn-sm">View Details</a>

                    <!-- Sent to actions/follow_club.php once the back-end is ready -->
                    <form class="follow-form" action="actions/follow_club.php" method="POST">
                        <input type="hidden" name="club_id" value="<?= $club['id'] ?>">
                        <input type="hidden" name="action" value="<?= $isFollowing ? 'unfollow' : 'follow' ?>">
                        <button type="submit" class="btn btn-outline btn-sm btn-follow<?= $isFollowing ? ' is-following' : '' ?>">
                            <?= $isFollowing ? 'Following ✓' : 'Follow+' ?>
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

</div>

<?php require 'includes/footer.php'; ?>
