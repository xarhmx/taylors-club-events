<?php
/*
 * includes/event_form.php
 * ------------------------------------------------------------
 * The "New Event" / "Edit Event" pop-up form (Figma: Add Event Pop up).
 * One function draws both versions:
 *   eventFormDialog('add-event-1', 1)          -> empty form, creates an event
 *   eventFormDialog('edit-event-3', 1, $event) -> filled-in form, edits event 3
 * ------------------------------------------------------------
 */

function eventFormDialog($dialogId, $clubId, $event = null)
{
    global $oldInput;
    $isEdit = $event !== null;
    $action = $isEdit ? 'actions/update_event.php' : 'actions/create_event.php';

    // After a form error, refill what the organiser typed; otherwise use the event's values
    $old    = (($oldInput['form'] ?? '') === $dialogId) ? $oldInput : null;
    $value  = fn($key) => e($old[$key] ?? ($isEdit ? $event[$key] : ''));
    $minDate = $isEdit ? '' : 'min="' . date('Y-m-d') . '"';   // can't create events in the past
    ?>
    <dialog class="popup popup-form" id="<?= e($dialogId) ?>" aria-labelledby="<?= e($dialogId) ?>-title">
        <button type="button" class="popup-close" data-close aria-label="Close">&times;</button>
        <h2 class="popup-title" id="<?= e($dialogId) ?>-title"><?= $isEdit ? 'Edit Event' : 'New Event' ?></h2>

        <form action="<?= $action ?>" method="POST" class="popup-fields">
            <?= csrfField() ?>
            <?= returnField() ?>
            <input type="hidden" name="club_id" value="<?= (int) $clubId ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="event_id" value="<?= (int) $event['event_id'] ?>">
            <?php endif; ?>

            <label class="field-row">
                <span>Name:</span>
                <input class="input-line" type="text" name="event_name" value="<?= $value('event_name') ?>" maxlength="150" required>
            </label>
            <label class="field-row">
                <span>Date:</span>
                <input class="input-line" type="date" name="event_date" value="<?= $value('event_date') ?>" <?= $minDate ?> required>
            </label>
            <label class="field-row">
                <span>Time:</span>
                <input class="input-line" type="time" name="event_time" value="<?= $value('event_time') ?>" required>
            </label>
            <label class="field-row">
                <span>Venue:</span>
                <input class="input-line" type="text" name="location" value="<?= $value('location') ?>" maxlength="150" required>
            </label>
            <label class="field-row field-row-top">
                <span>Description:</span>
                <textarea class="input-line" name="description" rows="3" maxlength="1000"><?= $value('description') ?></textarea>
            </label>

            <div class="popup-actions">
                <button type="button" class="btn btn-outline btn-sm" data-close>Cancel</button>
                <button type="submit" class="btn btn-dark btn-sm"><?= $isEdit ? 'Save Changes' : 'Create Event' ?></button>
            </div>
        </form>
    </dialog>
    <?php
}
