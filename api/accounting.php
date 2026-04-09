<?php

session_start();

require_once 'DB.php';
require_once 'tools.php';
require_once 'filter.php';
require_once 'models/File.php';
require_once 'models/Accounting.php';

// TODO: Remove this line in production
ini_set('display_errors', 1);

header('Content-Type: application/json');

Tools::checkPermission('p_comptabilite');

$methode = $_SERVER['REQUEST_METHOD'];

switch ($methode) {
    case 'GET':                      # READ
        get_accounting();
        break;

    case 'POST':                     # CREATE
            create_accounting();
        break;
    case 'DELETE':                   # DELETE
            delete_accounting();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}


function get_accounting(): void
{
    if (isset($_GET['id'])) {
        // Si un ID est précisé, on renvoie en plus les infos de l'utilisateur qui a crée le fichier
        $id = $_GET['id'];

        $data = Accounting::getInstance($id);

        if ($data == null) {
            http_response_code(404);
            echo json_encode(["message" => "Accounting file not found"]);
            return;
        }
    } else {
        $data = Accounting::bulkFetch();
    }

    echo json_encode($data);
}


function create_accounting(): void
{
    // TODO : Récupérer l'ID de membre grace au token PHP

    if (!isset($_POST['date'], $_POST['nom'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameters"]);
        return;
    }

    $file = File::saveFile();

    if ($file == null) {
        http_response_code(400);
        echo json_encode(["message" => "Accounting file not created"]);
    } else {
        $date = filter::date($_POST['date']);
        $nom = filter::string($_POST['nom'], maxLenght: 100);
        $id_membre = filter::int($_SESSION['userid']);

        $compta = Accounting::create($date, $nom, $file, $id_membre);


        http_response_code(201);
        echo $compta;
    }
}

function delete_accounting(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameters"]);
        return;
    }

    $id = filter::int($_GET['id']);

    $compta = Accounting::getInstance($id);

    if ($compta == null) {
        http_response_code(404);
        echo json_encode(["message" => "Accounting file not found"]);
        return;
    }

    $compta->delete();
    http_response_code(200);
    echo json_encode(["message" => "Accounting file deleted"]);
}
