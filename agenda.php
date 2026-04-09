<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda</title>
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">
    <link rel="stylesheet" href="/styles/planner_style.css">
</head>
<body class="body_margin">

<?php require_once "header.php"; ?>

<h1>Agenda</h1>

<div id="cal-wrap">

    <div id="cal-nav">
        <button id="btn-prev" aria-label="Semaine précédente">&#8249;</button>
        <span id="week-label"></span>
        <button id="btn-next" aria-label="Semaine suivante">&#8250;</button>
        <button id="btn-today">Aujourd'hui</button>
    </div>

    <div id="cal-legend">
        <span class="ldot course"></span><span>Cours (université)</span>
        <span class="ldot bde"></span><span>Événement BDE</span>
    </div>

    <div id="cal-scroll">
        <div id="cal-grid">
            <div id="time-col" aria-hidden="true"></div>
            <div id="days-row"></div>
        </div>
    </div>

    <p id="cal-error" hidden>Impossible de charger l'emploi du temps.</p>

</div>

<?php require_once "footer.php"; ?>

<script>
// ── Configuration ────────────────────────────────────────────────────────────
const H_START   = 8;          // première heure affichée
const H_END     = 20;         // dernière heure (exclusive)
const SLOT_PX   = 44;         // hauteur en px d'une demi-heure
const DAYS_SHORT = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
const MONTHS_FR  = ['jan.','fév.','mars','avr.','mai','juin',
                    'juil.','août','sept.','oct.','nov.','déc.'];

let weekOffset  = 0;
let cachedEvents = null;

// ── Utilitaires date ─────────────────────────────────────────────────────────

/** Retourne le lundi de la semaine courante + offset semaines. */
function getMonday(offset) {
    const today = new Date();
    const day   = today.getDay();                    // 0=dim
    const diff  = day === 0 ? -6 : 1 - day;
    const mon   = new Date(today);
    mon.setDate(today.getDate() + diff + offset * 7);
    mon.setHours(0, 0, 0, 0);
    return mon;
}

function addDays(date, n) {
    const d = new Date(date);
    d.setDate(d.getDate() + n);
    return d;
}

function sameDay(a, b) {
    return a.getFullYear() === b.getFullYear() &&
           a.getMonth()    === b.getMonth()    &&
           a.getDate()     === b.getDate();
}

function fmtShort(d) {
    return d.getDate() + '\u202f' + MONTHS_FR[d.getMonth()];
}

function fmtTime(d) {
    return d.getHours().toString().padStart(2,'0') + ':' +
           d.getMinutes().toString().padStart(2,'0');
}

function esc(s) {
    return String(s)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;');
}

// ── Rendu ────────────────────────────────────────────────────────────────────

/** Un événement est "toute la journée" si son heure de début est minuit pile
 *  (cas typique d'un DATETIME stocké sans heure en base). */
function isAllDay(ev) {
    const s = new Date(ev.start);
    return s.getHours() === 0 && s.getMinutes() === 0 && s.getSeconds() === 0;
}

function buildTimeCol() {
    const col = document.getElementById('time-col');
    col.innerHTML = '';
    const slots = (H_END - H_START) * 2;
    for (let i = 0; i < slots; i++) {
        const h   = H_START + Math.floor(i / 2);
        const div = document.createElement('div');
        div.className = 'time-label';
        div.style.height = SLOT_PX + 'px';
        if (i % 2 === 0) div.textContent = h + ':00';
        col.appendChild(div);
    }
}

