<?php
/*
 * calendar.php - Event Calendar page
 * ------------------------------------------------------------
 * Left: month calendar (built by js/script.js).
 * Right: list of events for the month or for the clicked day.
 * Clicking an event opens its Event Details pop-up.
 * ------------------------------------------------------------
 */
$pageTitle  = 'Calendar';
$activePage = 'calendar';

require 'includes/functions.php';
require 'includes/sample_data.php';   // TODO (back-end): replace with database queries

// Prepare the events for JavaScript (only the fields the calendar needs)
$calendarEvents = [];
foreach ($events as $event) {
    $club = findClub($clubs, $event['club_id']);
    $calendarEvents[] = [
        'id'       => (int) $event['event_id'],
        'title'    => $event['event_name'],
        'club'     => $club ? $club['club_name'] : '',
        'date'     => $event['event_date'],         // format: YYYY-MM-DD
        'time'     => niceTime($event['event_time']),
        'location' => $event['location'],
        'going'    => in_array($event['event_id'], $rsvpEventIds),
    ];
}

require 'includes/header.php';
?>

<div class="container">

    <div class="page-head animate">
        <h1 class="page-title">Calendar</h1>
        <p class="page-subtitle">Dark days have events. Click a day to see what's on, or an event for details.</p>
    </div>

    <div class="calendar-layout">

        <!-- Calendar widget -->
        <div class="card calendar-card animate" id="calendar" style="--i: 1">
            <div class="cal-header">
                <button type="button" class="cal-nav" id="calPrev" aria-label="Previous month">&#8249;</button>
                <div class="cal-selects">
                    <select class="cal-select" id="calMonth" aria-label="Month"></select>
                    <select class="cal-select" id="calYear" aria-label="Year"></select>
                </div>
                <button type="button" class="cal-nav" id="calNext" aria-label="Next month">&#8250;</button>
            </div>

            <div class="cal-weekdays">
                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
            </div>
            <div class="cal-grid" id="calGrid"></div>

            <div class="cal-legend">
                <span class="legend-event">Event</span>
                <span class="legend-today">Today</span>
                <span class="legend-selected">Selected</span>
            </div>
        </div>

        <!-- Event list -->
        <section class="animate" style="--i: 2">
            <div class="event-panel-head">
                <h2 class="event-panel-title" id="eventPanelTitle">Events</h2>
                <button type="button" class="link-btn" id="showAllBtn" hidden>Show whole month</button>
            </div>
            <div id="eventList"></div>
        </section>

    </div>
</div>

<!-- Pass the PHP events to JavaScript -->
<script>
    window.CALENDAR_EVENTS = <?= json_encode($calendarEvents, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

<?php require 'includes/event_modals.php'; ?>

<?php require 'includes/footer.php'; ?>
