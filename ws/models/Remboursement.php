<?php
require_once __DIR__ . '/../db.php';

class Remboursement {

    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("
            SELECT 
                r.*,
                p.montant AS montant_pret,
                c.nom AS client_nom,
                tp.nom AS type_pret_nom
            FROM pret_remboursement r
            LEFT JOIN pret_pret p ON p.id_pret = r.id_pret
            LEFT JOIN pret_client c ON c.id_client = p.id_client
            LEFT JOIN pret_type_pret tp ON tp.id_type_pret = p.id_type_pret
            ORDER BY r.annee DESC, r.mois DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByPretId($id_pret) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT 
                r.*,
                p.montant AS montant_pret,
                c.nom AS client_nom,
                tp.nom AS type_pret_nom
            FROM pret_remboursement r
            LEFT JOIN pret_pret p ON p.id_pret = r.id_pret
            LEFT JOIN pret_client c ON c.id_client = p.id_client
            LEFT JOIN pret_type_pret tp ON tp.id_type_pret = p.id_type_pret
            WHERE r.id_pret = ?
            ORDER BY r.annee ASC, r.mois ASC
        ");
        $stmt->execute([$id_pret]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT 
                r.*,
                p.montant AS montant_pret,
                c.nom AS client_nom,
                tp.nom AS type_pret_nom
            FROM pret_remboursement r
            LEFT JOIN pret_pret p ON p.id_pret = r.id_pret
            LEFT JOIN pret_client c ON c.id_client = p.id_client
            LEFT JOIN pret_type_pret tp ON tp.id_type_pret = p.id_type_pret
            WHERE r.id_remboursement = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getStatistiques() {
        $db = getDB();
        
        // Total des remboursements
        $stmtTotal = $db->query("SELECT SUM(montant_total) FROM pret_remboursement");
        $totalRemboursements = $stmtTotal->fetchColumn() ?: 0;
        
        // Total des intérêts
        $stmtInterets = $db->query("SELECT SUM(interet) FROM pret_remboursement");
        $totalInterets = $stmtInterets->fetchColumn() ?: 0;
        
        // Total des assurances
        $stmtAssurance = $db->query("SELECT SUM(assurance) FROM pret_remboursement");
        $totalAssurance = $stmtAssurance->fetchColumn() ?: 0;
        
        // Nombre de remboursements
        $stmtCount = $db->query("SELECT COUNT(*) FROM pret_remboursement");
        $nombreRemboursements = $stmtCount->fetchColumn() ?: 0;
        
        return [
            'total_remboursements' => $totalRemboursements,
            'total_interets' => $totalInterets,
            'total_assurance' => $totalAssurance,
            'nombre_remboursements' => $nombreRemboursements
        ];
    }

    public static function getRemboursementsParMois() {
        $db = getDB();
        $stmt = $db->query("
            SELECT 
                annee,
                mois,
                SUM(montant_total) as total_mensuel,
                SUM(interet) as total_interets,
                SUM(assurance) as total_assurance,
                COUNT(*) as nombre_remboursements
            FROM pret_remboursement
            GROUP BY annee, mois
            ORDER BY annee DESC, mois DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 