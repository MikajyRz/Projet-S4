<?php
require_once __DIR__ . '/../db.php';

class Pret
{

    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT 
                p.id_pret,
                p.montant,
                p.id_client,
                c.nom AS client_nom,
                p.id_type_pret,
                tp.nom AS type_pret_nom,
                tp.taux,
                tp.duree,
                tp.assurance,
                p.date_pret,
                p.statuts
            FROM pret_pret p
            LEFT JOIN pret_client c ON c.id_client = p.id_client
            LEFT JOIN pret_type_pret tp ON tp.id_type_pret = p.id_type_pret
            WHERE p.statuts = 'valide'
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllDemande()
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT 
                p.id_pret,
                p.montant,
                p.id_client,
                c.nom AS client_nom,
                p.id_type_pret,
                tp.nom AS type_pret_nom,
                tp.taux,
                tp.duree,
                tp.assurance,
                p.date_pret,
                p.statuts
            FROM pret_pret p
            LEFT JOIN pret_client c ON c.id_client = p.id_client
            LEFT JOIN pret_type_pret tp ON tp.id_type_pret = p.id_type_pret
            WHERE p.statuts = 'en attente'
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT 
                p.id_pret,
                p.montant,
                p.id_client,
                c.nom AS client_nom,
                p.id_type_pret,
                tp.nom AS type_pret_nom,
                tp.taux,
                tp.duree,
                tp.assurance,
                p.date_pret,
                p.statuts
            FROM pret_pret p
            LEFT JOIN pret_client c ON c.id_client = p.id_client
            LEFT JOIN pret_type_pret tp ON tp.id_type_pret = p.id_type_pret
            WHERE p.id_pret = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function calculerMensualiteAmortissement($montant, $taux_annuel, $duree_mois, $assurance_pourcent = 0)
    {
        $taux_mensuel = $taux_annuel / 12 / 100;
        if ($taux_mensuel == 0) {
            $mensualite_base = round($montant / $duree_mois, 2);
        } else {
            $mensualite_base = ($montant * $taux_mensuel) / (1 - pow(1 + $taux_mensuel, -$duree_mois));
            $mensualite_base = round($mensualite_base, 2);
        }
        
        // Ajouter l'assurance
        $assurance_mensuelle = round(($montant * $assurance_pourcent / 100) / 12, 2);
        return $mensualite_base + $assurance_mensuelle;
    }

    public static function getFondsDisponibles()
    {
        $db = getDB();

        // Total des fonds déposés
        $stmtFond = $db->query("SELECT SUM(montant) FROM pret_fond");
        $totalFonds = $stmtFond->fetchColumn() ?: 0;

        // Total des prêts validés
        $stmtPrets = $db->query("SELECT SUM(montant) FROM pret_pret WHERE statuts = 'valide'");
        $totalPrets = $stmtPrets->fetchColumn() ?: 0;

        // Fonds réellement disponibles
        return $totalFonds - $totalPrets;
    }

    public static function create($data)
    {
        $db = getDB();

        error_log(json_encode($data)); // Ajoute ce log

        // Vérifie si le montant peut être couvert
        $fondsDisponibles = self::getFondsDisponibles();
        if ($fondsDisponibles < $data->montant) {
            throw new Exception("Fonds insuffisants. Disponibles : $fondsDisponibles Ar");
        }

        // Insertion du prêt (en attente)
        $stmt = $db->prepare("INSERT INTO pret_pret(montant, id_client, id_type_pret, date_pret, statuts) VALUES (?, ?, ?, ?, ?)");
        $datePret = $data->date_pret;
        $stmt->execute([
            $data->montant,
            $data->id_client,
            $data->id_type_pret,
            $datePret,
            'en attente'
        ]);

        return $db->lastInsertId();
    }

    public static function genererRemboursementsSimules($montant, $taux_annuel, $duree, $assurance_pourcent, $datePret){
        $taux_mensuel = $taux_annuel / 12 / 100;
        $mensualite = self::calculerMensualiteAmortissement($montant, $taux_annuel, $duree, $assurance_pourcent);
        $assurance_mensuelle = round(($montant * $assurance_pourcent / 100) / 12, 2);
        
        $reste = $montant;
        $remboursements = [];

        for ($i = 1; $i <= $duree; $i++) {
            $interet = round($reste * $taux_mensuel, 2);

            if ($i == $duree) {
                $capital = $reste;
                $mensualite_finale = round($capital + $interet + $assurance_mensuelle, 2);
                $reste = 0;
            } else {
                $capital = round($mensualite - $interet - $assurance_mensuelle, 2);
                $reste = round($reste - $capital, 2);
            }

            $date_remb = date('Y-m-d', strtotime("+$i month", strtotime($datePret)));
            $mois = (int)date('m', strtotime($date_remb));
            $annee = (int)date('Y', strtotime($date_remb));

            $remboursements[] = [
                'mois' => $mois,
                'annee' => $annee,
                'montant_total' => $i == $duree ? $mensualite_finale : $mensualite,
                'interet' => $interet,
                'capital' => $capital,
                'capital_restant' => $reste,
                'assurance' => $assurance_mensuelle
            ];
        }

        return $remboursements;
    }

