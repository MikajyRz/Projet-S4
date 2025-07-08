<?php
require_once __DIR__ . '/../db.php';

class Fond {

    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret_fond ORDER BY id_fond DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pret_fond WHERE id_fond = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret_fond (motif, montant) VALUES (?, ?)");
        $stmt->execute([$data->motif, $data->montant]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret_fond SET motif = ?, montant = ? WHERE id_fond = ?");
        $stmt->execute([$data->motif, $data->montant, $id]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret_fond WHERE id_fond = ?");
        $stmt->execute([$id]);
    }

    public static function getTotalFonds() {
        $db = getDB();
        $stmt = $db->query("SELECT SUM(montant) FROM pret_fond");
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getFondsDisponibles() {
        $db = getDB();
        
        // Total des fonds déposés
        $totalFonds = self::getTotalFonds();
        
        // Total des prêts validés
        $stmtPrets = $db->query("SELECT SUM(montant) FROM pret_pret WHERE statuts = 'valide'");
        $totalPrets = $stmtPrets->fetchColumn() ?: 0;
        
        return $totalFonds - $totalPrets;
    }
} 