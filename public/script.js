const DAY_NAMES_LT = {
    1: 'Pirmadienis',
    2: 'Antradienis',
    3: 'Trečiadienis',
    4: 'Ketvirtadienis',
    5: 'Penktadienis',
};

function updateDateTime() {
    const dateObject = new Date();
    const day = dateObject.toLocaleDateString('lt-LT', { weekday: 'long' });
    const time = dateObject.toLocaleTimeString('lt-LT');
    const el = document.getElementById('current-day');
    if (el) {
        el.textContent = `${day} ${time}`;
    }
}

function toMinutes(time) {
    const [h, m] = time.split(':').map(Number);
    return h * 60 + m;
}

function formatTime(time) {
    return time.slice(0, 5);
}

function showStatus(message) {
    const status = document.getElementById('schedule-status');
    if (!status) {
        return;
    }
    status.textContent = message;
    status.hidden = message === '';
}

async function loadSchedule() {
    let response;
    try {
        response = await fetch('api/schedule.php', { headers: { Accept: 'application/json' } });
    } catch (e) {
        showStatus('Nepavyko pasiekti serverio. Patikrinkite interneto ryšį.');
        return;
    }

    if (response.status === 401) {
        window.location.href = 'login.php';
        return;
    }

    if (!response.ok) {
        showStatus('Nepavyko įkelti tvarkaraščio.');
        return;
    }

    const data = await response.json();
    const entries = data.entries || [];

    if (entries.length === 0) {
        showStatus('Tvarkaraštis dar neįvestas.');
    } else {
        showStatus('');
    }

    renderSchedule(entries);
}

function groupByDay(entries) {
    const byDay = { 1: [], 2: [], 3: [], 4: [], 5: [] };
    entries.forEach((entry) => {
        if (byDay[entry.day_of_week]) {
            byDay[entry.day_of_week].push(entry);
        }
    });
    Object.values(byDay).forEach((list) => list.sort((a, b) => toMinutes(a.start_time) - toMinutes(b.start_time)));
    return byDay;
}

function uniqueSlots(entries) {
    const map = new Map();
    entries.forEach((entry) => {
        const key = `${entry.start_time}-${entry.end_time}`;
        if (!map.has(key)) {
            map.set(key, { start: entry.start_time, end: entry.end_time });
        }
    });
    return [...map.values()].sort((a, b) => toMinutes(a.start) - toMinutes(b.start));
}

function findEntry(byDay, day, slot) {
    return byDay[day].find((e) => e.start_time === slot.start && e.end_time === slot.end);
}

function lastEntry(byDay, day) {
    const list = byDay[day];
    return list.length ? list[list.length - 1] : null;
}

function buildLessonCellContent(entry) {
    const titleParts = [];
    if (entry.teacher) {
        titleParts.push(`Mokytojas: ${entry.teacher}`);
    }
    if (entry.room) {
        titleParts.push(`Kabinetas: ${entry.room}`);
    }

    return { text: entry.subject, room: entry.room, title: titleParts.join(', ') };
}

function renderDesktopTable(slots, byDay) {
    const tbody = document.getElementById('schedule-body');
    if (!tbody) {
        return;
    }
    tbody.innerHTML = '';

    slots.forEach((slot, index) => {
        const row = document.createElement('tr');
        row.className = 'lesson-row';

        const numberCell = document.createElement('td');
        numberCell.textContent = String(index + 1);
        row.appendChild(numberCell);

        const timeCell = document.createElement('td');
        timeCell.textContent = `${formatTime(slot.start)} - ${formatTime(slot.end)}`;
        row.appendChild(timeCell);

        for (let day = 1; day <= 5; day++) {
            const entry = findEntry(byDay, day, slot);
            const cell = document.createElement('td');
            cell.dataset.day = String(day);
            cell.dataset.lesson = String(index);
            cell.dataset.start = slot.start;
            cell.dataset.end = slot.end;

            if (entry) {
                const content = buildLessonCellContent(entry);
                cell.textContent = content.text;
                if (content.room) {
                    const meta = document.createElement('div');
                    meta.className = 'meta';
                    meta.textContent = content.room;
                    cell.appendChild(meta);
                }
                if (content.title) {
                    cell.title = content.title;
                }
            } else {
                cell.textContent = '❌';
                cell.classList.add('empty-slot');
            }

            row.appendChild(cell);
        }

        tbody.appendChild(row);

        const nextSlot = slots[index + 1];
        if (nextSlot) {
            const breakMinutes = toMinutes(nextSlot.start) - toMinutes(slot.end);
            if (breakMinutes > 0) {
                const breakRow = document.createElement('tr');
                breakRow.className = 'break-row';
                const breakCell = document.createElement('td');
                breakCell.colSpan = 7;
                breakCell.textContent = `Pertrauka · ${breakMinutes} min.`;
                breakRow.appendChild(breakCell);
                tbody.appendChild(breakRow);
            }
        }
    });

    const endRow = document.createElement('tr');
    endRow.className = 'end-row';
    endRow.appendChild(document.createElement('td'));
    const endLabel = document.createElement('td');
    endLabel.textContent = 'Pabaiga';
    endRow.appendChild(endLabel);

    for (let day = 1; day <= 5; day++) {
        const cell = document.createElement('td');
        const last = lastEntry(byDay, day);
        cell.textContent = last ? formatTime(last.end_time) : '—';
        endRow.appendChild(cell);
    }
    tbody.appendChild(endRow);
}