    public static function insererRemboursements($id_pret){
        try{
            $db = getDB();

            // 1. Récupérer les infos du prêt
            $stmt = $db->prepare("SELECT * FROM pret_pret WHERE id_pret = ?");
            $stmt->execute([$id_pret]);
            $pret = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pret) {
                throw new Exception("Prêt introuvable.");
            }

            // 2. Récupérer taux et durée
            $stmt2 = $db->prepare("SELECT taux, duree, assurance FROM pret_type_pret WHERE id_type_pret = ?");
            $stmt2->execute([$pret['id_type_pret']]);
            $type = $stmt2->fetch(PDO::FETCH_ASSOC);

            if (!$type) {
                throw new Exception("Type de prêt introuvable.");
            }

            $taux_annuel = $type['taux'];
            $duree = $type['duree'];
            $assurance_pourcent = $type['assurance'] ?: 0;
            $taux_mensuel = $taux_annuel / 12 / 100;
            $mensualite = self::calculerMensualiteAmortissement($pret['montant'], $taux_annuel, $duree, $assurance_pourcent);

            // 3. Générer les remboursements
            $reste = $pret['montant'];
            $datePret = $pret['date_pret'];
            $assurance_mensuelle = round(($pret['montant'] * $assurance_pourcent / 100) / 12, 2);

            for ($i = 1; $i <= $duree; $i++) {
                $interet = round($reste * $taux_mensuel, 2);

                if ($i == $duree) {
                    $capital = $reste;
                    $mensualite_finale = round($capital + $interet + $assurance_mensuelle, 2);
                    $reste = 0;
                } else {
                    $capital = round($mensualite - $interet - $assurance_mensuelle, 2);
                    $reste = round($reste - $capital, 2);
                }

                $date_remb = date('Y-m-d', strtotime("+$i month", strtotime($datePret)));
                $mois = (int)date('m', strtotime($date_remb));
                $annee = (int)date('Y', strtotime($date_remb));

                $stmt3 = $db->prepare("INSERT INTO pret_remboursement (id_pret, montant_total, interet, capital, capital_restant, mois, annee, assurance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt3->execute([$id_pret, $i == $duree ? $mensualite_finale : $mensualite, $interet, $capital, $reste, $mois, $annee, $assurance_mensuelle]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de l'insertion des remboursements : " . $e->getMessage());
            throw $e;
        }
    }

    public static function validerPret($id_pret)
    {
        try{
            $db = getDB();

            // 1. Récupérer les infos du prêt
            $stmt = $db->prepare("SELECT * FROM pret_pret WHERE id_pret = ?");
            $stmt->execute([$id_pret]);
            $pret = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pret) {
                throw new Exception("Prêt introuvable.");
            }

            if ($pret['statuts'] !== 'en attente') {
                throw new Exception("Le prêt est déjà validé.");
            }

            // 2. Insérer les remboursements
            self::insererRemboursements($id_pret);

            // 3. Mettre à jour le statut du prêt
            $stmtUpdate = $db->prepare("UPDATE pret_pret SET statuts = 'valide' WHERE id_pret = ?");
            $stmtUpdate->execute([$id_pret]);

            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la validation du prêt : " . $e->getMessage());
            throw $e;
        }
    }

    public static function delete($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret_pret WHERE id_pret = ?");
        $stmt->execute([$id]);
    }

    public static function getAllTypePrets()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret_type_pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTypePretById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pret_type_pret WHERE id_type_pret = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createTypePret($data)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret_type_pret (nom, taux, duree, assurance) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data->nom, $data->taux, $data->duree, $data->assurance ?: 0]);
        return $db->lastInsertId();
    }

    public static function updateTypePret($id, $data)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret_type_pret SET nom = ?, taux = ?, duree = ?, assurance = ? WHERE id_type_pret = ?");
        $stmt->execute([$data->nom, $data->taux, $data->duree, $data->assurance ?: 0, $id]);
    }

    public static function deleteTypePret($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret_type_pret WHERE id_type_pret = ?");
        $stmt->execute([$id]);
    }
}
