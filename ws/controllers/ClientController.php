<?php
require_once __DIR__ . '/../models/Client.php';

class ClientController {
    
    public static function getAll() {
        $clients = Client::getAll();
        Flight::json($clients);
    }

    public static function getById($id) {
        $client = Client::getById($id);
        if ($client) {
            Flight::json($client);
        } else {
            Flight::halt(404, json_encode(['error' => 'Client non trouvé']));
        }
    }

    public static function create() {
        $data = Flight::request()->data;
        if (!isset($data->nom, $data->email)) {
            Flight::halt(400, json_encode(['error' => 'Nom et email requis']));
        }

        $id = Client::create($data);
        Flight::json(['message' => 'Client ajouté', 'id' => $id]);
    }

    public static function update($id) {
        $data = (object)[];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            parse_str(file_get_contents("php://input"), $put_vars);
            $data = (object) $put_vars;
        } else {
            $data = Flight::request()->data;
        }

        if (!Client::getById($id)) {
            Flight::halt(404, json_encode(['error' => 'Client non trouvé']));
        }

        if (!isset($data->nom, $data->email)) {
            Flight::halt(400, json_encode(['error' => 'Nom et email requis']));
        }

        Client::update($id, $data);
        Flight::json(['message' => 'Client modifié']);
    }

    public static function delete($id) {
        if (!Client::getById($id)) {
            Flight::halt(404, json_encode(['error' => 'Client non trouvé']));
        }

        Client::delete($id);
        Flight::json(['message' => 'Client supprimé']);
    }
}
