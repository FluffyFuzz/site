<?php

session_start();

require_once 'DB.php';
require_once 'tools.php';


// TODO: Remove this line in production
ini_set('display_errors', 1);

header('Content-Type: application/json');

Tools::checkPermission('p_achat');

$DB = new DB();

$methode = $_SERVER['REQUEST_METHOD'];

switch ($methode) {
    case 'GET':                      # READ
        get_purchase();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}

function get_purchase(): void
{
    $db = new DB();

    $data = $db->select("SELECT H.*, M.nom_membre, M.prenom_membre FROM HISTORIQUE_COMPLET as H INNER JOIN MEMBRE M on H.id_membre = M.id_membre");

    echo json_encode(array_reverse($data));
}
