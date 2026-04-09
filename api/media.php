<?php
session_start();
require_once 'DB.php';
require_once 'tools.php';
require_once 'filter.php';

header('Content-Type: application/json');

tools::checkPermission('p_evenement');

$DB = new DB();
$methode = $_SERVER['REQUEST_METHOD'];

switch ($methode) {
    case 'GET':
        get_media();
        break;
    case 'DELETE':
        delete_media();
        break;
    default:
        http_response_code(405);
        break;
}

function get_media(): void
{
    global $DB;
    $id_evenement = filter::int($_GET['id_evenement']);
    $media = $DB->select(
        "SELECT id_media, url_media, date_media FROM MEDIA WHERE id_evenement = ? ORDER BY date_media DESC",
        "i", [$id_evenement]
    );
    echo json_encode($media);
}

function delete_media(): void
{
    global $DB;
    $id = filter::int($_GET['id']);
    $DB->query("DELETE FROM MEDIA WHERE id_media = ?", "i", [$id]);
    http_response_code(200);
    echo json_encode(['message' => 'Media deleted']);
}
