<?php
require_once __DIR__ . '/../models/EF.php';
require_once __DIR__ . '/../models/Pret.php';

class InteretController {
    
    public static function getSommeInteretsParMoisAnnee($id_EF, $mois, $annee) {
        $details = EF::getSommeInteretsParMoisAnnee($id_EF, $mois, $annee);
        Flight::json($details);
    }

    public static function getDetailsInteretsEntreDates($id_EF, $moisDebut, $anneeDebut, $moisFin, $anneeFin) {
        $details = EF::getDetailsInteretsEntreDates($id_EF, $moisDebut, $anneeDebut, $moisFin, $anneeFin);
        // Exemple :
        echo "ID_EF: " . $id_EF . "<br>";
        echo "Période: " . $moisDebut . "/" . $anneeDebut . " à " . $moisFin . "/" . $anneeFin;
        Flight::json($details);
    }

    public static function getSommeInteretsEntreDatesViaMois($id_EF, $moisDebut, $anneeDebut, $moisFin, $anneeFin) {
        $details = EF::getSommeInteretsEntreDatesViaMois($id_EF, $moisDebut, $anneeDebut, $moisFin, $anneeFin);
        Flight::json($details);
    }

    public static function simulerRemboursement($id_pret)
    {
        try {
            $db = getDB();

            // Récupération des infos du prêt et du type de prêt
            $stmt = $db->prepare("
                SELECT p.montant, p.date_pret, t.taux, t.duree, COALESCE(t.assurance, 0) AS assurance
                FROM pf_pret p
                JOIN pf_type_pret t ON p.id_type_pret = t.id_type_pret
                WHERE p.id_pret = ?
            ");
            $stmt->execute([$id_pret]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                Flight::halt(404, "Prêt ou type de prêt introuvable.");
            }

            // Appel de la fonction de simulation
            $simul = Pret::genererRemboursementsSimules(
                $data['montant'],
                $data['taux'],
                $data['duree'],
                $data['assurance'],
                $data['date_pret']
            );

            Flight::json($simul);

        } catch (Exception $e) {
            Flight::halt(500, "Erreur serveur : " . $e->getMessage());
        }
    }

    public static function validerRemboursement($id_pret)
    {
        try {
            Pret::insererRemboursements($id_pret);
            Flight::json(['message' => 'Prêt validé et remboursements enregistrés avec succès.']);
        } catch (Exception $e) {
            http_response_code(500);
            Flight::json(['error' => 'Erreur lors de la validation : ' . $e->getMessage()]);
        }
    }
    
}
