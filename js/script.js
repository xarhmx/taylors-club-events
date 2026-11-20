/* ============================================================
   Taylor's Club Events - Front-end JavaScript
   ------------------------------------------------------------
   1.  Mobile navigation menu
   2.  Password fields (show/hide, "passwords match" check)
   3.  Pop-ups (club details, event details, add/edit event)
   4.  Forms: delete confirmation + messages from the server
   5.  Club category filter (clubs page)
   6.  Organiser: edit club details in place (clubs page)
   7.  Profile photo pop-up (edit profile page)
   8.  Event calendar (calendar page)
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
    initPasswordFields();
    initPopups();
    initForms();
    initFlash();
    initClubFilter();
    initClubEditing();
    initPhotoPopup();
    initCalendar();
});


/* ---------- 1. Mobile navigation menu ---------- */
function initMobileMenu() {
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.getElementById('navMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function () {
        const isOpen = menu.classList.toggle('open');
        toggle.setAttribute('aria-expanded', isOpen);
    });
}


/* ---------- 2. Password fields ---------- */
function initPasswordFields() {
    // Show / hide button
    document.querySelectorAll('.toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.target);
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            button.textContent = hidden ? 'Hide' : 'Show';
        });
    });

    // Register page: "Confirm" must match "Password"
    const password = document.getElementById('password');
    const confirmBox = document.getElementById('confirm_password');
    if (password && confirmBox) {
        const check = function () {
            confirmBox.setCustomValidity(
                confirmBox.value && confirmBox.value !== password.value ? 'Passwords do not match.' : ''
            );
        };
        password.addEventListener('input', check);
        confirmBox.addEventListener('input', check);
    }
}


/* ---------- 3. Pop-ups ---------- */
// Any element with data-open="some-id" opens <dialog id="some-id">.
// Any element with data-close inside a pop-up closes it.
function openPopup(id) {
    const popup = document.getElementById(id);
    if (!popup || typeof popup.showModal !== 'function') return;

    // Only one pop-up at a time (e.g. club details -> event details)
    document.querySelectorAll('dialog.popup[open]').forEach(d => d.close());
    popup.showModal();
}

function initPopups() {
    // Works for buttons added later too (e.g. calendar events)
    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-open]');
        if (opener) {
            event.preventDefault();
            openPopup(opener.dataset.open);
            return;
        }
        const closer = event.target.closest('[data-close]');
        if (closer) {
            const popup = closer.closest('dialog');
            if (popup) popup.close();
        }
    });

    // Clicking the dark area outside a pop-up closes it
    document.querySelectorAll('dialog.popup').forEach(function (popup) {
        popup.addEventListener('click', function (event) {
            if (event.target !== popup) return;
            const box = popup.getBoundingClientRect();
            const outside = event.clientX < box.left || event.clientX > box.right ||
                            event.clientY < box.top || event.clientY > box.bottom;
            if (outside) popup.close();
        });
    });

    // Open a pop-up from the link, e.g. clubs.php?club=3 or calendar.php?event=5
    const params = new URLSearchParams(window.location.search);
    if (params.has('event')) {
        openPopup('event-' + params.get('event'));
    } else if (params.has('club')) {
        const id = params.get('club');
        if (document.getElementById('club-' + id)) {
            openPopup('club-' + id);
        } else {
            // Organiser's own club has no pop-up: scroll to its card instead
            const card = document.getElementById('club-card-' + id);
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}


/* ---------- 4. Forms ---------- */
function initForms() {
    document.addEventListener('submit', function (event) {
        const form = event.target;

        // "Are you sure?" before deleting
        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }

        // Stop double-clicks sending the same form twice
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            setTimeout(() => { button.disabled = true; }, 0);
        }
    });
}

// Shows the one-time message from PHP (e.g. "Event created!") and
// re-opens the pop-up the user was using before the page reloaded.
function initFlash() {
    const data = document.getElementById('flashData');
    if (!data) return;
    if (data.dataset.message) showToast(data.dataset.message, data.dataset.type);
    if (data.dataset.open) openPopup(data.dataset.open);
}

// Small message at the bottom of the screen
function showToast(message, type) {
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        toast.setAttribute('role', 'status');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.toggle('toast-error', type === 'error');
    toast.classList.add('show');
    clearTimeout(toast.hideTimer);
    toast.hideTimer = setTimeout(() => toast.classList.remove('show'), type === 'error' ? 5000 : 3000);
}


