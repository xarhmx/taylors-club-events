<?php
declare(strict_types=1);

class EventService {
    private EventRepository $events;

    public function __construct(EventRepository $events) {
        $this->events = $events;
    }

    public function search(string $query, int $clubId, string $period): array {
        $results = array_filter($this->events->all(), function (array $event) use ($query, $clubId, $period): bool {
            $matchesText = $query === '' || stripos($event['title'] . ' ' . $event['description'] . ' ' . $event['location'], $query) !== false;
            $matchesClub = $clubId === 0 || $event['club_id'] === $clubId;
            $past = strtotime($event['date'] . ' ' . $event['time']) <= time();
            $matchesPeriod = $period === 'all' || ($period === 'past' ? $past : !$past);
            return $matchesText && $matchesClub && $matchesPeriod;
        });
        usort($results, function (array $a, array $b): int {
            return strcmp($a['date'] . $a['time'], $b['date'] . $b['time']);
        });
        return $results;
    }

    public function validate(array $values): array {
        $errors = [];
        foreach (['title' => 150, 'location' => 150, 'description' => 2000] as $field => $limit) {
            if (trim($values[$field] ?? '') === '' || strlen($values[$field] ?? '') > $limit) {
                $errors[$field] = ucfirst($field) . ' is required and must be at most ' . $limit . ' bytes.';
            }
        }
        if (SampleData::getClubById((int) ($values['club_id'] ?? 0)) === null) {
            $errors['club_id'] = 'Choose an existing club.';
        }
        $text = ($values['date'] ?? '') . ' ' . ($values['time'] ?? '');
        // PHP can roll an invalid date into the next month, so compare it back.
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $text);
        if ($date === false || $date->format('Y-m-d H:i') !== $text || $date->getTimestamp() <= time()) {
            $errors['date'] = 'Choose a valid future date and time.';
        }
        $capacity = filter_var($values['capacity'] ?? '', FILTER_VALIDATE_INT);
        if ($capacity === false || $capacity < 1 || $capacity > 1000) {
            $errors['capacity'] = 'Capacity must be a whole number between 1 and 1000.';
        }
        return $errors;
    }

    public function create(array $values, array $user): int {
        // Keep this check here too, not just in the dashboard.
        if (($user['role'] ?? '') !== 'organiser') {
            throw new DomainException('Only organisers can create events.');
        }
        $errors = $this->validate($values);
        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }
        return $this->events->save([
            'club_id' => (int) $values['club_id'],
            'title' => trim($values['title']),
            'description' => trim($values['description']),
            'location' => trim($values['location']),
            'date' => $values['date'],
            'time' => $values['time'],
            'capacity' => (int) $values['capacity'],
            'created_by' => $user['id'],
        ]);
    }
}
