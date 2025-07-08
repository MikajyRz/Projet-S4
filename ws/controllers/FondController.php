<?php
require_once __DIR__ . '/../models/Fond.php';

class FondController {

    public static function getAll() {
        $fonds = Fond::getAll();
        Flight::json($fonds);
    }

    public static function getById($id) {
        $fond = Fond::getById($id);
        if ($fond) {
            Flight::json($fond);
        } else {
            Flight::halt(404, json_encode(['error' => 'Fond non trouvé']));
        }
    }

    public static function create() {
        $data = Flight::request()->data;

        if (!isset($data->motif, $data->montant)) {
            Flight::halt(400, json_encode(['error' => 'Motif et montant requis']));
        }

        $id = Fond::create($data);
        Flight::json(['message' => 'Fond ajouté', 'id' => $id]);
    }

    public static function update($id) {
        $data = (object) [];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            parse_str(file_get_contents("php://input"), $put_vars);
            $data = (object) $put_vars;
        } else {
            $data = Flight::request()->data;
        }

        if (!Fond::getById($id)) {
            Flight::halt(404, json_encode(['error' => 'Fond non trouvé']));
        }

        if (!isset($data->motif, $data->montant)) {
            Flight::halt(400, json_encode(['error' => 'Motif et montant requis']));
        }

        Fond::update($id, $data);
        Flight::json(['message' => 'Fond modifié']);
    }

    public static function delete($id) {
        if (!Fond::getById($id)) {
            Flight::halt(404, json_encode(['error' => 'Fond non trouvé']));
        }

        Fond::delete($id);
        Flight::json(['message' => 'Fond supprimé']);
    }

    public static function getTotalFonds() {
        $total = Fond::getTotalFonds();
        Flight::json(['total' => $total]);
    }

    public static function getFondsDisponibles() {
        $disponibles = Fond::getFondsDisponibles();
        Flight::json(['disponibles' => $disponibles]);
    }
} 