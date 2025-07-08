<?php
require_once __DIR__ . '/../db.php';

class EF {

    // ========== Établissements Financiers ==========

    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pf_EF");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pf_EF WHERE id_EF = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pf_EF (nom) VALUES (?)");
        $stmt->execute([$data->nom]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pf_EF SET nom = ? WHERE id_EF = ?");
        $stmt->execute([$data->nom, $id]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pf_EF WHERE id_EF = ?");
        $stmt->execute([$id]);
    }

    // ========== Fonds ==========

    public static function getAllFonds() {
        $db = getDB();
        $stmt = $db->query("
            SELECT f.id_fond, f.motif, f.montant, e.nom AS etablissement
            FROM pf_fond f
            JOIN pf_EF e ON f.id_EF = e.id_EF
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFondById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pf_fond WHERE id_fond = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createFond($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pf_fond (motif, montant, id_EF) VALUES (?, ?, ?)");
        $stmt->execute([$data->motif, $data->montant, $data->id_EF]);
        return $db->lastInsertId();
    }

    public static function updateFond($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pf_fond SET motif = ?, montant = ?, id_EF = ? WHERE id_fond = ?");
        $stmt->execute([$data->motif, $data->montant, $data->id_EF, $id]);
    }

    public static function deleteFond($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pf_fond WHERE id_fond = ?");
        $stmt->execute([$id]);
    }

    // Fonctions communes
    public static function sumFond($id_EF) {
        $db = getDB();
        $stmt = $db->prepare("SELECT SUM(montant) as Total FROM pf_fond WHERE id_EF = ?");
        $stmt->execute([$id_EF]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function getPrets($idEF) {
        $db = getDB();
        $stmt = $this->$db->prepare("SELECT * FROM pf_pret WHERE id_EF = ?");
        $stmt->execute([$idEF]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getDetailsInteretsParMoisAnnee($id_EF, $mois, $annee) {
        $db = getDB();

        $stmt = $db->prepare("
            SELECT 
                r.id_remboursement,
                r.id_pret,
                p.montant AS montant_pret,
                r.montant_total,
                r.interet,
                r.capital,
                r.capital_restant,
                r.mois,
                r.annee,
                c.nom AS nom_client,
                tp.nom AS type_pret
            FROM pf_remboursement r
            JOIN pf_pret p ON r.id_pret = p.id_pret
            JOIN pf_client c ON p.id_client = c.id_client
            JOIN pf_type_pret tp ON p.id_type_pret = tp.id_type_pret
            WHERE p.id_EF = ?
            AND r.mois = ?
            AND r.annee = ?
            ORDER BY r.id_remboursement
        ");

        $stmt->execute([$id_EF, $mois, $annee]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSommeInteretsParMoisAnnee($id_EF, $mois, $annee) {
        $db = getDB();

        $stmt = $db->prepare("
            SELECT SUM(r.interet) AS total_interets
            FROM pf_remboursement r
            JOIN pf_pret p ON r.id_pret = p.id_pret
            WHERE p.id_EF = ?
            AND r.mois = ?
            AND r.annee = ?
        ");

        $stmt->execute([$id_EF, $mois, $annee]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total_interets'] ?: 0;
    }

    public static function getDetailsInteretsEntreDates($id_EF, $moisDebut, $anneeDebut, $moisFin, $anneeFin) {
        $resultats = [];

        $mois = $moisDebut;
        $annee = $anneeDebut;

        while ($annee < $anneeFin || ($annee == $anneeFin && $mois <= $moisFin)) {
            $détailsMois = self::getDetailsInteretsParMoisAnnee($id_EF, $mois, $annee);
            $resultats = array_merge($resultats, $détailsMois);

            $mois++;
            if ($mois > 12) {
                $mois = 1;
                $annee++;
            }
        }

        return $resultats;
    }

    public static function getSommeInteretsEntreDatesViaMois($id_EF, $moisDebut, $anneeDebut, $moisFin, $anneeFin) {
        $total = 0;

        $mois = $moisDebut;
        $annee = $anneeDebut;

        while ($annee < $anneeFin || ($annee == $anneeFin && $mois <= $moisFin)) {
            $total += self::getSommeInteretsParMoisAnnee($id_EF, $mois, $annee);

            $mois++;
            if ($mois > 12) {
                $mois = 1;
                $annee++;
            }
        }

        return $total;
    }
    
}
