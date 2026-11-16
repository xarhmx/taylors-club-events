<?php
declare(strict_types=1);
require_once __DIR__ . '/../Data.php';
require_once __DIR__ . '/../services/EventRepository.php';
require_once __DIR__ . '/../services/EventService.php';
require_once __DIR__ . '/../services/ParticipationService.php';
date_default_timezone_set('Asia/Kuala_Lumpur');
// Keep the checks separate from any real browser session.
$_SESSION = [];
function check(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
function rejects(callable $operation, string $type): void {
    try { $operation(); } catch (Throwable $error) {
        check($error instanceof $type, 'Unexpected exception: ' . get_class($error));
        return;
    }
    throw new RuntimeException('Expected operation to be rejected.');
}
$repository = new EventRepository();
$events = new EventService($repository);
$participation = new ParticipationService();
$values = ['title' => 'Intermediate PHP', 'club_id' => '1', 'date' => date('Y-m-d', strtotime('+2 days')), 'time' => '14:00', 'location' => 'Lab 1', 'description' => 'Service design workshop', 'capacity' => '1'];
$student = ['id' => 1, 'role' => 'student'];
$organiser = ['id' => 2, 'role' => 'organiser'];
rejects(function () use ($events, $values, $student): void { $events->create($values, $student); }, DomainException::class);
$invalid = $values;
// This must fail rather than silently becoming a March date.
$invalid['date'] = '2027-02-30';
$invalid['capacity'] = '1.5';
$invalid['club_id'] = '999';
check(count($events->validate($invalid)) === 3, 'Invalid date, capacity and club must be rejected.');
$id = $events->create($values, $organiser);
$event = $repository->find($id);
check($event !== null && $event['created_by'] === 2, 'Created event must be retrievable with creator.');
check(count($repository->forClub(1)) === 2, 'Club events must include newly created events.');
check(count($events->search('Intermediate', 1, 'upcoming')) === 1, 'Search must include created events.');
check($events->search('Intermediate', 2, 'all') === [], 'Club filter must apply.');
$participation->updateBooking(1, $event, 'book');
$participation->updateBooking(1, $event, 'book');
check($participation->count($id) === 1, 'Repeated booking must not use another seat.');
rejects(function () use ($participation, $event): void { $participation->updateBooking(3, $event, 'book'); }, DomainException::class);
rejects(function () use ($participation, $event): void { $participation->saveReview(1, $event, '5', 'Too early'); }, DomainException::class);
$past = $event;
$past['date'] = date('Y-m-d', strtotime('-1 day'));
rejects(function () use ($participation, $past): void { $participation->updateBooking(3, $past, 'book'); }, DomainException::class);
rejects(function () use ($participation, $past): void { $participation->saveReview(1, $past, '6', 'Invalid'); }, InvalidArgumentException::class);
$participation->saveReview(1, $past, '4', ' Helpful ');
$participation->saveReview(1, $past, '5', 'Updated');
$summary = $participation->ratingSummary($id);
check($summary['count'] === 1 && (float) $summary['average'] === 5.0, 'Updating a review must replace the previous rating.');
$participation->updateBooking(1, $event, 'cancel');
$participation->updateBooking(3, $event, 'book');
check($participation->isBooked(3, $id), 'Cancellation must release a seat.');
echo "PASS: creation, permissions, validation, repository, filters, capacity, duplicate bookings, cancellation and feedback rules.\n";
