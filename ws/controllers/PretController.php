<?php
require_once __DIR__ . '/../models/Pret.php';

class PretController {
    
    public static function create() {
        $data = Flight::request()->data;

        // Validation des champs requis
        if (!isset($data->id_client) || !is_numeric($data->id_client)) {
            Flight::json(['error' => 'L\'ID du client est requis et doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->id_type_pret) || !is_numeric($data->id_type_pret)) {
            Flight::json(['error' => 'L\'ID du type de prêt est requis et doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->montant) || !is_numeric($data->montant) || $data->montant <= 0) {
            Flight::json(['error' => 'Le montant doit être un nombre positif.'], 400);
            return;
        }
        if (!isset($data->duree_mois) || !is_numeric($data->duree_mois) || $data->duree_mois <= 0) {
            Flight::json(['error' => 'La durée doit être un nombre positif.'], 400);
            return;
        }
        if (!isset($data->date_debut) || empty(trim($data->date_debut))) {
            Flight::json(['error' => 'La date de début est requise.'], 400);
            return;
        }

        // Validation du format de date
        $date_debut = DateTime::createFromFormat('Y-m-d', $data->date_debut);
        if (!$date_debut) {
            Flight::json(['error' => 'Format de date invalide. Utilisez YYYY-MM-DD.'], 400);
            return;
        }

        try {
            $result = Pret::createPret(
                (int)$data->id_client,
                (int)$data->id_type_pret,
                $data->montant,
                (int)$data->duree_mois,
                $data->date_debut
            );
            
            if ($result['success']) {
                Flight::json(['message' => $result['message']], 201);
            } else {
                Flight::json(['error' => $result['message']], 400);
            }
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    public static function getAllClients() {
        try {
            $clients = Pret::getAllClients();
            Flight::json($clients);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la récupération des clients: ' . $e->getMessage()], 500);
        }
    }

    public static function getFondsDisponibles() {
        try {
            $fonds = Pret::getFondsDisponibles();
            Flight::json($fonds);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la récupération des fonds: ' . $e->getMessage()], 500);
        }
    }
}