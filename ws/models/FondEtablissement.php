<?php
require_once __DIR__ . '/../db.php';

class FondEtablissement {
    public static function getFond() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM Fond_Etablissement LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function ajouterFond($montant) {
        $db = getDB();
        try {
            $db->beginTransaction();

            $stmt = $db->query("SELECT id_fond, montant_total FROM Fond_Etablissement LIMIT 1");
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $newMontant = $existing['montant_total'] + $montant;
                $stmt = $db->prepare("UPDATE Fond_Etablissement SET montant_total = ?, date_maj = CURDATE() WHERE id_fond = ?");
                $stmt->execute([$newMontant, $existing['id_fond']]);
                $id_fond = $existing['id_fond'];
            } else {
                $stmt = $db->prepare("INSERT INTO Fond_Etablissement (montant_total, date_maj) VALUES (?, CURDATE())");
                $stmt->execute([$montant]);
                $id_fond = $db->lastInsertId();
            }

            $stmt = $db->prepare("INSERT INTO transactions (id_fonds, id_type_transaction, id_pret, date_transaction) VALUES (?, ?, ?, CURDATE())");
            $stmt->execute([$id_fond, 1, NULL,]); // id_type_transaction = 1 (ajout de fonds), id_pret = NULL

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }
}