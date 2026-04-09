<?php

session_start();
use model\Role;

require_once 'filter.php';
require_once 'models/Role.php';
require_once 'DB.php';
require_once 'tools.php';

// TODO: Remove this line in production
ini_set('display_errors', 1);

header('Content-Type: application/json');


Tools::checkPermission('p_role');


$methode = $_SERVER['REQUEST_METHOD'];


switch ($methode) {
    case 'GET':                      # READ
        get_role();
        break;
    case 'POST':                     # CREATE
            create_role();
        break;
    case 'PUT':
        if (Tools::methodAccepted('application/json')) {
            update_role();
        }
        break;

    case 'DELETE':                   # DELETE
        delete_role();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}

function get_role(): void
{
    if (isset($_GET['id'])) {
        // Si un ID est précisé, on renvoie les infos de l'utilisateur correspondant avec ses rôles
        $id = filter::int($_GET['id']);

        $data = Role::getInstance($id);

        if (!$data) {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
            return;
        }
    } else {
        // Sinon, on renvoie la liste de tous les utilisateurs. On va juste préciser si ils ont des rôles ou non
        $data = Role::bulkFetch();
    }

    http_response_code(200);
    echo json_encode($data);
}

function create_role(): void
{
    $role = Role::create("Nouveau role", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

    http_response_code(201);
    echo json_encode($role);
}

function update_role(): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name'], $data['permissions'], $_GET['id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing parameters']);
        return;
    }

    $id = filter::int($_GET['id']);
    $name = filter::string($data['name']);
    $p_log = filter::bool($data['permissions']['p_log'] ?? false);
    $p_boutique = filter::bool($data['permissions']['p_boutique'] ?? false);
    $p_reunion = filter::bool($data['permissions']['p_reunion'] ?? false);
    $p_utilisateur = filter::bool($data['permissions']['p_utilisateur'] ?? false);
    $p_grade = filter::bool($data['permissions']['p_grade'] ?? false);
    $p_role = filter::bool($data['permissions']['p_role'] ?? false);
    $p_actualite = filter::bool($data['permissions']['p_actualite'] ?? false);
    $p_evenement = filter::bool($data['permissions']['p_evenement'] ?? false);
    $p_comptabilite = filter::bool($data['permissions']['p_comptabilite'] ?? false);
    $p_achat = filter::bool($data['permissions']['p_achat'] ?? false);
    $p_moderation = filter::bool($data['permissions']['p_moderation'] ?? false);

    $role = Role::getInstance($id);

    if (!$role) {
        http_response_code(404);
        echo json_encode(['message' => 'Role not found']);
        return;
    }

    $role->update($name, $p_log, $p_boutique, $p_reunion, $p_utilisateur, $p_grade, $p_role, $p_actualite, $p_evenement, $p_comptabilite, $p_achat, $p_moderation);

    http_response_code(200);
    echo json_encode($role);
}

function delete_role(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing parameters']);
        return;
    }

    $id = filter::int($_GET['id']);

    $role = Role::getInstance($id);

    if (!$role) {
        http_response_code(404);
        echo json_encode(['message' => 'Role not found']);
        return;
    }

    $role->delete();

    http_response_code(200);
    echo json_encode(['message' => 'Role deleted']);
}
