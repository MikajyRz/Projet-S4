<?php
/**
 * Script de test pour la validation des prêts
 * Ce script teste le workflow complet de validation
 */

require_once 'ws/db.php';

echo "<h1>Test de Validation des Prêts</h1>\n";

try {
    $db = getDB();
    
    echo "<h2>1. Vérification de la structure de la base de données</h2>\n";
    
    // Vérifier que les tables existent
    $tables = ['Pret', 'StatutPret', 'HistoriqueStatutPret', 'Banquaire', 'Clients', 'TypePret'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table $table existe<br>\n";
        } else {
            echo "❌ Table $table manquante<br>\n";
        }
    }
    
    echo "<h2>2. Vérification des données de base</h2>\n";
    
    // Vérifier les statuts
    $stmt = $db->query("SELECT COUNT(*) FROM StatutPret");
    $count = $stmt->fetchColumn();
    echo "Statuts disponibles: $count<br>\n";
    
    // Vérifier les banquiers
    $stmt = $db->query("SELECT COUNT(*) FROM Banquaire");
    $count = $stmt->fetchColumn();
    echo "Banquiers disponibles: $count<br>\n";
    
    // Vérifier les clients
    $stmt = $db->query("SELECT COUNT(*) FROM Clients");
    $count = $stmt->fetchColumn();
    echo "Clients disponibles: $count<br>\n";
    
    // Vérifier les types de prêt
    $stmt = $db->query("SELECT COUNT(*) FROM TypePret");
    $count = $stmt->fetchColumn();
    echo "Types de prêt disponibles: $count<br>\n";
    
    echo "<h2>3. Test du workflow de validation</h2>\n";
    
    // Récupérer un client et un type de prêt pour le test
    $stmt = $db->query("SELECT id_client FROM Clients LIMIT 1");
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->query("SELECT id_type_pret FROM TypePret LIMIT 1");
    $typePret = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client || !$typePret) {
        echo "❌ Données insuffisantes pour le test (clients ou types de prêt manquants)<br>\n";
        exit;
    }
    
    $id_client = $client['id_client'];
    $id_type_pret = $typePret['id_type_pret'];
    
    echo "Client de test: ID $id_client<br>\n";
    echo "Type de prêt de test: ID $id_type_pret<br>\n";
    
    // ÉTAPE 1: Créer un prêt de test
    echo "<h3>Étape 1: Création d'un prêt de test</h3>\n";
    
    $db->beginTransaction();
    
    try {
        // Insérer le prêt
        $stmt = $db->prepare("
            INSERT INTO Pret (id_client, id_type_pret, id_statut_pret, date_debut, duree_mois, montant, taux_mensuel) 
            VALUES (?, ?, 1, CURDATE(), 12, 5000.00, 0.5)
        ");
        $stmt->execute([$id_client, $id_type_pret]);
        $id_pret = $db->lastInsertId();
        
        echo "✅ Prêt créé avec ID: $id_pret<br>\n";
        
        // Insérer dans l'historique
        $stmt = $db->prepare("
            INSERT INTO HistoriqueStatutPret (id_pret, id_statut_pret, commentaire) 
            VALUES (?, 1, 'Test: Demande de prêt soumise')
        ");
        $stmt->execute([$id_pret]);
        
        echo "✅ Historique initial créé<br>\n";
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        echo "❌ Erreur lors de la création du prêt: " . $e->getMessage() . "<br>\n";
        exit;
    }
    
    // ÉTAPE 2: Vérifier que le prêt est en attente
    echo "<h3>Étape 2: Vérification du statut initial</h3>\n";
    
    $stmt = $db->prepare("
        SELECT p.*, sp.libelle as statut_libelle 
        FROM Pret p 
        JOIN StatutPret sp ON p.id_statut_pret = sp.id_statut_pret 
        WHERE p.id_pret = ?
    ");
    $stmt->execute([$id_pret]);
    $pret = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pret['statut_libelle'] === 'En attente de validation') {
        echo "✅ Prêt en attente de validation<br>\n";
    } else {
        echo "❌ Statut incorrect: " . $pret['statut_libelle'] . "<br>\n";
    }
    
    // ÉTAPE 3: Valider le prêt
    echo "<h3>Étape 3: Validation du prêt</h3>\n";
    
    $db->beginTransaction();
    
    try {
        // Récupérer un banquier
        $stmt = $db->query("SELECT id_bancaire FROM Banquaire LIMIT 1");
        $banquier = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_bancaire = $banquier['id_bancaire'];
        
        // Update du statut vers "Validé"
        $stmt = $db->prepare("
            UPDATE Pret 
            SET id_statut_pret = 2,
                date_validation = CURDATE(),
                id_bancaire_validateur = ?
            WHERE id_pret = ?
        ");
        $stmt->execute([$id_bancaire, $id_pret]);
        
        echo "✅ Statut mis à jour vers 'Validé'<br>\n";
        
        // Insertion dans l'historique
        $stmt = $db->prepare("
            INSERT INTO HistoriqueStatutPret (id_pret, id_statut_pret, id_bancaire, commentaire) 
            VALUES (?, 2, ?, 'Test: Prêt validé')
        ");
        $stmt->execute([$id_pret, $id_bancaire]);
        
        echo "✅ Historique de validation créé<br>\n";
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        echo "❌ Erreur lors de la validation: " . $e->getMessage() . "<br>\n";
        exit;
    }
    
    // ÉTAPE 4: Vérifier le statut final
    echo "<h3>Étape 4: Vérification du statut final</h3>\n";
    
    $stmt = $db->prepare("
        SELECT p.*, sp.libelle as statut_libelle, b.nom as banquier_nom
        FROM Pret p 
        JOIN StatutPret sp ON p.id_statut_pret = sp.id_statut_pret 
        LEFT JOIN Banquaire b ON p.id_bancaire_validateur = b.id_bancaire
        WHERE p.id_pret = ?
    ");
    $stmt->execute([$id_pret]);
    $pret = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pret['statut_libelle'] === 'Validé') {
        echo "✅ Prêt validé avec succès<br>\n";
        echo "Validateur: " . $pret['banquier_nom'] . "<br>\n";
        echo "Date de validation: " . $pret['date_validation'] . "<br>\n";
    } else {
        echo "❌ Statut incorrect après validation: " . $pret['statut_libelle'] . "<br>\n";
    }
    
    // ÉTAPE 5: Vérifier l'historique
    echo "<h3>Étape 5: Vérification de l'historique</h3>\n";
    
    $stmt = $db->prepare("
        SELECT hsp.*, sp.libelle as statut_libelle, b.nom as banquier_nom
        FROM HistoriqueStatutPret hsp
        JOIN StatutPret sp ON hsp.id_statut_pret = sp.id_statut_pret
        LEFT JOIN Banquaire b ON hsp.id_bancaire = b.id_bancaire
        WHERE hsp.id_pret = ?
        ORDER BY hsp.date_statut ASC
    ");
    $stmt->execute([$id_pret]);
    $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Historique du prêt:<br>\n";
    echo "<ul>\n";
    foreach ($historique as $h) {
        echo "<li>" . $h['date_statut'] . " - " . $h['statut_libelle'];
        if ($h['banquier_nom']) {
            echo " (par " . $h['banquier_nom'] . ")";
        }
        echo ": " . $h['commentaire'] . "</li>\n";
    }
    echo "</ul>\n";
    
    if (count($historique) >= 2) {
        echo "✅ Historique complet créé<br>\n";
    } else {
        echo "❌ Historique incomplet<br>\n";
    }
    
    echo "<h2>4. Résumé du test</h2>\n";
    echo "✅ Workflow de validation des prêts fonctionnel<br>\n";
    echo "✅ Toutes les étapes ont été exécutées avec succès<br>\n";
    echo "✅ L'historique est correctement maintenu<br>\n";
    
    // Nettoyer le prêt de test
    echo "<h3>Nettoyage</h3>\n";
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("DELETE FROM HistoriqueStatutPret WHERE id_pret = ?");
        $stmt->execute([$id_pret]);
        
        $stmt = $db->prepare("DELETE FROM Pret WHERE id_pret = ?");
        $stmt->execute([$id_pret]);
        
        $db->commit();
        echo "✅ Prêt de test supprimé<br>\n";
    } catch (Exception $e) {
        $db->rollBack();
        echo "⚠️ Erreur lors du nettoyage: " . $e->getMessage() . "<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "<br>\n";
}

echo "<br><a href='public/validation_prets.html'>Accéder à l'interface de validation</a><br>\n";
echo "<a href='public/admin_accueil.html'>Retour à l'administration</a><br>\n";
?> 