function buildDayCol(date, events, today) {
    const col = document.createElement('div');
    col.className = 'day-col' + (sameDay(date, today) ? ' is-today' : '');

    // En-tête
    const hdr = document.createElement('div');
    hdr.className = 'day-hdr';
    hdr.innerHTML =
        `<span class="d-name">${DAYS_SHORT[date.getDay()]}</span>` +
        `<span class="d-num">${date.getDate()}</span>`;
    col.appendChild(hdr);

    const dayEvs    = events.filter(ev => sameDay(new Date(ev.start), date));
    const allDayEvs = dayEvs.filter(isAllDay);
    const timedEvs  = dayEvs.filter(ev => !isAllDay(ev));

    // Grille temporelle
    const grid = document.createElement('div');
    grid.className = 'day-grid';
    grid.style.height = (H_END - H_START) * 2 * SLOT_PX + 'px';

    // Lignes de fond (demi-heures)
    const slots = (H_END - H_START) * 2;
    for (let i = 0; i < slots; i++) {
        const line = document.createElement('div');
        line.className = 'slot-bg' + (i % 2 === 0 ? ' h-line' : '');
        line.style.top = (i * SLOT_PX) + 'px';
        grid.appendChild(line);
    }

    timedEvs.forEach(ev => grid.appendChild(makeEventBlock(ev)));

    // Badges "journée entière" en overlay haut-droit, hors du flux
    if (allDayEvs.length > 0) {
        const overlay = document.createElement('div');
        overlay.className = 'allday-overlay';
        allDayEvs.forEach(ev => {
            const badge = document.createElement('div');
            badge.className = 'allday-badge';
            badge.style.background = ev.color;
            badge.textContent = ev.title;
            badge.title = ev.title + (ev.location ? '\n📍 ' + ev.location : '');
            overlay.appendChild(badge);
        });
        grid.appendChild(overlay);
    }

    col.appendChild(grid);
    return col;
}

function makeEventBlock(ev) {
    const start    = new Date(ev.start);
    const end      = new Date(ev.end);
    const startMin = (start.getHours() - H_START) * 60 + start.getMinutes();
    const durMin   = Math.round((end - start) / 60000);
    const totalMin = (H_END - H_START) * 60;

    if (startMin < 0 || startMin >= totalMin) return document.createDocumentFragment();

    const top       = startMin / 30 * SLOT_PX;
    const maxHeight = (totalMin - startMin) / 30 * SLOT_PX;
    const height    = Math.min(Math.max(durMin / 30 * SLOT_PX, SLOT_PX * 0.85), maxHeight);

    const block = document.createElement('div');
    block.className = 'ev-block';
    block.style.cssText = `top:${top}px;height:${height}px;background:${ev.color}`;

    const timeStr = fmtTime(start) + '–' + fmtTime(end);
    block.innerHTML =
        `<div class="ev-time">${timeStr}</div>` +
        `<div class="ev-title">${esc(ev.title)}</div>` +
        (ev.location ? `<div class="ev-loc">${esc(ev.location)}</div>` : '');

    block.title = ev.title + '\n' + timeStr +
                  (ev.location    ? '\n📍 ' + ev.location    : '') +
                  (ev.description ? '\n'    + ev.description.substring(0, 120) : '');

    return block;
}

function render() {
    const monday = getMonday(weekOffset);
    const sunday = addDays(monday, 6);
    const today  = new Date();

    document.getElementById('week-label').textContent =
        fmtShort(monday) + ' — ' + fmtShort(sunday);

    buildTimeCol();

    const row = document.getElementById('days-row');
    row.innerHTML = '';
    const events = cachedEvents || [];
    for (let d = 0; d < 7; d++) {
        row.appendChild(buildDayCol(addDays(monday, d), events, today));
    }
}

// ── Chargement des données ────────────────────────────────────────────────────

async function loadEvents() {
    try {
        const r = await fetch('/api/agenda.php');
        if (!r.ok) throw new Error('HTTP ' + r.status);
        cachedEvents = await r.json();
        if (!Array.isArray(cachedEvents)) cachedEvents = [];
    } catch (e) {
        console.error('Agenda API error:', e);
        cachedEvents = [];
        document.getElementById('cal-error').hidden = false;
    }
    render();
}

// ── Navigation ────────────────────────────────────────────────────────────────

document.getElementById('btn-prev') .addEventListener('click', () => { weekOffset--; render(); });
document.getElementById('btn-next') .addEventListener('click', () => { weekOffset++; render(); });
document.getElementById('btn-today').addEventListener('click', () => { weekOffset = 0; render(); });

// ── Démarrage ─────────────────────────────────────────────────────────────────
loadEvents();
</script>

</body>
</html>
