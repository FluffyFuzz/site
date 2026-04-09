<?php
session_start();
use model\File;
use model\News;
use model\Role;

require_once 'filter.php';
require_once 'models/News.php';
require_once 'DB.php';
require_once 'tools.php';
require_once 'models/File.php';

header('Content-Type: application/json');

tools::checkPermission('p_actualite');

$DB = new DB();

$methode = $_SERVER['REQUEST_METHOD'];

switch ($methode) {
    case 'GET':                      # READ
        get_news();
        break;
    case 'POST':                     # CREATE
        create_news();
        break;
    case 'PUT':                     # UPDATE (données)
        if (tools::methodAccepted('application/json')) {
            update_news();
        }
        break;

    case 'PATCH':                     # UPDATE (image)
            update_image();
        break;

    case 'DELETE':                   # DELETE
        delete_news();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}



function get_news() : void
{
    if (isset($_GET['id'])) {
        $id = filter::int($_GET['id']);
        $news = News::getInstance($id);

        if ($news == null) {
            http_response_code(404);
            echo json_encode(['error' => 'Role not found']);
            return;
        }
        echo $news;

    } else {
        $news = News::bulkFetch();
        echo json_encode($news);
    }
}

function create_news() : void
{
    $news = News::create("Nouvel article", "Description de l'article", "2021-01-01", $_SESSION['userid'], null);
    echo $news;
}

function update_news() : void
{
    $id = filter::int($_GET['id']);
    $news = News::getInstance($id);

    if ($news == null) {
        http_response_code(404);
        echo json_encode(['error' => 'Role not found']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $name = filter::string($data['name'], maxLenght: 100);
    $description = filter::string($data['description'], maxLenght: 1000);
    $date = filter::string($data['date']);
    $id_membre = filter::int($_SESSION['userid']);

    $news->update($name, $description, $date, $id_membre);

    echo $news;
}

function update_image() : void
{
    $id = filter::int($_GET['id']);
    $news = News::getInstance($id);

    if ($news == null) {
        http_response_code(404);
        echo json_encode(['error' => 'Role not found']);
        return;
    }

    $image = File::saveImage();

    if ($image == null) {
        http_response_code(400);
        echo json_encode(['error' => 'Image could not be processed']);
        return;
    }

    $news->updateImage($image);
    echo $news;
}


function delete_news() : void
{
    $id = filter::int($_GET['id']);
    $news = News::getInstance($id);

    if ($news == null) {
        http_response_code(404);
        echo json_encode(['error' => 'News not found']);
        return;
    }

    $news->delete();
    http_response_code(200);
    echo json_encode(['message' => 'News deleted']);
}

