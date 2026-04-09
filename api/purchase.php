<?php
session_start();

require_once 'DB.php';
require_once 'tools.php';


// TODO: Remove this line in production
ini_set('display_errors', 1);

header('Content-Type: application/json');

tools::checkPermission('p_achat');

$DB = new DB();

$methode = $_SERVER['REQUEST_METHOD'];

switch ($methode) {
    case 'GET':                      # READ
        get_purchase();
        break;
    case 'PATCH':                    # UPDATE statut commande
        toggle_statut();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}

function get_purchase() : void {
    $db = new DB();
    $data = $db->select("SELECT * FROM HISTORIQUE_COMPLET ORDER BY date_transaction DESC");
    echo json_encode($data);
}

function toggle_statut() : void {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id_commande'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing id_commande']);
        return;
    }
    $db = new DB();
    $db->query(
        "UPDATE COMMANDE SET statut_commande = NOT statut_commande WHERE id_commande = ?",
        "i", [(int)$data['id_commande']]
    );
    http_response_code(200);
    echo json_encode(['ok' => true]);
}

