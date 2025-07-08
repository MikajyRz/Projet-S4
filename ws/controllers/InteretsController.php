<?php
require_once __DIR__ . '/../models/Interets.php';

class InteretsController {
    
    /**
     * Récupère les intérêts par mois avec filtres
     */
    public static function getInteretsParMois() {
        $request = Flight::request();
        $date_debut = $request->query->date_debut ?? date('Y-m-01'); // Premier jour du mois actuel
        $date_fin = $request->query->date_fin ?? date('Y-m-t'); // Dernier jour du mois actuel
        
        // Validation des dates
        if (!self::validerDate($date_debut) || !self::validerDate($date_fin)) {
            Flight::json(['error' => 'Format de date invalide. Utilisez YYYY-MM-DD.'], 400);
            return;
        }
        
        try {
            $interets = Interets::getInteretsParMois($date_debut, $date_fin);
            Flight::json($interets);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors du calcul des intérêts: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupère les intérêts totaux pour une période
     */
    public static function getInteretsTotaux() {
        $request = Flight::request();
        $date_debut = $request->query->date_debut ?? date('Y-m-01');
        $date_fin = $request->query->date_fin ?? date('Y-m-t');
        
        if (!self::validerDate($date_debut) || !self::validerDate($date_fin)) {
            Flight::json(['error' => 'Format de date invalide. Utilisez YYYY-MM-DD.'], 400);
            return;
        }
        
        try {
            $totaux = Interets::getInteretsTotaux($date_debut, $date_fin);
            Flight::json($totaux);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors du calcul des totaux: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupère les intérêts par type de prêt
     */
    public static function getInteretsParType() {
        $request = Flight::request();
        $date_debut = $request->query->date_debut ?? date('Y-m-01');
        $date_fin = $request->query->date_fin ?? date('Y-m-t');
        
        if (!self::validerDate($date_debut) || !self::validerDate($date_fin)) {
            Flight::json(['error' => 'Format de date invalide. Utilisez YYYY-MM-DD.'], 400);
            return;
        }
        
        try {
            $interetsParType = Interets::getInteretsParTypePret($date_debut, $date_fin);
            Flight::json($interetsParType);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors du calcul par type: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Récupère les statistiques générales
     */
    public static function getStatistiques() {
        try {
            $stats = Interets::getStatistiquesGenerales();
            Flight::json($stats);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Génère des données de test pour l'historique
     */
    public static function genererDonneesTest() {
        try {
            $result = Interets::genererDonneesTest();
            Flight::json(['message' => 'Données de test générées avec succès', 'success' => true]);
        } catch (Exception $e) {
            Flight::json(['error' => 'Erreur lors de la génération: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Valide le format d'une date
     */
    private static function validerDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
} 