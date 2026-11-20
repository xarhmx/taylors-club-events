<?php
/*
 * edit_profile.php - Edit Profile page
 * ------------------------------------------------------------
 * Same layout as the profile page, but:
 *   - the phone number can be changed
 *   - clicking the photo opens a small pop-up to upload a new
 *     photo or delete the current one
 * Everything is sent to actions/update_profile.php
 * (multipart/form-data is needed for file uploads).
 * Errors come back as a flash message (top of the page).
 * ------------------------------------------------------------
 */
$pageTitle  = 'Edit Profile';
$activePage = 'profile';

require 'includes/functions.php';
require 'includes/data.php';             // loads everything from the database

$user    = requireLogin();   // guests are sent to the login page
$myClubs = clubsWithPositions($clubs, $clubMembers, $followedClubIds, $user['user_id']);

require 'includes/header.php';
?>

<div class="container">
    <form action="actions/update_profile.php" method="POST" enctype="multipart/form-data" id="editProfileForm">
        <?= csrfField() ?>

        <div class="profile-title-row animate">
            <h1 class="page-title">Profile</h1>
            <a href="profile.php" class="btn btn-outline btn-sm">Cancel</a>
            <button type="submit" class="btn btn-dark btn-sm">Save your changes</button>
        </div>

        <section class="profile-header animate" style="--i: 1">

            <!-- Photo + upload pop-up -->
            <div class="photo-picker">
                <button type="button" class="profile-avatar is-editable" id="avatarButton"
                        aria-expanded="false" aria-controls="photoPopup" aria-label="Change profile photo">
                    <span id="avatarPreview"><?= avatarHtml($user) ?></span>
                    <span class="avatar-overlay">Change</span>
                </button>

                <div class="photo-popup" id="photoPopup" hidden>
                    <label class="photo-upload">
                        <span>Upload your new profile photo from your device. Max file size: 2MB (JPG or PNG), 1 file.</span>
                        <span class="clip-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.4 11.1l-9.2 9.2a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/></svg>
                        </span>
                        <input type="file" name="profile_photo" id="photoInput" accept="image/jpeg,image/png" class="visually-hidden">
                    </label>
                    <p class="photo-file-name muted" id="photoFileName"></p>
                    <label class="checkbox-row">
                        <span>Delete current profile photo?</span>
                        <input type="checkbox" name="delete_photo" value="1">
                    </label>
                </div>
            </div>

            <dl class="profile-info">
                <div><dt>Name</dt><dd><?= e($user['name']) ?></dd></div>
                <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
                <div><dt>Student ID</dt><dd><?= e($user['student_id']) ?></dd></div>
                <div>
                    <dt><label for="phone">Phone</label></dt>
                    <dd class="input-wrap">
                        <input class="input-line" type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>"
                               placeholder="Input your phone number here. (Optional)" maxlength="20"
                               pattern="[0-9+\- ]{7,20}" title="Numbers, spaces, + and - only">
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Clubs in (read only here) -->
        <section class="profile-section animate" style="--i: 2">
            <h2 class="section-title">Clubs in:</h2>
            <?php if (empty($myClubs)): ?>
                <p class="muted">No clubs yet.</p>
            <?php else: ?>
                <ol class="clubs-in-list">
                    <?php foreach ($myClubs as $row): ?>
                        <li>
                            <span class="club-avatar club-avatar-xs" style="--hue: <?= clubHue($row['club']['club_id']) ?>"><?= initials($row['club']['club_name']) ?></span>
                            <?= e($row['club']['club_name']) ?>
                            <span class="muted">(<?= e($row['position']) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>

    </form>
</div>

<?php require 'includes/footer.php'; ?>
