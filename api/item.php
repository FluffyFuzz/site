<?php

session_start();

require_once 'DB.php';
require_once 'tools.php';
require_once 'filter.php';
require_once 'models/Item.php';

header('Content-Type: application/json');

Tools::checkPermission('p_boutique');

$methode = $_SERVER['REQUEST_METHOD'];

switch ($methode) {
    case 'GET':                      # READ
        get_items();
        break;
    case 'POST':                     # CREATE
            create_item();
        break;
    case 'PUT':                      # UPDATE (données seulement)
        if (Tools::methodAccepted('application/json')) {
            update_item();
        }
        break;
    case 'PATCH':                    # UPDATE (image seulement)
            update_image();
        break;
    case 'DELETE':                   # DELETE
        delete_item();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}


function get_items(): void
{
    if (isset($_GET['id'])) {
        $id = filter::int($_GET['id']);
        $item = Item::getInstance($id);

        if (!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
            return;
        }
    } else {
        $item = Item::bulkFetch();
    }

    http_response_code(200);
    echo json_encode($item);
}

function create_item(): void
{
    $item = Item::create("Nouvel article", 1, 0, true, 1.99, null, "Non défini");

    http_response_code(201);
    echo $item;
}

function update_item(): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_GET['id'], $data['name'], $data['xp'], $data['stocks'], $data['reduction'], $data['price'], $data['categorie'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }

    $id = filter::int($_GET['id']);
    $name = filter::string($data['name'], maxLenght: 100);
    $xp = filter::int($data['xp']);
    $stocks = filter::int($data['stocks'], min: -100000);
    $reduction = filter::bool($data['reduction']);
    $price = filter::float($data['price']);
    $categorie = filter::string($data['categorie'], maxLenght: 100);

    $item = Item::getInstance($id);

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
        return;
    }

    $item->update($name, $xp, $stocks, $reduction, $price, $categorie);

    echo $item;
}

function update_image(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }

    $item = Item::getInstance(filter::int($_GET['id']));

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
        return;
    }

    $imageName = File::saveImage();

    if (!$imageName) {
        http_response_code(400);
        echo json_encode(['error' => 'Image could not be processed']);
        return;
    }


    $item->getImage()?->deleteFile();

    $item->updateImage($imageName);

    echo $item;
}


function delete_item(): void
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }

    $id = filter::int($_GET['id']);
    $item = Item::getInstance($id);

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
        return;
    }

    $item->delete();

    http_response_code(200);
    echo json_encode(['message' => 'Item deleted']);
}
