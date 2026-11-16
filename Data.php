<?php
declare(strict_types=1);

// Seed data for the demo. Changes here show up in every fresh session.
class SampleData {

    public static function getClubs(): array {
        return [
            [
                "id" => 1,
                "name" => "Coding Club",
                "category" => "Technology",
                "description" => "A club for coding enthusiasts to learn and share knowledge about programming."
            ],
            [
                "id" => 2,
                "name" => "Photography Society",
                "category" => "Arts",
                "description" => "A society for photography lovers to explore and improve their skills in capturing moments."
            ],
            [
                "id" => 3,
                "name" => "Sports Club",
                "category" => "Sports",
                "description" => "A club for sports enthusiasts to participate in various sports activities and competitions."
            ]
        ];
    }

    public static function getEvents(): array {
        return [
            [
                "id" => 101,
                "club_id" => 1,
                "title" => "PHP Coding Workshop",
                "date" => "2026-11-20",
                "time" => "14:00",
                "location" => "Taylor's University",
                "description" => "Learn the basics of PHP programming in this hands-on workshop.",
                "capacity" => 30
            ],
            [
                "id" => 102,
                "club_id" => 2,
                "title" => "Photography Walk",
                "date" => "2026-11-22",
                "time" => "10:00",
                "location" => "Taylor's Lakeside Campus",
                "description" => "Explore photography techniques.",
                "capacity" => 25
            ],
            [
                "id" => 103,
                "club_id" => 3,
                "title" => "Sports Tournament",
                "date" => "2026-11-25",
                "time" => "09:00",
                "location" => "University Sports Hall",
                "description" => "Join a friendly sports competition.",
                "capacity" => 50
            ]
        ];
    }

    public static function getClubById(int $id): ?array {
        foreach (self::getClubs() as $club) {
            if ($club['id'] === $id) {
                return $club;
            }
        }
        return null;
    }

    public static function getEventById(int $id): ?array {
        foreach (self::getEvents() as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }
        return null;
    }

    // Keep this as a list even when a club only has one event.
    public static function getEventsByClubId(int $clubId): array {
        $results = [];
        foreach (self::getEvents() as $event) {
            if ($event['club_id'] === $clubId) {
                $results[] = $event;
            }
        }
        return $results;
    }
}
