<?php
require_once __DIR__ . '/../db.php';

class Auth {
    public static function authenticateClient($email, $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM Clients WHERE email = ?");
        $stmt->execute([$email]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($client && $password === $client['mot_de_passe']) {
            return [
                'id' => $client['id_client'],
                'nom' => $client['nom'],
                'role' => 'client'
            ];
        }
        return false;
    }

    public static function authenticateAdmin($email, $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM Banquaire WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && $password === $admin['mot_de_passe']) {
            return [
                'id' => $admin['id_bancaire'],
                'nom' => $admin['nom'],
                'role' => 'admin'
            ];
        }
        return false;
    }

    public static function emailExists($email) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM Clients WHERE email = ?");
        $stmt->execute([$email]);
        $clientExists = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM Banquaire WHERE email = ?");
        $stmt->execute([$email]);
        $adminExists = $stmt->fetchColumn();
        
        return $clientExists || $adminExists;
    }
}