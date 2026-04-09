<?php
session_start();
require_once 'DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// URL iCal du dossier "Dpt INFO" dans ADE (une seule URL pour tous les groupes)
// Obtenir : planning.univ-lemans.fr/direct/ → sélectionner "Dpt INFO" → icône iCal
define('ADE_ICS_URL',   'https://planning.univ-lemans.fr/jsp/custom/modules/plannings/anonymous_cal.jsp?resources=71&projectId=8&calType=ical&nbWeeks=44');
define('ADE_ICS_CACHE', '/tmp/adiil_agenda_dpt.ics');
define('ADE_CACHE_TTL', 3600);  // 1 heure

// Hiérarchie complète des groupes (Grp TP → TD → BUT année)
// Utilisée pour filtrer l'ICS départemental : chaque étudiant ne voit
// que ses cours TP + ses cours TD + ses cours CM.
const ADE_HIERARCHY = [
    '11A' => ['grp' => 'Grp 11A', 'td' => 'TD11', 'but' => 'BUT INFO1'],
    '11B' => ['grp' => 'Grp 11B', 'td' => 'TD11', 'but' => 'BUT INFO1'],
    '12C' => ['grp' => 'Grp 12C', 'td' => 'TD12', 'but' => 'BUT INFO1'],
    '12D' => ['grp' => 'Grp 12D', 'td' => 'TD12', 'but' => 'BUT INFO1'],
    '21A' => ['grp' => 'Grp 21A', 'td' => 'TD21', 'but' => 'BUT INFO2'],
    '21B' => ['grp' => 'Grp 21B', 'td' => 'TD21', 'but' => 'BUT INFO2'],
    '22C' => ['grp' => 'Grp 22C', 'td' => 'TD22', 'but' => 'BUT INFO2'],
    '22D' => ['grp' => 'Grp 22D', 'td' => 'TD22', 'but' => 'BUT INFO2'],
    '31A' => ['grp' => 'Grp 31A', 'td' => 'TD31', 'but' => 'BUT INFO3'],
    '31B' => ['grp' => 'Grp 31B', 'td' => 'TD31', 'but' => 'BUT INFO3'],
    '32C' => ['grp' => 'Grp 32C', 'td' => 'TD32', 'but' => 'BUT INFO3'],
    '32D' => ['grp' => 'Grp 32D', 'td' => 'TD32', 'but' => 'BUT INFO3'],
];

$db     = new DB();
$member = $db->select(
    "SELECT tp_membre FROM MEMBRE WHERE id_membre = ?",
    "i",
    [$_SESSION['userid']]
);

$tp_membre = (!empty($member) && !empty($member[0]['tp_membre'])) ? $member[0]['tp_membre'] : null;
$hierarchy = ($tp_membre !== null && isset(ADE_HIERARCHY[$tp_membre])) ? ADE_HIERARCHY[$tp_membre] : null;

$ics_content = fetchICS();
$ics_events  = $ics_content ? parseICS($ics_content, $hierarchy) : [];
$site_events = getSiteEvents((int)$_SESSION['userid']);

echo json_encode(array_merge($ics_events, $site_events));


// ────────────────────────────────────────────────────────────────────────────
// Fonctions
// ────────────────────────────────────────────────────────────────────────────

/**
 * Récupère le contenu ICS du département (cache partagé 1h).
 * Fallback sur le cache expiré si la requête réseau échoue.
 */
function fetchICS(): string
{
    if (ADE_ICS_URL === '') {
        return '';
    }

    if (file_exists(ADE_ICS_CACHE) && (time() - filemtime(ADE_ICS_CACHE)) < ADE_CACHE_TTL) {
        return file_get_contents(ADE_ICS_CACHE);
    }

    $ctx     = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0']]);
    $content = @file_get_contents(ADE_ICS_URL, false, $ctx);

    if ($content !== false) {
        @file_put_contents(ADE_ICS_CACHE, $content);
        return $content;
    }

    return file_exists(ADE_ICS_CACHE) ? file_get_contents(ADE_ICS_CACHE) : '';
}

/**
 * Parse le fichier ICS et filtre les événements selon la hiérarchie de groupe.
 *
 * Règles de filtrage (dans l'ordre) :
 *   1. DESCRIPTION contient "Grp XX"  → afficher uniquement si XX = tp_membre
 *   2. DESCRIPTION contient "TDxx"    → afficher uniquement si xx = td du groupe
 *   3. DESCRIPTION contient "BUT INFOx" → afficher uniquement si x = année du groupe
 *   4. Aucun identifiant de groupe     → afficher toujours
 */
