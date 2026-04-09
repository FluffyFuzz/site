<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['eventid'])) {
    header('Location: /events.php');
    exit;
}

$userid  = (int) $_SESSION['userid'];
$eventid = (int) $_POST['eventid'];

$db = new DB();

// Vérifie que l'inscription existe bien
$inscription = $db->select(
    "SELECT * FROM INSCRIPTION WHERE id_membre = ? AND id_evenement = ?",
    "ii", [$userid, $eventid]
);

if (empty($inscription)) {
    header("Location: /event_details.php?id=$eventid");
    exit;
}

// Supprime l'inscription
$db->query(
    "DELETE FROM INSCRIPTION WHERE id_membre = ? AND id_evenement = ?",
    "ii", [$userid, $eventid]
);

// Retire l'XP associé à l'événement
$xp = $db->select(
    "SELECT xp_evenement FROM EVENEMENT WHERE id_evenement = ?",
    "i", [$eventid]
);
if (!empty($xp)) {
    $db->query(
        "UPDATE MEMBRE SET xp_membre = GREATEST(0, xp_membre - ?) WHERE id_membre = ?",
        "ii", [$xp[0]['xp_evenement'], $userid]
    );
}

header("Location: /event_details.php?id=$eventid");
exit;