/* ---------- 5. Club category filter ---------- */
function initClubFilter() {
    const chips = document.querySelectorAll('.filter-chip');
    const cards = document.querySelectorAll('.club-card');
    if (!chips.length) return;

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const category = chip.dataset.category;

            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');

            cards.forEach(function (card) {
                const match = category === 'All' || card.dataset.category === category;
                card.classList.toggle('is-hidden', !match);
            });
        });
    });
}


/* ---------- 6. Organiser: edit club details in place ---------- */
function initClubEditing() {
    document.querySelectorAll('[data-edit-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('.club-card');
            const editing = card.classList.toggle('is-editing');
            if (editing) {
                card.querySelector('textarea[name="description"]').focus();
            } else {
                card.querySelector('form').reset();   // Cancel = undo changes
            }
        });
    });
}


/* ---------- 7. Profile photo pop-up ---------- */
function initPhotoPopup() {
    const button = document.getElementById('avatarButton');
    const popup = document.getElementById('photoPopup');
    if (!button || !popup) return;

    const input = document.getElementById('photoInput');
    const preview = document.getElementById('avatarPreview');
    const fileName = document.getElementById('photoFileName');
    const deleteBox = popup.querySelector('input[name="delete_photo"]');
    const originalPhoto = preview.innerHTML;
    const defaultIcon = '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="8" r="4.2"/>' +
                        '<path d="M3.5 21c0-4.4 3.8-7.5 8.5-7.5s8.5 3.1 8.5 7.5z"/></svg>';
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB

    function setOpen(open) {
        popup.hidden = !open;
        button.setAttribute('aria-expanded', open);
    }

    button.addEventListener('click', () => setOpen(popup.hidden));

    // Close when clicking anywhere else
    document.addEventListener('click', function (event) {
        if (!popup.hidden && !popup.contains(event.target) && !button.contains(event.target)) {
            setOpen(false);
        }
    });

    // Check the chosen file and show a preview
    input.addEventListener('change', function () {
        const file = input.files[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            showToast('Please choose a JPG or PNG image.');
            input.value = '';
            return;
        }
        if (file.size > MAX_SIZE) {
            showToast('That photo is over 2MB. Please choose a smaller one.');
            input.value = '';
            return;
        }

        deleteBox.checked = false;
        fileName.textContent = 'Selected: ' + file.name;
        preview.innerHTML = '';
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = '';
        preview.appendChild(img);
    });

    // "Delete current profile photo?"
    deleteBox.addEventListener('change', function () {
        if (deleteBox.checked) {
            input.value = '';
            fileName.textContent = '';
            preview.innerHTML = defaultIcon;
        } else {
            preview.innerHTML = originalPhoto;
        }
    });
}


