<?php
require_once __DIR__ . '/../db.php';

class Pret {
    
    public static function createPret($id_client, $id_type_pret, $montant, $duree_mois, $date_debut) {
        $db = getDB();
        
        try {
            // Démarrer une transaction
            $db->beginTransaction();
            
            // 1. Vérifier que le client existe
            $stmt = $db->prepare("SELECT COUNT(*) FROM Clients WHERE id_client = ?");
            $stmt->execute([$id_client]);
            if ($stmt->fetchColumn() == 0) {
                throw new InvalidArgumentException("Le client spécifié n'existe pas.");
            }
            
            // 2. Vérifier que le type de prêt existe et récupérer ses détails
            $stmt = $db->prepare("SELECT * FROM TypePret WHERE id_type_pret = ?");
            $stmt->execute([$id_type_pret]);
            $typePret = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$typePret) {
                throw new InvalidArgumentException("Le type de prêt spécifié n'existe pas.");
            }
            
            // 3. Vérifier que le montant est dans les limites
            if ($montant < $typePret['montant_min'] || $montant > $typePret['montant_max']) {
                throw new InvalidArgumentException("Le montant demandé doit être entre " . $typePret['montant_min'] . "€ et " . $typePret['montant_max'] . "€ pour ce type de prêt.");
            }
            
            // 4. Vérifier que la durée ne dépasse pas la durée maximale
            if ($duree_mois > $typePret['duree_max_mois']) {
                throw new InvalidArgumentException("La durée demandée ne peut pas dépasser " . $typePret['duree_max_mois'] . " mois pour ce type de prêt.");
            }
            
            // 5. Vérifier les fonds disponibles
            $stmt = $db->prepare("SELECT montant_total FROM Fond_Etablissement ORDER BY date_maj DESC LIMIT 1");
            $stmt->execute();
            $fonds = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fonds || $fonds['montant_total'] < $montant) {
                throw new InvalidArgumentException("Fonds insuffisants dans l'établissement. Montant disponible: " . ($fonds ? $fonds['montant_total'] : 0) . "€");
            }
            
            // 6. Vérifier si le client n'a pas de prêts en attente ou refusés récents
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM Pret p 
                JOIN StatutPret sp ON p.id_pret = sp.id_pret 
                WHERE p.id_client = ? 
                AND sp.libelle IN ('en attente', 'refusé') 
                AND sp.date_statut = (
                    SELECT MAX(sp2.date_statut) 
                    FROM StatutPret sp2 
                    WHERE sp2.id_pret = p.id_pret
                )
            ");
            $stmt->execute([$id_client]);
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException("Le client a déjà une demande de prêt en attente ou refusée récemment.");
            }
            
            // 7. Calculer le taux mensuel
            $taux_mensuel = $typePret['taux_annuel'] / 12;
            
            // 8. Créer le prêt
            $stmt = $db->prepare("
                INSERT INTO Pret (id_client, id_type_pret, date_debut, duree_mois, montant, taux_mensuel) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_client, $id_type_pret, $date_debut, $duree_mois, $montant, $taux_mensuel]);
            
            $id_pret = $db->lastInsertId();
            
            // 9. Ajouter le statut "en attente"
            $stmt = $db->prepare("
                INSERT INTO StatutPret (libelle, id_pret, date_statut) 
                VALUES ('en attente', ?, CURDATE())
            ");
            $stmt->execute([$id_pret]);
            
            // 10. Mettre à jour les fonds (réserver le montant)
            $nouveauMontant = $fonds['montant_total'] - $montant;
            $stmt = $db->prepare("
                INSERT INTO Fond_Etablissement (montant_total, date_maj) 
                VALUES (?, CURDATE())
            ");
            $stmt->execute([$nouveauMontant]);
            
            // Valider la transaction
            $db->commit();
            
            return [
                'success' => true, 
                'message' => 'Demande de prêt créée avec succès. ID du prêt: ' . $id_pret,
                'id_pret' => $id_pret
            ];
            
        } catch (InvalidArgumentException $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur lors de la création du prêt: " . $e->getMessage());
            throw new Exception("Erreur lors de la création du prêt: " . $e->getMessage());
        }
    }
    
    public static function getAllClients() {
        $db = getDB();
        $stmt = $db->query("SELECT id_client, nom, email FROM Clients ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getFondsDisponibles() {
        $db = getDB();
        $stmt = $db->prepare("SELECT montant_total FROM Fond_Etablissement ORDER BY date_maj DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['montant_total'] : 0;
    }
    
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("
            SELECT p.*, c.nom as client_nom, tp.libelle as type_pret_libelle,
                   sp.libelle as statut_actuel
            FROM Pret p
            JOIN Clients c ON p.id_client = c.id_client
            JOIN TypePret tp ON p.id_type_pret = tp.id_type_pret
            LEFT JOIN StatutPret sp ON p.id_pret = sp.id_pret
            WHERE sp.date_statut = (
                SELECT MAX(sp2.date_statut) 
                FROM StatutPret sp2 
                WHERE sp2.id_pret = p.id_pret
            )
            ORDER BY p.date_debut DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}