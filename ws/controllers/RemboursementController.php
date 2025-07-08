<?php
require_once __DIR__ . '/../models/Remboursement.php';

class RemboursementController {

    public static function getAll() {
        $remboursements = Remboursement::getAll();
        Flight::json($remboursements);
        error_log("Appel à getStatistiques");
        $remboursements = Remboursement::getAll();
        error_log(print_r($remboursements, true));
    }

    public static function getById($id) {
        $remboursement = Remboursement::getById($id);
        if ($remboursement) {
            Flight::json($remboursement);
        } else {
            Flight::halt(404, json_encode(['error' => 'Remboursement non trouvé']));
        }
    }

    public static function getByPretId($id_pret) {
        $remboursements = Remboursement::getByPretId($id_pret);
        Flight::json($remboursements);
    }

    public static function getStatistiques() {
        $stats = Remboursement::getStatistiques();
        Flight::json($stats);
    }

    public static function getRemboursementsParMois() {
        $remboursements = Remboursement::getRemboursementsParMois();
        Flight::json($remboursements);
    }


} 