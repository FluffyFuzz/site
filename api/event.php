<?php
session_start();
use model\Event;
use model\File;

require_once 'DB.php';
require_once 'tools.php';
require_once 'filter.php';
require_once 'models/Event.php';

header('Content-Type: application/json');

tools::checkPermission('p_evenement');

$methode = $_SERVER['REQUEST_METHOD'];
$DB = new DB();

switch ($methode) {
    case 'GET':                      # READ
        get_events();
        break;
    case 'POST':                     # CREATE
        create_event();
        break;
    case 'PUT':                      # UPDATE (données seulement)
        if (tools::methodAccepted('application/json')) {
            update_event();
        }
        break;
    case 'PATCH':                    # UPDATE (image seulement)
            update_image();
        break;
    case 'DELETE':                   # DELETE
        delete_event();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}

function get_events() : void
{
    if (isset($_GET['id']))
    {
        $id = filter::int($_GET['id']);
        $events = Event::getInstance($id);

        if ($events == null) {
            http_response_code(404);
            echo json_encode(['error' => 'Event not found']);
            return;
        }
    }
    else
    {
        $events = Event::bulkFetch();
    }

    echo json_encode($events);
}

function create_event() : void
{
    $event = Event::create("Nouvel événement", "Description de l'événement", 0, -1, false, 0, "Lieu de l'événement", "2021-01-01");

    echo json_encode($event);
}

function update_event() : void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $event = Event::getInstance($_GET['id']);

    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        return;
    }

    $event->update(filter::string($data['nom'], maxLenght:100), filter::string($data['description'], maxLenght:1000),
                   filter::int($data['xp']), filter::int($data['places'], -100000), filter::bool($data['reductions']), filter::float($data['prix']),
                   filter::string($data['lieu'], maxLenght:50), filter::date($data['date']));
    echo json_encode($event);
}

function update_image() : void
{
    $event = Event::getInstance($_GET['id']);

    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        return;
    }

    $image = File::saveImage();

    if (!$image) {
        http_response_code(400);
        echo json_encode(['error' => 'Image could not be processed']);
        return;
    }

    $event->updateImage($image);
    echo json_encode($event);
}

function delete_event() : void
{
    $event = Event::getInstance($_GET['id']);

    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        return;
    }

    $event->delete();
    http_response_code(200);
    echo json_encode(['message' => 'Event deleted']);
}