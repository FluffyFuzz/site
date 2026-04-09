<?php
session_start();
require_once 'DB.php';
require_once 'tools.php';
require_once 'filter.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || empty($_SESSION['isAdmin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$DB = new DB();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        get_messages();
        break;
    case 'POST':
        post_message();
        break;
    default:
        http_response_code(405);
        break;
}

function get_messages(): void
{
    global $DB;
    $me = (int) $_SESSION['userid'];

    $messages = $DB->select(
        "SELECT M.id_message, M.id_membre,
                CONCAT(MB.prenom_membre, ' ', MB.nom_membre) AS auteur,
                M.contenu, M.date_message
         FROM MESSAGE_ADMIN M
         JOIN MEMBRE MB ON MB.id_membre = M.id_membre
         ORDER BY M.date_message ASC"
    );

    foreach ($messages as &$msg) {
        $msg['is_mine'] = ((int) $msg['id_membre']) === $me;
    }

    echo json_encode($messages);
}

function post_message(): void
{
    global $DB;
    $data      = json_decode(file_get_contents('php://input'), true);
    $contenu   = filter::string($data['contenu'], minLenght: 1, maxLenght: 2000);
    $id_membre = (int) $_SESSION['userid'];

    $DB->query(
        "INSERT INTO MESSAGE_ADMIN (id_membre, contenu) VALUES (?, ?)",
        "is", [$id_membre, $contenu]
    );

    http_response_code(201);
    echo json_encode(['message' => 'Message envoyé']);
}
