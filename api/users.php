<?php

session_start();
use model\File;
use model\Member;

require_once 'models/Member.php';
require_once 'models/File.php';
require_once 'filter.php';

require_once 'DB.php';
require_once 'tools.php';

header('Content-Type: application/json');


tools::checkPermission('p_utilisateur');


$methode = $_SERVER['REQUEST_METHOD'];


# On accepte le format multipart/form-data UNIQUEMENT sur les requetes POST et PATCH
# Sinon, il faudrait coder un parser de multipart/form-data
switch ($methode) {
    case 'GET':                      # READ
        get_users();
        break;
    case 'POST':                     # CREATE
        create_user();
        break;
    case 'PUT':                      # UPDATE (données seulement)
        if (tools::methodAccepted('application/json')) {
            update_user();
        }
        break;
    case 'PATCH':                    # UPDATE (image seulement)
        update_image();
        break;
    case 'DELETE':                   # DELETE
        delete_user();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}

function get_users(): void
{
    if (isset($_GET['id'])) {
        // Si un ID est précisé, on renvoie les infos de l'utilisateur correspondant avec ses rôles
        $id = filter::int($_GET['id']);

        $data = Member::getInstance($id);

        if ($data) {
            $data = $data->toJsonWithRoles();
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
            return;
        }
    } else {
        // Sinon, on renvoie la liste de tous les utilisateurs. On va juste préciser si ils ont des rôles ou non
        $data = Member::bulkFetch();
    }

    http_response_code(200);
    echo json_encode($data);
}

function create_user(): void
{
    $user = Member::create(
        "Nom",
        "Prenom",
        "prenom.nom@univ-lemans.fr",
        null,
        "21a"
    );

    http_response_code(201);
    echo json_encode($user->toJsonWithRoles());
}

function update_user(): void
{

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name'], $data['firstname'], $data['email'], $data['tp'], $data['xp'], $_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameters"]);
        return;
    }

    $id = filter::int($_GET['id']);
    $name = filter::string($data['name'], maxLenght: 100);
    $surname =  filter::string($data['firstname'], maxLenght: 100);
    $email = filter::email($data['email'], maxLenght: 100);
    $tp = filter::string($data['tp'], maxLenght: 3);
    $xp = filter::int($data['xp']);

    $user = Member::getInstance($id);

    if ($user) {
        $user->update($name, $surname, $email, $tp, $xp);

        http_response_code(200);
        echo json_encode($user->toJsonWithRoles());
    } else {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
    }
}


function update_image(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameters"]);
        return;
    }

    $id = filter::int($_GET['id']);

    $user = Member::getInstance($id);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
        return;
    }

    $newImage = File::saveImage();

    if (!$newImage) {
        http_response_code(415);
        echo json_encode(["message" => "Image could not be processed"]);
        return;
    }

    $deleteFile = File::getFile($user->toJson()['pp_membre']);
    $deleteFile?->deleteFile();

    $user->updateProfilePic($newImage);

    http_response_code(200);
    echo json_encode($user->toJsonWithRoles());
}


function delete_user(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameters"]);
        return;
    }

    $id = filter::int($_GET['id']);

    $user = Member::getInstance($id);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
        return;
    }

    $user->delete();

    http_response_code(200);
    echo json_encode(["message" => "User deleted"]);
}
