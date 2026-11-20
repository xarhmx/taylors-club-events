<?php
/*
 * includes/event_modals.php
 * ------------------------------------------------------------
 * Draws one hidden "Event Details" pop-up for every event.
 * Any button with data-open="event-5" opens the pop-up for event 5.
 *
 * Inside each pop-up:
 *   - event name, club, date, time, venue, description, people going
 *   - RSVP / Cancel RSVP            (upcoming events)
 *   - rating + feedback form         (past events the user went to)
 *   - Edit / Delete                  (only for the club's organiser)
 *
 * Needs: $events, $clubs, $currentUser, $rsvpEventIds,
 *        $attendeeCounts, $feedback   (from sample_data.php / database)
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/event_form.php';

foreach ($events as $event):
    $club        = findClub($clubs, $event['club_id']);
    $eventId     = (int) $event['event_id'];
    $past        = isPast($event);
    $isGoing     = in_array($eventId, $rsvpEventIds);
    $going       = $attendeeCounts[$eventId] ?? 0;
    $canManage   = $club && isOrganiserOf($club, $currentUser);
    $reviews     = array_filter($feedback, fn($f) => $f['event_id'] == $eventId);
    $alreadyRated = (bool) array_filter($reviews, fn($f) => $f['user_id'] == $currentUser['user_id']);
    $average     = $reviews ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
?>
<dialog class="popup popup-event" id="event-<?= $eventId ?>" aria-labelledby="event-<?= $eventId ?>-title">
    <button type="button" class="popup-close" data-close aria-label="Close">&times;</button>

    <p class="popup-kicker">
        <span class="club-avatar club-avatar-xs" style="--hue: <?= clubHue($club['club_id']) ?>"><?= initials($club['club_name']) ?></span>
        <?= e($club['club_name']) ?>
    </p>
    <h2 class="popup-title" id="event-<?= $eventId ?>-title"><?= e($event['event_name']) ?></h2>

    <dl class="event-facts">
        <div><dt>Date</dt><dd><?= niceDate($event['event_date']) ?></dd></div>
        <div><dt>Time</dt><dd><?= niceTime($event['event_time']) ?></dd></div>
        <div><dt>Venue</dt><dd><?= e($event['location']) ?></dd></div>
        <div><dt><?= $past ? 'Went' : 'Going' ?></dt><dd><span class="going-count" data-count="<?= $going ?>"><?= $going ?></span> people</dd></div>
    </dl>

    <?php if (!empty($event['description'])): ?>
        <p class="popup-text"><?= e($event['description']) ?></p>
    <?php endif; ?>

    <?php if (!$past): ?>
        <!-- RSVP: sent to actions/rsvp_event.php -->
        <form class="rsvp-form" action="actions/rsvp_event.php" method="POST">
            <input type="hidden" name="event_id" value="<?= $eventId ?>">
            <input type="hidden" name="action" value="<?= $isGoing ? 'cancel' : 'rsvp' ?>">
            <button type="submit" class="btn btn-rsvp<?= $isGoing ? ' is-going' : ' btn-primary' ?>">
                <?= $isGoing ? 'Going ✓ · Cancel RSVP' : 'RSVP' ?>
            </button>
        </form>
    <?php else: ?>
        <div class="feedback-section" id="feedback-<?= $eventId ?>">
            <div class="feedback-head">
                <h3>Ratings &amp; feedback</h3>
                <?php if ($reviews): ?>
                    <span class="stars" title="<?= round($average, 1) ?> out of 5"><?= stars(round($average)) ?></span>
                    <span class="muted"><?= number_format($average, 1) ?> (<?= count($reviews) ?>)</span>
                <?php endif; ?>
            </div>

            <?php if ($isGoing && !$alreadyRated): ?>
                <!-- Feedback: sent to actions/submit_feedback.php -->
                <form class="feedback-form" action="actions/submit_feedback.php" method="POST">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <span class="rating-label">Rate this event:</span>
                    <div class="star-input" role="radiogroup" aria-label="Your rating">
                        <?php for ($star = 5; $star >= 1; $star--): ?>
                            <input type="radio" id="rate-<?= $eventId ?>-<?= $star ?>" name="rating" value="<?= $star ?>" required>
                            <label for="rate-<?= $eventId ?>-<?= $star ?>" title="<?= $star ?> star<?= $star > 1 ? 's' : '' ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <textarea class="input-box" name="comment" rows="2" maxlength="500" placeholder="How was the event? (optional)"></textarea>
                    <button type="submit" class="btn btn-dark btn-sm">Submit feedback</button>
                </form>
            <?php endif; ?>

            <?php if ($reviews): ?>
                <ul class="review-list">
                    <?php foreach ($reviews as $review): ?>
                        <li>
                            <p><strong><?= e($review['name']) ?></strong> <span class="stars"><?= stars($review['rating']) ?></span></p>
                            <?php if ($review['comment'] !== ''): ?><p class="muted"><?= e($review['comment']) ?></p><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif (!$isGoing): ?>
                <p class="muted">No feedback yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($canManage): ?>
        <!-- Organiser only -->
        <div class="popup-actions popup-actions-split">
            <span class="badge">Your club's event</span>
            <div>
                <button type="button" class="btn btn-outline btn-sm" data-open="edit-event-<?= $eventId ?>">Edit</button>
                <form action="actions/delete_event.php" method="POST" class="inline-form delete-form"
                      data-confirm="Delete &quot;<?= e($event['event_name']) ?>&quot;? This cannot be undone.">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</dialog>

<?php
    if ($canManage) {
        eventFormDialog('edit-event-' . $eventId, $club['club_id'], $event);
    }
endforeach;
