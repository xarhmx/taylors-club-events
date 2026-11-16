<?php
declare(strict_types=1);

// Shared setup for the session demo. No database connection yet.
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once __DIR__ . '/../Data.php';
require_once __DIR__ . '/../services/EventRepository.php';
require_once __DIR__ . '/../services/EventService.php';
require_once __DIR__ . '/../services/ParticipationService.php';
$eventRepository = new EventRepository();
$eventService = new EventService($eventRepository);
$participation = new ParticipationService();

function escape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
// Ignore array values submitted where a single form value is expected.
function input(string $key): string {
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}
function requireLogin(): array {
    $user = currentUser();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }
    return $user;
}
function csrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function checkCsrf(): void {
    // Check before touching any session data from a form.
    if (!hash_equals(csrfToken(), input('csrf_token'))) {
        http_response_code(403);
        exit('Invalid form token. Go back, refresh the page and try again.');
    }
}
function requestedId(): int {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    return $id === false || $id === null ? 0 : $id;
}
function notFound(string $message): void {
    http_response_code(404);
    echo $message;
    exit;
}
function eventEnded(array $event): bool {
    return strtotime($event['date'] . ' ' . $event['time']) < time();
}
function rsvpCount(int $eventId): int {
    global $participation;
    return $participation->count($eventId);
}
