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

    // Méthode pour valider un prêt
    public static function validerPret() {
        $data = Flight::request()->data;

        // Validation des champs requis
        if (!isset($data->id_pret) || !is_numeric($data->id_pret)) {
            Flight::json(['error' => 'L\'ID du prêt est requis et doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->id_bancaire) || !is_numeric($data->id_bancaire)) {
            Flight::json(['error' => 'L\'ID du banquier est requis et doit être un nombre.'], 400);
            return;
        }

        $commentaire = isset($data->commentaire) ? $data->commentaire : '';

        try {
            $result = Pret::validerPret(
                (int)$data->id_pret,
                (int)$data->id_bancaire,
                $commentaire
            );
            
            if ($result['success']) {
                Flight::json(['message' => $result['message'], 'pret' => $result['pret']], 200);
            } else {
                Flight::json(['error' => $result['message']], 400);
            }
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    // Méthode pour refuser un prêt
    public static function refuserPret() {
        $data = Flight::request()->data;

        // Validation des champs requis
        if (!isset($data->id_pret) || !is_numeric($data->id_pret)) {
            Flight::json(['error' => 'L\'ID du prêt est requis et doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->id_bancaire) || !is_numeric($data->id_bancaire)) {
            Flight::json(['error' => 'L\'ID du banquier est requis et doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->motif_refus) || empty(trim($data->motif_refus))) {
            Flight::json(['error' => 'Le motif de refus est requis.'], 400);
            return;
        }

        $commentaire = isset($data->commentaire) ? $data->commentaire : '';

        try {
            $result = Pret::refuserPret(
                (int)$data->id_pret,
                (int)$data->id_bancaire,
                $data->motif_refus,
                $commentaire
            );
            
            if ($result['success']) {
                Flight::json(['message' => $result['message'], 'pret' => $result['pret']], 200);
            } else {
                Flight::json(['error' => $result['message']], 400);
            }
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    // Méthode pour récupérer les prêts en attente
    public static function getPretsEnAttente() {
        try {
            $prets = Pret::getPretsEnAttente();
            Flight::json($prets);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la récupération des prêts en attente: ' . $e->getMessage()], 500);
        }
    }

    // Méthode pour récupérer l'historique d'un prêt
    public static function getHistoriquePret($id_pret) {
        if (!is_numeric($id_pret)) {
            Flight::json(['error' => 'L\'ID du prêt doit être un nombre.'], 400);
            return;
        }

        try {
            $historique = Pret::getHistoriquePret((int)$id_pret);
            Flight::json($historique);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la récupération de l\'historique: ' . $e->getMessage()], 500);
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

    // Méthode pour récupérer tous les banquiers
    public static function getAllBanquiers() {
        try {
            $banquiers = Pret::getAllBanquiers();
            Flight::json($banquiers);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la récupération des banquiers: ' . $e->getMessage()], 500);
        }
    }
}