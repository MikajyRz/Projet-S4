<?php
require_once __DIR__ . '/../models/TypePret.php';

class TypePretController {

    public static function getAll() {
        $types = TypePret::getAll();
        Flight::json($types);
    }
    
    public static function create() {
        $data = Flight::request()->data;

        // Validation des champs requis
        if (!isset($data->libelle) || empty(trim($data->libelle))) {
            Flight::json(['error' => 'Le nom du prêt est requis et ne peut pas être vide.'], 400);
            return;
        }
        if (!isset($data->taux_annuel) || !is_numeric($data->taux_annuel)) {
            Flight::json(['error' => 'Le taux d’intérêt doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->duree_max_mois) || !ctype_digit((string)$data->duree_max_mois)) {
            Flight::json(['error' => 'La durée maximale doit être un entier.'], 400);
            return;
        }
        if (!isset($data->montant_min) || !is_numeric($data->montant_min)) {
            Flight::json(['error' => 'Le montant minimal doit être un nombre.'], 400);
            return;
        }
        if (!isset($data->montant_max) || !is_numeric($data->montant_max)) {
            Flight::json(['error' => 'Le montant maximal doit être un nombre.'], 400);
            return;
        }

        try {
            TypePret::CreateTypePret(
                $data->libelle,
                $data->taux_annuel,
                (int)$data->duree_max_mois,
                $data->montant_min,
                $data->montant_max
            );
            Flight::json(['message' => 'Type de prêt créé avec succès'], 201);
        } catch (InvalidArgumentException $e) {
            Flight::json(['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}