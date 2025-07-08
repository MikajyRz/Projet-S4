<?php
require_once __DIR__ . '/../db.php';

class Client {

    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret_client");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pret_client WHERE id_client = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret_client (nom, email) VALUES (?, ?)");
        $stmt->execute([$data->nom, $data->email]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret_client SET nom = ?, email = ? WHERE id_client = ?");
        $stmt->execute([$data->nom, $data->email, $id]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret_client WHERE id_client = ?");
        $stmt->execute([$id]);
    }
}
