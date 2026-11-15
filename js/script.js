/* ============================================================
   Taylor's Club Events - Front-end JavaScript
   ------------------------------------------------------------
   1. Mobile navigation menu
   2. Show / hide password (login page)
   3. Follow / unfollow buttons (clubs page)
   4. Club category filter (clubs page)
   5. Event calendar (calendar page)
   ============================================================ */

// While the back-end is not ready, follow buttons only change on screen.
// When actions/follow_club.php works, set this to false so the form is
// really submitted to PHP.
const FRONTEND_ONLY = true;

document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
    initPasswordToggle();
    initFollowButtons();
    initClubFilter();
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


/* ---------- 2. Show / hide password ---------- */
function initPasswordToggle() {
    document.querySelectorAll('.toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.target);
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            button.textContent = hidden ? 'Hide' : 'Show';
        });
    });
}


/* ---------- 3. Follow / unfollow buttons ---------- */
function initFollowButtons() {
    document.querySelectorAll('.follow-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!FRONTEND_ONLY) return; // let PHP handle it

            event.preventDefault();
            const button = form.querySelector('.btn-follow');
            const actionInput = form.querySelector('input[name="action"]');
            const nowFollowing = !button.classList.contains('is-following');

            button.classList.toggle('is-following', nowFollowing);
            button.textContent = nowFollowing ? 'Following ✓' : 'Follow+';
            actionInput.value = nowFollowing ? 'unfollow' : 'follow';
        });
    });
}


/* ---------- 4. Club category filter ---------- */
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


/* ---------- 5. Event calendar ---------- */
function initCalendar() {
    const root = document.getElementById('calendar');
    if (!root) return;

    // Events are printed by calendar.php into window.CALENDAR_EVENTS
    const events = window.CALENDAR_EVENTS || [];

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
                const link = document.createElement('a');
                link.className = 'event-item';
                link.href = 'event.php?id=' + encodeURIComponent(ev.id);

                const text = document.createElement('div');
                text.className = 'item-text';
                const name = document.createElement('p');
                name.className = 'item-name';
                name.textContent = ev.title;
                const meta = document.createElement('p');
                meta.className = 'item-meta';
                meta.textContent = ev.club + ' · ' + ev.time + ' · ' + ev.location;
                text.append(name, meta);

                const arrow = document.createElement('span');
                arrow.className = 'arrow';
                arrow.textContent = '→';

                link.append(text, arrow);
                li.appendChild(link);
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
