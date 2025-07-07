<?php
require_once __DIR__ . '/../db.php';

class TypePret {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM Pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function CreateTypePret($libelle, $taux_annuel, $duree_max_mois, $montant_min, $montant_max) {
        $db = getDB();

        // Validation des inputs
        if (empty($libelle)) {
            throw new InvalidArgumentException("Le nom du prêt ne peut pas être vide.");
        }
        if (!is_numeric($taux_annuel) || $taux_annuel < 0 || $taux_annuel > 100) {
            throw new InvalidArgumentException("Le taux d’intérêt doit être un nombre entre 0 et 100.");
        }
        if (!ctype_digit((string)$duree_max_mois) || (int)$duree_max_mois <= 0) {
            throw new InvalidArgumentException("La durée maximale doit être un entier positif.");
        }
        if (!is_numeric($montant_min) || $montant_min < 0 || !is_numeric($montant_max) || $montant_max < 0 || $montant_min > $montant_max) {
            throw new InvalidArgumentException("Les montants minimal et maximal doivent être des nombres positifs avec montant_min ≤ montant_max.");
        }

        try {
            // Vérifier l'unicité du libelle
            $stmt = $db->prepare("SELECT COUNT(*) FROM TypePret WHERE libelle = ?");
            $stmt->execute([$libelle]);
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException("Un type de prêt avec ce nom existe déjà.");
            }

            // Insertion dans la base de données
            $stmt = $db->prepare("INSERT INTO TypePret (libelle, taux_annuel, duree_max_mois, montant_min, montant_max) VALUES (?, ?, ?, ?, ?)");
            $success = $stmt->execute([
                $libelle,
                $taux_annuel,
                (int)$duree_max_mois,
                $montant_min,
                $montant_max
            ]);
            if (!$success) {
                throw new Exception("Échec de l'insertion du type de prêt.");
            }
            return true;
        } catch (PDOException $e) {
            error_log("PDOException in CreateTypePret: " . $e->getMessage());
            throw new Exception("Erreur lors de la création du type de prêt: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Exception in CreateTypePret: " . $e->getMessage());
            throw $e;
        }
    }
}