function parseICS(string $ics, ?array $hierarchy): array
{
    if (empty($ics)) {
        return [];
    }

    $tz = new DateTimeZone('Europe/Paris');

    // Normaliser les fins de ligne puis déplier les lignes continues (RFC 5545)
    $ics = str_replace(["\r\n", "\r"], "\n", $ics);
    $ics = preg_replace("/\n[ \t]/", '', $ics);

    $events = [];
    preg_match_all('/BEGIN:VEVENT\n(.*?)END:VEVENT/s', $ics, $matches);

    foreach ($matches[1] as $block) {
        $props = [];
        foreach (explode("\n", $block) as $line) {
            $colon = strpos($line, ':');
            if ($colon === false) {
                continue;
            }
            $key   = explode(';', substr($line, 0, $colon))[0];
            $value = substr($line, $colon + 1);
            $props[trim($key)] = trim($value);
        }

        if (!isset($props['DTSTART'], $props['DTEND'], $props['SUMMARY'])) {
            continue;
        }

        $description = unescapeICS($props['DESCRIPTION'] ?? '');
        $summary     = unescapeICS($props['SUMMARY']);
        $location    = unescapeICS($props['LOCATION'] ?? '');

        if (!shouldShowEvent($description, $hierarchy)) {
            continue;
        }

        try {
            $start = parseICSDate($props['DTSTART'], $tz);
            $end   = parseICSDate($props['DTEND'], $tz);
        } catch (Exception $e) {
            continue;
        }

        $events[] = [
            'title'       => $summary,
            'start'       => $start->format('c'),
            'end'         => $end->format('c'),
            'location'    => $location,
            'description' => $description,
            'type'        => 'course',
            'color'       => '#4A90D9',
        ];
    }

    return $events;
}

/**
 * Détermine si un événement ICS doit être affiché pour l'étudiant.
 *
 * @param string     $description  Valeur DESCRIPTION déjà décodée
 * @param array|null $hierarchy    ['grp'=>'Grp 21A','td'=>'TD21','but'=>'BUT INFO2']
 */
function shouldShowEvent(string $description, ?array $hierarchy): bool
{
    // Pas de groupe configuré → tout afficher
    if ($hierarchy === null) {
        return true;
    }

    // 1. Cours TP : "Grp 21A", "Grp 11B", etc.
    if (preg_match('/Grp \d+[A-Z]/', $description, $m)) {
        return $m[0] === $hierarchy['grp'];
    }

    // 2. Cours TD : "TD21", "TD11", etc.
    if (preg_match('/\bTD\d+\b/', $description, $m)) {
        return $m[0] === $hierarchy['td'];
    }

    // 3. Cours CM / promo : "BUT INFO2", "BUT INFO1", etc.
    if (preg_match('/BUT INFO\d/', $description, $m)) {
        return $m[0] === $hierarchy['but'];
    }

    // 4. Aucun identifiant connu → afficher
    return true;
}

/**
 * Convertit une date ICS en DateTime Europe/Paris.
 * Supporte UTC (terminé par Z) et local.
 */
function parseICSDate(string $str, DateTimeZone $tz): DateTime
{
    if (str_ends_with($str, 'Z')) {
        $dt = new DateTime($str, new DateTimeZone('UTC'));
        $dt->setTimezone($tz);
    } else {
        $dt = new DateTime($str, $tz);
    }
    return $dt;
}

/**
 * Décode les séquences d'échappement ICS.
 */
function unescapeICS(string $s): string
{
    return str_replace(['\\n', '\\N', '\\,', '\\;'], ["\n", "\n", ',', ';'], $s);
}

/**
 * Récupère les événements BDE auxquels l'étudiant est inscrit.
 */
function getSiteEvents(int $userId): array
{
    $db   = new \DB();
    $rows = $db->select(
        "SELECT E.nom_evenement, E.lieu_evenement, E.date_evenement, E.description_evenement
         FROM EVENEMENT E
         INNER JOIN INSCRIPTION I ON I.id_evenement = E.id_evenement
         WHERE I.id_membre = ? AND E.deleted = false",
        "i",
        [$userId]
    );

    $tz     = new DateTimeZone('Europe/Paris');
    $events = [];

    foreach ($rows as $row) {
        $start = new DateTime($row['date_evenement'], $tz);
        $end   = clone $start;
        $end->modify('+2 hours');

        $events[] = [
            'title'       => $row['nom_evenement'],
            'start'       => $start->format('c'),
            'end'         => $end->format('c'),
            'location'    => $row['lieu_evenement'] ?? '',
            'description' => $row['description_evenement'] ?? '',
            'type'        => 'event',
            'color'       => '#4CAF50',
        ];
    }

    return $events;
}
