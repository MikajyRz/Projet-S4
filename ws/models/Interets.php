<?php
require_once __DIR__ . '/../db.php';

class Interets {
    
    /**
     * Calcule les intérêts gagnés par mois pour une période donnée
     */
    public static function getInteretsParMois($date_debut, $date_fin) {
        $db = getDB();
        
        try {
            // Requête pour calculer les intérêts par mois
            $stmt = $db->prepare("
                SELECT 
                    DATE_FORMAT(hp.mois, '%Y-%m') as periode,
                    DATE_FORMAT(hp.mois, '%M %Y') as mois_annee,
                    SUM(hp.montant_mensuelite * p.taux_mensuel / 100) as interets_mensuels,
                    COUNT(DISTINCT p.id_pret) as nombre_prets,
                    SUM(p.montant) as capital_total
                FROM HistoriquePret hp
                JOIN Pret p ON hp.id_pret = p.id_pret
                WHERE p.id_statut_pret = 4 -- Prêts en cours
                AND hp.mois BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(hp.mois, '%Y-%m')
                ORDER BY periode ASC
            ");
            
            $stmt->execute([$date_debut, $date_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur lors du calcul des intérêts: " . $e->getMessage());
            throw new Exception("Erreur lors du calcul des intérêts");
        }
    }
    
    /**
     * Calcule les intérêts totaux pour une période
     */
    public static function getInteretsTotaux($date_debut, $date_fin) {
        $db = getDB();
        
        try {
            $stmt = $db->prepare("
                SELECT 
                    SUM(hp.montant_mensuelite * p.taux_mensuel / 100) as interets_totaux,
                    COUNT(DISTINCT p.id_pret) as nombre_prets_actifs,
                    SUM(p.montant) as capital_total_actif
                FROM HistoriquePret hp
                JOIN Pret p ON hp.id_pret = p.id_pret
                WHERE p.id_statut_pret = 4 -- Prêts en cours
                AND hp.mois BETWEEN ? AND ?
            ");
            
            $stmt->execute([$date_debut, $date_fin]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur lors du calcul des intérêts totaux: " . $e->getMessage());
            throw new Exception("Erreur lors du calcul des intérêts totaux");
        }
    }
    
    /**
     * Calcule les intérêts par type de prêt
     */
    public static function getInteretsParTypePret($date_debut, $date_fin) {
        $db = getDB();
        
        try {
            $stmt = $db->prepare("
                SELECT 
                    tp.libelle as type_pret,
                    SUM(hp.montant_mensuelite * p.taux_mensuel / 100) as interets_mensuels,
                    COUNT(DISTINCT p.id_pret) as nombre_prets,
                    AVG(p.taux_mensuel) as taux_moyen
                FROM HistoriquePret hp
                JOIN Pret p ON hp.id_pret = p.id_pret
                JOIN TypePret tp ON p.id_type_pret = tp.id_type_pret
                WHERE p.id_statut_pret = 4 -- Prêts en cours
                AND hp.mois BETWEEN ? AND ?
                GROUP BY tp.id_type_pret, tp.libelle
                ORDER BY interets_mensuels DESC
            ");
            
            $stmt->execute([$date_debut, $date_fin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur lors du calcul des intérêts par type: " . $e->getMessage());
            throw new Exception("Erreur lors du calcul des intérêts par type");
        }
    }
    
    /**
     * Génère des données de test pour l'historique des prêts
     */
    public static function genererDonneesTest() {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            // Récupérer les prêts en cours
            $stmt = $db->query("
                SELECT id_pret, montant, taux_mensuel, date_debut, duree_mois 
                FROM Pret 
                WHERE id_statut_pret = 4
            ");
            $prets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($prets as $pret) {
                $date_debut = new DateTime($pret['date_debut']);
                $montant_mensuelite = ($pret['montant'] * $pret['taux_mensuel'] / 100) / (1 - pow(1 + $pret['taux_mensuel'] / 100, -$pret['duree_mois']));
                
                // Générer les mensualités pour chaque mois
                for ($i = 0; $i < $pret['duree_mois']; $i++) {
                    $mois = clone $date_debut;
                    $mois->add(new DateInterval('P' . $i . 'M'));
                    
                    // Vérifier si l'historique existe déjà
                    $stmt_check = $db->prepare("
                        SELECT COUNT(*) FROM HistoriquePret 
                        WHERE id_pret = ? AND mois = ?
                    ");
                    $stmt_check->execute([$pret['id_pret'], $mois->format('Y-m-d')]);
                    
                    if ($stmt_check->fetchColumn() == 0) {
                        $stmt_insert = $db->prepare("
                            INSERT INTO HistoriquePret (id_pret, mois, montant_mensuelite) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt_insert->execute([$pret['id_pret'], $mois->format('Y-m-d'), $montant_mensuelite]);
                    }
                }
            }
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur lors de la génération des données de test: " . $e->getMessage());
            throw new Exception("Erreur lors de la génération des données de test");
        }
    }
    
    /**
     * Récupère les statistiques générales
     */
    public static function getStatistiquesGenerales() {
        $db = getDB();
        
        try {
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total_prets,
                    SUM(CASE WHEN id_statut_pret = 4 THEN 1 ELSE 0 END) as prets_actifs,
                    SUM(CASE WHEN id_statut_pret = 4 THEN montant ELSE 0 END) as capital_actif,
                    AVG(CASE WHEN id_statut_pret = 4 THEN taux_mensuel ELSE NULL END) as taux_moyen_actif
                FROM Pret
            ");
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des statistiques");
        }
    }
} 