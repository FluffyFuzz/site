<?php

session_start();
use model\File;
use model\Grade;

require_once 'DB.php';
require_once 'tools.php';
require_once 'filter.php';
require_once 'models/Grade.php';
require_once 'filter.php';

// TODO: Remove this line in production
ini_set('display_errors', 1);

header('Content-Type: application/json');

Tools::checkPermission('p_grade');

$methode = $_SERVER['REQUEST_METHOD'];


switch ($methode) {
    case 'GET':                      # READ
        get_grades();
        break;
    case 'POST':                     # CREATE
        create_grade();
        break;
    case 'PUT':                      # UPDATE (données seulement)
        if (Tools::methodAccepted('application/json')) {
            update_grade();
        }
        break;
    case 'PATCH':                    # UPDATE (image seulement)
            update_image();
        break;
    case 'DELETE':                   # DELETE
        delete_grade();
        break;

    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}


function get_grades(): void
{
    if (isset($_GET['id'])) {
        $id = filter::int($_GET['id']);
        $grades = Grade::getInstance($id);

        if ($grades === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Grade not found']);
            return;
        }
    } else {
        $grades = Grade::bulkFetch();
    }

    echo json_encode($grades);
}

function create_grade(): void
{
    $grade = Grade::create("Nouveau grade", "Ceci est un nouveau grade", 10.99, null, 0);

    http_response_code(201);
    echo $grade;
}

function update_grade(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Please provide an id']);
        return;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($_GET['id'], $data['name'], $data['description'], $data['price'], $data['reduction'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Incomplete data']);
        return;
    }
    $id = filter::int($_GET['id']);
    $name = filter::string($data['name'], maxLenght: 100);
    $description = filter::string($data['description'], maxLenght: 500);
    $price = filter::float($data['price']);
    $reduction = filter::int($data['reduction']);

    $grade = Grade::getInstance($id);

    if ($grade === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Grade not found']);
        return;
    }

    $grade->update($name, $description, $price, $reduction);

    http_response_code(200);
    echo $grade;
}


function update_image(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Please provide an id']);
        return;
    }

    $id = filter::int($_GET['id']);
    $grade = Grade::getInstance($id);

    if ($grade === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Grade not found']);
        return;
    }

    $image = File::saveImage();

    if (!$image) {
        http_response_code(400);
        echo json_encode(['error' => 'Image could not be processed']);
        return;
    }

    $grade->updateImage($image);

    echo json_encode($grade);
}

function delete_grade(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Please provide an id']);
        return;
    }

    $id = filter::int($_GET['id']);
    $grade = Grade::getInstance($id);

    if ($grade === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Grade not found']);
        return;
    }

    $grade->delete();

    http_response_code(200);
    echo json_encode(['message' => 'Grade deleted']);
}
