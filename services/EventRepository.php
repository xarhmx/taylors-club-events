<?php
declare(strict_types=1);

class EventRepository {
    // Include new events without changing the seed records.
    public function all(): array {
        return array_merge(SampleData::getEvents(), array_values($_SESSION['created_events'] ?? []));
    }

    public function find(int $id): ?array {
        foreach ($this->all() as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }
        return null;
    }

    public function forClub(int $clubId): array {
        return array_values(array_filter($this->all(), function (array $event) use ($clubId): bool {
            return $event['club_id'] === $clubId;
        }));
    }

    public function save(array $event): int {
        $ids = array_column($this->all(), 'id');
        // Seed IDs and session IDs share the same range.
        $event['id'] = max($ids) + 1;
        $_SESSION['created_events'][$event['id']] = $event;
        return $event['id'];
    }
}