function renderMobileSchedule(slots, byDay) {
    const mobileSchedule = document.getElementById('mobile-schedule');
    if (!mobileSchedule) {
        return;
    }
    mobileSchedule.innerHTML = '';

    for (let day = 1; day <= 5; day++) {
        const daySection = document.createElement('section');
        daySection.className = 'mobile-day';

        const heading = document.createElement('h2');
        heading.textContent = DAY_NAMES_LT[day];
        daySection.appendChild(heading);

        const table = document.createElement('table');
        table.innerHTML = '<thead><tr><th>Nr.</th><th>Laikas</th><th>Pamoka</th></tr></thead>';
        const body = document.createElement('tbody');

        slots.forEach((slot, index) => {
            const entry = findEntry(byDay, day, slot);
            const row = document.createElement('tr');
            const content = entry ? entry.subject : '❌';
            row.innerHTML = `<td>${index + 1}</td><td>${formatTime(slot.start)} - ${formatTime(slot.end)}</td><td data-day="${day}" data-lesson="${index}"></td>`;
            row.children[2].textContent = content;
            if (entry && entry.room) {
                row.children[2].textContent += ` (${entry.room})`;
            }
            body.appendChild(row);

            const nextSlot = slots[index + 1];
            if (nextSlot) {
                const breakMinutes = toMinutes(nextSlot.start) - toMinutes(slot.end);
                if (breakMinutes > 0) {
                    const breakRow = document.createElement('tr');
                    breakRow.className = 'break-row';
                    breakRow.innerHTML = `<td colspan="3">Pertrauka · ${breakMinutes} min.</td>`;
                    body.appendChild(breakRow);
                }
            }
        });

        const last = lastEntry(byDay, day);
        const endRow = document.createElement('tr');
        endRow.className = 'end-row';
        endRow.innerHTML = `<td></td><td>Pabaiga</td><td>${last ? formatTime(last.end_time) : '—'}</td>`;
        body.appendChild(endRow);

        table.appendChild(body);
        daySection.appendChild(table);
        mobileSchedule.appendChild(daySection);
    }
}

function highlightSchedule() {
    document.querySelectorAll('td').forEach((td) => {
        td.classList.remove('today');
        td.classList.remove('current');
    });

    const now = new Date();
    const day = now.getDay();

    if (day < 1 || day > 5) {
        return;
    }

    document.querySelectorAll(`#schedule-body td[data-day="${day}"]`).forEach((cell) => {
        cell.classList.add('today');
    });
    document.querySelectorAll(`.mobile-day td[data-day="${day}"]`).forEach((cell) => {
        cell.classList.add('today');
    });

    const minutes = now.getHours() * 60 + now.getMinutes();
    document.querySelectorAll(`#schedule-body td[data-day="${day}"]`).forEach((cell) => {
        const start = cell.dataset.start;
        const end = cell.dataset.end;
        if (!start || !end) {
            return;
        }
        if (minutes >= toMinutes(start) && minutes <= toMinutes(end)) {
            cell.classList.remove('today');
            cell.classList.add('current');

            const lesson = cell.dataset.lesson;
            const mobileCurrent = document.querySelector(`[data-day="${day}"][data-lesson="${lesson}"]`);
            if (mobileCurrent) {
                mobileCurrent.classList.remove('today');
                mobileCurrent.classList.add('current');
            }
        }
    });
}

function scrollToCurrentDay() {
    if (!window.matchMedia('(max-width: 600px)').matches) {
        return;
    }

    const currentDay = new Date().getDay();
    const dayNumber = currentDay >= 1 && currentDay <= 5 ? currentDay : 1;
    const daySection = document.querySelectorAll('.mobile-day')[dayNumber - 1];

    if (daySection) {
        daySection.scrollIntoView({ behavior: 'auto', block: 'start' });
    }
}

function renderSchedule(entries) {
    const byDay = groupByDay(entries);
    const slots = uniqueSlots(entries);

    renderDesktopTable(slots, byDay);
    renderMobileSchedule(slots, byDay);
    scrollToCurrentDay();
    highlightSchedule();
}

document.addEventListener('DOMContentLoaded', () => {
    updateDateTime();
    setInterval(updateDateTime, 1000);
    setInterval(highlightSchedule, 1000);

    loadSchedule();
});
