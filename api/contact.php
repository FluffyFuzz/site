<?php
session_start();
require_once 'DB.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || empty($_SESSION['isAdmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$DB = new DB();

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        $messages = $DB->select(
            "SELECT id_contact, nom_contact, prenom_contact, email_contact,
                    objet_contact, message_contact, date_contact, lu_contact, reponse_faq
             FROM CONTACT ORDER BY date_contact DESC"
        );
        $DB->query("UPDATE CONTACT SET lu_contact = 1 WHERE lu_contact = 0");
        echo json_encode($messages);
        break;

    case 'PATCH':
        $data       = json_decode(file_get_contents('php://input'), true);
        $id         = (int) ($data['id_contact'] ?? 0);
        $reponse    = trim($data['reponse_faq'] ?? '');
        if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'id manquant']); exit; }
        // Réponse vide = retirer de la FAQ
        $DB->query(
            "UPDATE CONTACT SET reponse_faq = ? WHERE id_contact = ?",
            "si", [$reponse === '' ? null : $reponse, $id]
        );
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        break;
}
