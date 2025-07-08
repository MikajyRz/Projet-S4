<?php
require_once __DIR__ . '/../db.php';

class AuthController {
    public static function login() {
        $data = Flight::request()->data;
        
        if (!isset($data->email, $data->mdp)) {
            Flight::halt(400, json_encode(['error' => 'Email et mot de passe requis']));
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pret_login WHERE email = ?");
        $stmt->execute([$data->email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data->mdp, $user['mdp'])) {
            Flight::halt(401, json_encode(['error' => 'Email ou mot de passe incorrect']));
        }

        // Ici vous pourriez générer un token JWT si vous voulez une API stateless
        Flight::json(['message' => 'Connexion réussie', 'user' => ['id' => $user['id_login'], 'email' => $user['email']]]);
    }

    public static function register() {
        $data = Flight::request()->data;
        
        if (!isset($data->email, $data->mdp)) {
            Flight::halt(400, json_encode(['error' => 'Email et mot de passe requis']));
        }

        $db = getDB();
        
        // Vérifier si l'email existe déjà
        $stmt = $db->prepare("SELECT id_login FROM pret_login WHERE email = ?");
        $stmt->execute([$data->email]);
        
        if ($stmt->fetch()) {
            Flight::halt(400, json_encode(['error' => 'Cet email est déjà utilisé']));
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($data->mdp, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO pret_login (email, mdp) VALUES (?, ?)");
        $stmt->execute([$data->email, $hashedPassword]);

        Flight::json(['message' => 'Utilisateur créé avec succès', 'id' => $db->lastInsertId()]);
    }
}