/* ---------- 8. Event calendar ---------- */
function initCalendar() {
    const root = document.getElementById('calendar');
    if (!root) return;

    // Events are printed by calendar.php as JSON data in <script id="calendarData">
    const dataTag = document.getElementById('calendarData');
    const events = dataTag ? JSON.parse(dataTag.textContent) : [];

    const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July',
                    'August', 'September', 'October', 'November', 'December'];
    const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    const monthSelect = document.getElementById('calMonth');
    const yearSelect = document.getElementById('calYear');
    const grid = document.getElementById('calGrid');
    const list = document.getElementById('eventList');
    const panelTitle = document.getElementById('eventPanelTitle');
    const showAllBtn = document.getElementById('showAllBtn');

    // Group events by date: { "2026-11-21": [event, event], ... }
    const eventsByDate = {};
    events.forEach(function (ev) {
        (eventsByDate[ev.date] = eventsByDate[ev.date] || []).push(ev);
    });

    const today = new Date();
    const todayKey = toKey(today.getFullYear(), today.getMonth(), today.getDate());

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();
    let selectedKey = null;

    // Fill the month and year dropdowns
    MONTHS.forEach(function (name, i) {
        monthSelect.add(new Option(name.slice(0, 3), i));
    });
    for (let y = viewYear - 1; y <= viewYear + 2; y++) {
        yearSelect.add(new Option(y, y));
    }

    // Arrow buttons and dropdowns
    document.getElementById('calPrev').addEventListener('click', function () { changeMonth(-1); });
    document.getElementById('calNext').addEventListener('click', function () { changeMonth(1); });
    monthSelect.addEventListener('change', function () { viewMonth = +monthSelect.value; selectedKey = null; render(); });
    yearSelect.addEventListener('change', function () { viewYear = +yearSelect.value; selectedKey = null; render(); });
    showAllBtn.addEventListener('click', function () { selectedKey = null; render(); });

    render();

    function changeMonth(step) {
        viewMonth += step;
        if (viewMonth < 0)  { viewMonth = 11; viewYear--; }
        if (viewMonth > 11) { viewMonth = 0;  viewYear++; }
        selectedKey = null;
        render();
    }

    // Draw the grid of days for the current month
    function render() {
        monthSelect.value = viewMonth;
        yearSelect.value = viewYear;
        grid.innerHTML = '';

        const firstWeekday = new Date(viewYear, viewMonth, 1).getDay();
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();
        const totalCells = Math.ceil((firstWeekday + daysInMonth) / 7) * 7;

        for (let cell = 0; cell < totalCells; cell++) {
            const dayNumber = cell - firstWeekday + 1;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'cal-day';

            if (dayNumber < 1) {                        // days from previous month
                button.textContent = daysInPrevMonth + dayNumber;
                button.classList.add('other-month');
                button.disabled = true;
            } else if (dayNumber > daysInMonth) {       // days from next month
                button.textContent = dayNumber - daysInMonth;
                button.classList.add('other-month');
                button.disabled = true;
            } else {
                const key = toKey(viewYear, viewMonth, dayNumber);
                button.textContent = dayNumber;
                button.dataset.date = key;

                if (key === todayKey) button.classList.add('today');
                if (eventsByDate[key]) {
                    button.classList.add('has-event');
                    button.title = eventsByDate[key].length + ' event(s)';
                }
                if (key === selectedKey) button.classList.add('selected');

                // Click a day to show only its events; click again to show the whole month
                button.addEventListener('click', function () {
                    selectedKey = selectedKey === key ? null : key;
                    render();
                });
            }
            grid.appendChild(button);
        }

        renderList();
    }

    // Draw the list of events on the right
    function renderList() {
        let keys;
        if (selectedKey) {
            keys = [selectedKey];
            panelTitle.textContent = 'Events on ' + longDate(selectedKey);
            showAllBtn.hidden = false;
        } else {
            const prefix = viewYear + '-' + pad(viewMonth + 1) + '-';
            keys = Object.keys(eventsByDate).filter(k => k.startsWith(prefix)).sort();
            panelTitle.textContent = 'Events in ' + MONTHS[viewMonth] + ' ' + viewYear;
            showAllBtn.hidden = true;
        }

        list.innerHTML = '';
        const keysWithEvents = keys.filter(k => eventsByDate[k]);

        if (!keysWithEvents.length) {
            list.innerHTML = '<p class="empty-state">No events ' +
                (selectedKey ? 'on this day.' : 'this month.') + '</p>';
            return;
        }

        keysWithEvents.forEach(function (key, index) {
            const group = document.createElement('div');
            group.className = 'event-group';
            group.style.animationDelay = (index * 60) + 'ms';

            const heading = document.createElement('p');
            heading.className = 'event-group-date';
            heading.textContent = longDate(key);
            group.appendChild(heading);

            const ul = document.createElement('ul');
            eventsByDate[key].forEach(function (ev) {
                const li = document.createElement('li');

                // Button that opens the Event Details pop-up
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'event-item';
                item.dataset.open = 'event-' + ev.id;

                const text = document.createElement('span');
                text.className = 'item-text';
                const name = document.createElement('span');
                name.className = 'item-name';
                name.textContent = ev.title;
                const meta = document.createElement('span');
                meta.className = 'item-meta';
                meta.textContent = ev.club + ' · ' + ev.time + ' · ' + ev.location;
                text.append(name, meta);

                if (ev.going) {
                    const tag = document.createElement('span');
                    tag.className = 'going-tag';
                    tag.textContent = 'Going';
                    item.append(text, tag);
                } else {
                    item.append(text);
                }

                const arrow = document.createElement('span');
                arrow.className = 'arrow';
                arrow.textContent = '→';
                item.append(arrow);

                li.appendChild(item);
                ul.appendChild(li);
            });
            group.appendChild(ul);
            list.appendChild(group);
        });
    }

    // Helpers
    function pad(n) { return String(n).padStart(2, '0'); }

    function toKey(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }

    // "2026-11-21" -> "Saturday, 21st November 2026"
    function longDate(key) {
        const parts = key.split('-').map(Number);
        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        return DAYS[date.getDay()] + ', ' + ordinal(parts[2]) + ' ' + MONTHS[parts[1] - 1] + ' ' + parts[0];
    }

    function ordinal(n) {
        const s = ['th', 'st', 'nd', 'rd'];
        const v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }
}
