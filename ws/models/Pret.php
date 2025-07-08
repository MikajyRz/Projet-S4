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
                FROM Pret 
                WHERE id_client = ? 
                AND id_statut_pret IN (1, 3) -- 1 = En attente, 3 = Refusé
            ");
            $stmt->execute([$id_client]);
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException("Le client a déjà une demande de prêt en attente ou refusée récemment.");
            }
            
            // 7. Calculer le taux mensuel
            $taux_mensuel = $typePret['taux_annuel'] / 12;
            
            // 8. Créer le prêt avec statut "En attente de validation" (id_statut_pret = 1)
            $stmt = $db->prepare("
                INSERT INTO Pret (id_client, id_type_pret, id_statut_pret, date_debut, duree_mois, montant, taux_mensuel) 
                VALUES (?, ?, 1, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_client, $id_type_pret, $date_debut, $duree_mois, $montant, $taux_mensuel]);
            
            $id_pret = $db->lastInsertId();
            
            // 9. Insertion dans HistoriqueStatutPret
            $stmt = $db->prepare("
                INSERT INTO HistoriqueStatutPret (id_pret, id_statut_pret, commentaire) 
                VALUES (?, 1, 'Demande de prêt soumise par le client')
            ");
            $stmt->execute([$id_pret]);
            
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
    
    // Méthode pour valider un prêt
    public static function validerPret($id_pret, $id_bancaire, $commentaire = '') {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            // 1. Vérifier que le prêt existe et est en attente
            $stmt = $db->prepare("
                SELECT p.*, c.nom as client_nom, tp.libelle as type_pret_libelle
                FROM Pret p
                JOIN Clients c ON p.id_client = c.id_client
                JOIN TypePret tp ON p.id_type_pret = tp.id_type_pret
                WHERE p.id_pret = ? AND p.id_statut_pret = 1
            ");
            $stmt->execute([$id_pret]);
            $pret = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pret) {
                throw new InvalidArgumentException("Prêt non trouvé ou déjà traité.");
            }
            
            // 2. Vérifier que le banquier existe
            $stmt = $db->prepare("SELECT COUNT(*) FROM Banquaire WHERE id_bancaire = ?");
            $stmt->execute([$id_bancaire]);
            if ($stmt->fetchColumn() == 0) {
                throw new InvalidArgumentException("Banquier non trouvé.");
            }
            
            // 3. Vérifier les fonds disponibles
            $stmt = $db->prepare("SELECT montant_total FROM Fond_Etablissement ORDER BY date_maj DESC LIMIT 1");
            $stmt->execute();
            $fonds = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fonds || $fonds['montant_total'] < $pret['montant']) {
                throw new InvalidArgumentException("Fonds insuffisants pour valider ce prêt.");
            }
            
            // 4. Update du statut du prêt vers "Validé" (id_statut_pret = 2)
            $stmt = $db->prepare("
                UPDATE Pret 
                SET id_statut_pret = 2,
                    date_validation = CURDATE(),
                    id_bancaire_validateur = ?
                WHERE id_pret = ?
            ");
            $stmt->execute([$id_bancaire, $id_pret]);
            
            // 5. Insertion dans HistoriqueStatutPret
            $stmt = $db->prepare("
                INSERT INTO HistoriqueStatutPret (id_pret, id_statut_pret, id_bancaire, commentaire) 
                VALUES (?, 2, ?, ?)
            ");
            $stmt->execute([$id_pret, $id_bancaire, $commentaire ?: 'Prêt validé par le banquier']);
            
            // 6. Créer la transaction de déblocage
            $stmt = $db->prepare("
                INSERT INTO transactions (id_fonds, id_type_transaction, id_pret, date_transaction, montant) 
                VALUES (1, 1, ?, CURDATE(), ?)
            ");
            $stmt->execute([$id_pret, $pret['montant']]);
            
            // 7. Mettre à jour les fonds
            $nouveauMontant = $fonds['montant_total'] - $pret['montant'];
            $stmt = $db->prepare("
                UPDATE Fond_Etablissement 
                SET montant_total = ?, date_maj = CURDATE()
                WHERE id_fond = 1
            ");
            $stmt->execute([$nouveauMontant]);
            
            // 8. Passer le prêt en statut "En cours" (id_statut_pret = 4)
            $stmt = $db->prepare("
                UPDATE Pret 
                SET id_statut_pret = 4
                WHERE id_pret = ?
            ");
            $stmt->execute([$id_pret]);
            
            // 9. Insertion du statut "En cours" dans l'historique
            $stmt = $db->prepare("
                INSERT INTO HistoriqueStatutPret (id_pret, id_statut_pret, id_bancaire, commentaire) 
                VALUES (?, 4, ?, 'Prêt débloqué et en cours de remboursement')
            ");
            $stmt->execute([$id_pret, $id_bancaire]);
            
            $db->commit();
            
            return [
                'success' => true,
                'message' => 'Prêt validé et débloqué avec succès.',
                'pret' => $pret
            ];
            
        } catch (InvalidArgumentException $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur lors de la validation du prêt: " . $e->getMessage());
            throw new Exception("Erreur lors de la validation du prêt: " . $e->getMessage());
        }
    }
    
    // Méthode pour refuser un prêt
    public static function refuserPret($id_pret, $id_bancaire, $motif_refus, $commentaire = '') {
        $db = getDB();
        
        try {
            $db->beginTransaction();
            
            // 1. Vérifier que le prêt existe et est en attente
            $stmt = $db->prepare("
                SELECT p.*, c.nom as client_nom
                FROM Pret p
                JOIN Clients c ON p.id_client = c.id_client
                WHERE p.id_pret = ? AND p.id_statut_pret = 1
            ");
            $stmt->execute([$id_pret]);
            $pret = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pret) {
                throw new InvalidArgumentException("Prêt non trouvé ou déjà traité.");
            }
            
            // 2. Vérifier que le banquier existe
            $stmt = $db->prepare("SELECT COUNT(*) FROM Banquaire WHERE id_bancaire = ?");
            $stmt->execute([$id_bancaire]);
            if ($stmt->fetchColumn() == 0) {
                throw new InvalidArgumentException("Banquier non trouvé.");
            }
            
            // 3. Update du statut du prêt vers "Refusé" (id_statut_pret = 3)
            $stmt = $db->prepare("
                UPDATE Pret 
                SET id_statut_pret = 3,
                    motif_refus = ?,
                    id_bancaire_validateur = ?
                WHERE id_pret = ?
            ");
            $stmt->execute([$motif_refus, $id_bancaire, $id_pret]);
            
            // 4. Insertion dans HistoriqueStatutPret
            $stmt = $db->prepare("
                INSERT INTO HistoriqueStatutPret (id_pret, id_statut_pret, id_bancaire, commentaire) 
                VALUES (?, 3, ?, ?)
            ");
            $stmt->execute([$id_pret, $id_bancaire, $commentaire ?: 'Prêt refusé par le banquier']);
            
            $db->commit();
            
            return [
                'success' => true,
                'message' => 'Prêt refusé avec succès.',
                'pret' => $pret
            ];
            
        } catch (InvalidArgumentException $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur lors du refus du prêt: " . $e->getMessage());
            throw new Exception("Erreur lors du refus du prêt: " . $e->getMessage());
        }
    }
    
    // Méthode pour récupérer les prêts en attente de validation
    public static function getPretsEnAttente() {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, c.nom as client_nom, c.email as client_email, c.revenu_mensuel,
                   tp.libelle as type_pret_libelle, tp.taux_annuel, tp.montant_min, tp.montant_max,
                   p.date_demande
            FROM Pret p
            JOIN Clients c ON p.id_client = c.id_client
            JOIN TypePret tp ON p.id_type_pret = tp.id_type_pret
            WHERE p.id_statut_pret = 1
            ORDER BY p.date_demande ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Méthode pour récupérer l'historique d'un prêt
    public static function getHistoriquePret($id_pret) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT hsp.*, sp.libelle as statut_libelle, b.nom as banquier_nom
            FROM HistoriqueStatutPret hsp
            JOIN StatutPret sp ON hsp.id_statut_pret = sp.id_statut_pret
            LEFT JOIN Banquaire b ON hsp.id_bancaire = b.id_bancaire
            WHERE hsp.id_pret = ?
            ORDER BY hsp.date_statut ASC
        ");
        $stmt->execute([$id_pret]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            JOIN StatutPret sp ON p.id_statut_pret = sp.id_statut_pret
            ORDER BY p.date_debut DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Méthode pour récupérer tous les banquiers
    public static function getAllBanquiers() {
        $db = getDB();
        $stmt = $db->query("SELECT id_bancaire, nom, email FROM Banquaire ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}