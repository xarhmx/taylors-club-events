<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$user = requireLogin();
$event = $eventRepository->find(requestedId());
if ($event === null) { notFound('This event does not exist.'); }
$feedback = $_SESSION['feedback'][$user['id']][$event['id']] ?? ['rating' => 5, 'comment' => ''];
$error = '';
$eligible = $participation->canReview($user['id'], $event);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    // Keep their input if validation fails.
    $feedback = ['rating' => (int) input('rating'), 'comment' => input('comment')];
    try {
        $participation->saveReview($user['id'], $event, input('rating'), input('comment'));
        header('Location: Feedback.php?id=' . $event['id'] . '&saved=1');
        exit;
    } catch (InvalidArgumentException | DomainException $exception) {
        $error = $exception->getMessage();
    }
}
$pageTitle = 'Feedback';
