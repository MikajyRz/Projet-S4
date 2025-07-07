<?php
require_once __DIR__ . '/../models/Auth.php';

class AuthController {
    public static function login() {
        $data = Flight::request()->data;
        error_log('Données reçues dans login: ' . print_r($data, true)); // Débogage
        $email = $data->email ?? '';
        $password = $data->password ?? '';

        if (empty($email) || empty($password)) {
            error_log('Email ou mot de passe vide');
            Flight::json(['error' => 'Email et mot de passe requis'], 400);
            return;
        }

        // Try client authentication
        $user = Auth::authenticateClient($email, $password);
        error_log('Résultat authentification client: ' . ($user ? 'Succès' : 'Échec')); // Débogage
        
        if (!$user) {
            // Try admin authentication
            $user = Auth::authenticateAdmin($email, $password);
            error_log('Résultat authentification admin: ' . ($user ? 'Succès' : 'Échec')); // Débogage
        }
        
        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'role' => $user['role']
            ];
            error_log('Session utilisateur créée: ' . print_r($_SESSION['user'], true)); // Débogage
            Flight::json([
                'message' => 'Connexion réussie',
                'role' => $user['role'],
                'redirect' => $user['role'] === 'admin' ? '/Projet-S4/public/admin_accueil.html' : '/Projet-S4/public/client_accueil.html'
            ]);
        } else {
            $error = Auth::emailExists($email) ? 'Mot de passe incorrect' : 'Email inexistant';
            error_log('Erreur d\'authentification: ' . $error); // Débogage
            Flight::json(['error' => $error], 401);
        }
    }

    public static function logout() {
        error_log('Session avant déconnexion: ' . print_r($_SESSION, true)); // Débogage
        session_destroy();
        error_log('Session détruite'); // Débogage
        $response = ['message' => 'Déconnexion réussie', 'redirect' => '/Projet-S4/public/index.html'];
        error_log('Réponse logout: ' . json_encode($response)); // Débogage
        Flight::json($response);
    }

    public static function adminDashboard() {
        error_log('Début adminDashboard - Session: ' . print_r($_SESSION, true)); // Débogage
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            error_log('Accès non autorisé à adminDashboard'); // Débogage
            Flight::json(['error' => 'Accès non autorisé'], 403);
            return;
        }
        $response = [
            'message' => 'Bienvenue, ' . $_SESSION['user']['nom'],
            'role' => 'admin'
        ];
        error_log('Réponse adminDashboard: ' . json_encode($response)); // Débogage
        Flight::json($response);
    }

    public static function clientDashboard() {
        error_log('Début clientDashboard - Session: ' . print_r($_SESSION, true)); // Débogage
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
            error_log('Accès non autorisé à clientDashboard'); // Débogage
            Flight::json(['error' => 'Accès non autorisé'], 403);
            return;
        }
        $response = [
            'message' => 'Bienvenue, ' . $_SESSION['user']['nom'],
            'role' => 'client'
        ];
        error_log('Réponse clientDashboard: ' . json_encode($response)); // Débogage
        Flight::json($response);
    }
}