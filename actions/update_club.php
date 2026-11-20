<?php
/*
 * actions/update_club.php
 * ------------------------------------------------------------
 * "Edit Details" on the organiser's own club card (clubs.php).
 * Receives: club_id, category, description
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();
$user = requireLogin();

$clubId      = inputId('club_id');
$category    = input('category');
$description = trim(input('description'));

if (!userOrganisesClub($user, $clubId)) {
    flash('error', 'You can only edit your own club.');
    redirectBack('clubs.php');
}

$errors = [];
if (!in_array($category, CLUB_CATEGORIES, true)) {       // only categories from the list
    $errors[] = 'Please choose a category from the list.';
}
if (textLength($description) < 10 || textLength($description) > 500) {
    $errors[] = 'Description must be 10 to 500 characters.';
}

if ($errors) {
    flash('error', implode(' ', $errors));
} else {
    $pdo->prepare('UPDATE clubs SET category = ?, description = ? WHERE club_id = ?')
        ->execute([$category, $description, $clubId]);
    flash('success', 'Club details updated.');
}

redirectBack('clubs.php');
