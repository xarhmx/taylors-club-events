<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
$user = requireLogin();
$event = $eventRepository->find(requestedId());
if ($event === null) { notFound('This event does not exist.'); }
$error = '';
$booked = isset($_SESSION['rsvps'][$user['id']][$event['id']]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    // The service checks capacity again when the form is submitted.
    try {
        $participation->updateBooking($user['id'], $event, input('action'));
    } catch (InvalidArgumentException | DomainException $exception) {
        $error = $exception->getMessage();
    }
    if ($error === '') {
        header('Location: Rspv.php?id=' . $event['id']);
        exit;
    }
}
$pageTitle = 'RSVP';
