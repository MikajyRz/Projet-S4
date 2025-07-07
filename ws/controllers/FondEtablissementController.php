<?php
require_once __DIR__ . '/../models/FondEtablissement.php';
require_once __DIR__ . '/../helpers/Utils.php';

class FondEtablissementController {
    public static function getFond() {
        $fond = FondEtablissement::getFond();
        Flight::json($fond);
    }

    public static function addFonds() {
        $data = Flight::request()->data;

        if (!isset($data->montant) || !is_numeric($data->montant) || $data->montant <=0) {
            Flight::json(['error' => 'Montant invalide. Veuillez entrer un nombre positif.'], 400);
            return;
        }

        try {
            FondEtablissement::ajouterFond($data->montant);
            Flight::json(['message' => 'Fonds ajoutés avec succès']);
        } catch (PDOException $e) {
            Flight::json(['error' => 'Erreur lors de l\'ajout des fonds : ' . $e->getMessage()], 500);
        }
    }


}