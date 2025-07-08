<?php
require_once __DIR__ . '/../models/Pret.php';

class EFController {

    // ========= TYPE PRET ==========

    public static function getAllTypePret() {
        $prets = Pret::getAllTypePrets();
        Flight::json($prets);
    }

    public static function getTypePretById($id) {
        $pret = Pret::getTypePretById($id);
        if ($pret) {
            Flight::json($pret);
        } else {
            Flight::halt(404, json_encode(['error' => 'Type pret non trouvé']));
        }
    }

    public static function createTypePret() {
        $data = Flight::request()->data;

        if (!isset($data->nom, $data->duree, $data->taux)) {
            Flight::halt(400, json_encode(['error' => 'Nom, durée et taux requis']));
        }

        $id = Pret::createTypePret($data);
        Flight::json(['message' => 'Type pret ajouté', 'id' => $id]);
    }

    public static function updateTypePret($id) {
        $data = (object) [];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            parse_str(file_get_contents("php://input"), $put_vars);
            $data = (object) $put_vars;
        } else {
            $data = Flight::request()->data;
        }

        if (!Pret::getTypePretById($id)) {
            Flight::halt(404, json_encode(['error' => 'Type pret non trouvé']));
        }

        if (!isset($data->nom, $data->duree, $data->taux)) {
            Flight::halt(400, json_encode(['error' => 'Nom, durée et taux requis']));
        }

        Pret::updateTypePret($id, $data);
        Flight::json(['message' => 'Type pret modifié']);
    }

    public static function deleteTypePret($id) {
        if (!Pret::getTypePretById($id)) {
            Flight::halt(404, json_encode(['error' => 'Type pret non trouvé']));
        }

        Pret::deleteTypePret($id);
        Flight::json(['message' => 'Type pret supprimé']);
    }

    // ========= PRET ========
    public static function getAllPrets() {
        $prets = Pret::getAll();
        Flight::json($prets);
    }

    public static function getAllDemande() {
        $prets = Pret::getAllDemande();
        Flight::json($prets);
    }

    public static function getPretById($id) {
        $pret = Pret::getById($id);
        if ($pret) {
            Flight::json($pret);
        } else {
            Flight::halt(404, json_encode(['error' => 'Prêt non trouvé']));
        }
    }

    public static function createPret() {
        $data = Flight::request()->data;
        try {
            $id = Pret::create($data);
            Flight::json(['message' => 'Prêt créé (en attente)', 'id' => $id]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            Flight::halt(400, json_encode(['error' => $e->getMessage()]));
        }
    }

    public static function deletePret($id) {
        if (!Pret::getById($id)) {
            Flight::halt(404, json_encode(['error' => 'Prêt non trouvé']));
        }

        Pret::delete($id);
        Flight::json(['message' => 'Prêt supprimé']);
    }

    public static function validerPret($id_pret) {
        try {
            Pret::validerPret($id_pret);
            Flight::json(['message' => "Le prêt #$id_pret a été validé avec succès."]);
        } catch (Exception $e) {
            error_log("Erreur validation prêt : " . $e->getMessage());
            Flight::halt(400, json_encode(['error' => $e->getMessage()]));
        }
    }

    public static function simulerRemboursement() {
        $data = Flight::request()->data;
        
        if (!isset($data->montant, $data->taux, $data->duree, $data->date_pret)) {
            Flight::halt(400, json_encode(['error' => 'Montant, taux, durée et date requis']));
        }

        $assurance = isset($data->assurance) ? $data->assurance : 0;
        
        try {
            $remboursements = Pret::genererRemboursementsSimules(
                $data->montant,
                $data->taux,
                $data->duree,
                $assurance,
                $data->date_pret
            );
            Flight::json($remboursements);
        } catch (Exception $e) {
            Flight::halt(400, json_encode(['error' => $e->getMessage()]));
        }
    }

    
        public static function sauvegarderSimulation() {
            $data = Flight::request()->data;
            
            if (!isset($data->id_pret, $data->montant, $data->taux, $data->duree)) {
                Flight::halt(400, json_encode(['error' => 'Données incomplètes']));
            }
    
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO pret_simulation (id_pret, montant, taux, duree, assurance, details) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data->id_pret,
                $data->montant,
                $data->taux,
                $data->duree,
                $data->assurance ?? 0,
                json_encode($data->details ?? [])
            ]);
            
            Flight::json(['message' => 'Simulation sauvegardée', 'id' => $db->lastInsertId()]);
    }
    public static function getSimulations() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret_simulation ORDER BY date_simulation DESC");
        Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
