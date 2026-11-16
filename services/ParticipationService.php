<?php
declare(strict_types=1);

class ParticipationService {
    public function isBooked(int $userId, int $eventId): bool {
        return isset($_SESSION['rsvps'][$userId][$eventId]);
    }

    public function count(int $eventId): int {
        $count = 0;
        foreach ($_SESSION['rsvps'] ?? [] as $bookings) {
            if (isset($bookings[$eventId])) { $count++; }
        }
        return $count;
    }

    public function updateBooking(int $userId, array $event, string $action): void {
        if ($action === 'cancel') {
            unset($_SESSION['rsvps'][$userId][$event['id']]);
            return;
        }
        if ($action !== 'book') {
            throw new InvalidArgumentException('Choose a valid RSVP action.');
        }
        if (strtotime($event['date'] . ' ' . $event['time']) <= time()) {
            throw new DomainException('Bookings have closed for this event.');
        }
        // Submitting the same booking twice should not use another seat.
        if (!$this->isBooked($userId, $event['id']) && $this->count($event['id']) >= $event['capacity']) {
            throw new DomainException('This event is full.');
        }
        $_SESSION['rsvps'][$userId][$event['id']] = true;
    }

    public function canReview(int $userId, array $event): bool {
        return $this->isBooked($userId, $event['id']) && strtotime($event['date'] . ' ' . $event['time']) <= time();
    }

    public function saveReview(int $userId, array $event, string $rating, string $comment): void {
        if (!$this->canReview($userId, $event)) {
            throw new DomainException('Feedback is available after an event you booked has started.');
        }
        $number = filter_var($rating, FILTER_VALIDATE_INT);
        $comment = trim($comment);
        if ($number === false || $number < 1 || $number > 5 || strlen($comment) > 2000) {
            throw new InvalidArgumentException('Choose a rating from 1 to 5 and a comment up to 2000 bytes.');
        }
        // One review per person and event. Saving again replaces it.
        $_SESSION['feedback'][$userId][$event['id']] = ['rating' => $number, 'comment' => $comment];
    }

    public function ratingSummary(int $eventId): array {
        $ratings = [];
        foreach ($_SESSION['feedback'] ?? [] as $reviews) {
            if (isset($reviews[$eventId])) { $ratings[] = $reviews[$eventId]['rating']; }
        }
        return ['count' => count($ratings), 'average' => $ratings === [] ? null : array_sum($ratings) / count($ratings)];
    }
}
