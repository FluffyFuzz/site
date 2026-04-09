<?php

require_once 'files_save.php';
require_once 'database.php';
$db = new DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mediaid'], $_POST['eventid'])) {
        $fileName = $db->select(
            "SELECT url_media FROM `MEDIA` WHERE id_media = ? AND id_evenement = ?",
            "ii",
            [$_POST['mediaid'], $_POST['eventid']]
        )[0]['url_media'];

    if (deleteFile($fileName)) {
        // Met à jour la base de données avec le nom du fichier
        $db->query(
            "DELETE FROM MEDIA WHERE id_media = ? AND id_evenement = ?",
            "ii",
            [$_POST['mediaid'], $_POST['eventid']]
        );
    }

        // Recharge la page pour afficher la nouvelle image
        header("Location: /my_gallery.php?eventid=" . $_POST["eventid"]);
        exit();
} else {
    header("Location: /index.php");
    exit();
}
