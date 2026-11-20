<?php
/*
 * actions/follow_club.php
 * ------------------------------------------------------------
 * Follow+ / Following button on clubs.php.
 * Receives: club_id, action ('follow' or 'unfollow')
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();
$user = requireLogin();

$clubId = inputId('club_id');
$action = input('action');

$stmt = $pdo->prepare('SELECT club_name FROM clubs WHERE club_id = ?');
$stmt->execute([$clubId]);
$clubName = $stmt->fetchColumn();

if ($clubName === false || !in_array($action, ['follow', 'unfollow'], true)) {
    flash('error', 'That club could not be found.');
    redirectBack('clubs.php');
}

if ($action === 'follow') {
    $stmt = $pdo->prepare('SELECT 1 FROM club_followers WHERE user_id = ? AND club_id = ?');
    $stmt->execute([$user['user_id'], $clubId]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT INTO club_followers (user_id, club_id) VALUES (?, ?)')
            ->execute([$user['user_id'], $clubId]);
    }
    flash('success', "You're now following $clubName.");
} else {
    $pdo->prepare('DELETE FROM club_followers WHERE user_id = ? AND club_id = ?')
        ->execute([$user['user_id'], $clubId]);
    flash('success', "You unfollowed $clubName.");
}

redirectBack('clubs.php');
