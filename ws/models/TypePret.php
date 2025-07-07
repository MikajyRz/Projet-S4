<?php
require_once __DIR__ . '/../db.php';

class TyPret {

    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM Pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function CreateTypePret($libelle, $taux_annuel, $duree_max_mois, $montant_min, $montant_max) {
        $db = getDB();
        
        if (!is_numeric($taux_annuel) || $taux_annuel < 0 || $taux_annuel > 100) {
            throw new InvalidArgumentException("Le taux d’intérêt doit être un nombre entre 0 et 100.");
        }
        
        if (!is_int($duree_max_mois) || $duree_max_mois <= 0) {
            throw new InvalidArgumentException("La durée maximale doit être un entier positif.");
        }
        
        if (!is_numeric($montant_min) || $montant_min < 0 || !is_numeric($montant_max) || $montant_max < 0 || $montant_min > $montant_max) {
            throw new InvalidArgumentException("Les montants minimal et maximal doivent être des nombres positifs avec montant_min ≤ montant_max.");
        }
        
        // Vérifier l'unicité du libelle
        $stmt = $db->prepare("SELECT COUNT(*) FROM TypePret WHERE libelle = ?");
        $stmt->execute([$libelle]);
        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException("Un type de prêt avec ce nom existe déjà.");
        }
        
        // Insertion dans la base de données
        $stmt = $db->prepare("INSERT INTO TypePret (libelle, taux_annuel, duree_max_mois, montant_min, montant_max) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$libelle, $taux_annuel, $duree_max_mois, $montant_min, $montant_max]);
    }
}