CREATE DATABASE pret;
USE pret;

CREATE TABLE Fond_Etablissement (
    id_fond INT AUTO_INCREMENT PRIMARY KEY,
    montant_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    date_maj DATE NOT NULL    
);

CREATE TABLE Banquaire (
    id_bancaire INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL 
);

CREATE TABLE Clients (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL, 
    date_naissance DATE NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    telephone VARCHAR(15) NOT NULL,
    revenu_mensuel DECIMAL(10, 2) NOT NULL
);

CREATE TABLE TypePret (
    id_type_pret INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL,
    taux_annuel DECIMAL(5, 2) NOT NULL,
    duree_max_mois INT NOT NULL,
    montant_min DECIMAL(15, 2) NOT NULL,
    montant_max DECIMAL(15, 2) NOT NULL
);

CREATE TABLE Type_transaction (
    id_type_transaction INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

-- Table des statuts possibles pour un prêt
CREATE TABLE StatutPret (
    id_statut_pret INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

-- Insertion des statuts AVANT de créer la table Pret
INSERT INTO StatutPret (libelle) VALUES 
('En attente de validation'),
('Validé'),
('Refusé'),
('En cours'),
('Terminé'),
('En défaut');

-- Table principale des prêts (maintenant les statuts existent)
CREATE TABLE Pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_type_pret INT NOT NULL,
    id_statut_pret INT NOT NULL DEFAULT 1, -- 1 = En attente de validation
    date_debut DATE NOT NULL,
    duree_mois INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    taux_mensuel DECIMAL(5, 2) NOT NULL,
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation DATE NULL,
    id_bancaire_validateur INT NULL,
    motif_refus TEXT NULL,
    FOREIGN KEY (id_client) REFERENCES Clients(id_client),
    FOREIGN KEY (id_type_pret) REFERENCES TypePret(id_type_pret),
    FOREIGN KEY (id_statut_pret) REFERENCES StatutPret(id_statut_pret),
    FOREIGN KEY (id_bancaire_validateur) REFERENCES Banquaire(id_bancaire)
);

-- Table pour l'historique des statuts
CREATE TABLE HistoriqueStatutPret (
    id_historique_statut INT AUTO_INCREMENT PRIMARY KEY,
    id_pret INT NOT NULL,
    id_statut_pret INT NOT NULL,
    date_statut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_bancaire INT NULL,
    commentaire TEXT NULL,
    FOREIGN KEY (id_pret) REFERENCES Pret(id_pret),
    FOREIGN KEY (id_statut_pret) REFERENCES StatutPret(id_statut_pret),
    FOREIGN KEY (id_bancaire) REFERENCES Banquaire(id_bancaire)
);

CREATE TABLE transactions (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    id_fonds INT NOT NULL,
    id_type_transaction INT NOT NULL,
    id_pret INT NULL,
    date_transaction DATE NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_fonds) REFERENCES Fond_Etablissement(id_fond),
    FOREIGN KEY (id_type_transaction) REFERENCES Type_transaction(id_type_transaction),
    FOREIGN KEY (id_pret) REFERENCES Pret(id_pret)
);

CREATE TABLE HistoriquePret (
    id_historique INT AUTO_INCREMENT PRIMARY KEY,
    id_pret INT NOT NULL,
    mois DATE NOT NULL,
    montant_mensuelite DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_pret) REFERENCES Pret(id_pret)
);

-- Insertion des types de transactions
INSERT INTO Type_transaction (libelle) VALUES 
('Déblocage de prêt'),
('Remboursement mensuel'),
('Frais de dossier'),
('Intérêts'),
('Pénalités');

-- Insertion d'un fonds initial
INSERT INTO Fond_Etablissement (montant_total, date_maj) VALUES (1000000.00, CURDATE());

-- Insertion d'un banquier par défaut
INSERT INTO Banquaire (nom, email, mot_de_passe) VALUES ('Admin', 'admin@banque.com', 'password123');

-- Insertion de données de test
INSERT INTO TypePret (libelle, taux_annuel, duree_max_mois, montant_min, montant_max) VALUES 
('Prêt personnel', 8.50, 60, 1000.00, 50000.00),
('Prêt immobilier', 3.20, 300, 50000.00, 500000.00),
('Prêt automobile', 6.80, 84, 5000.00, 100000.00);

INSERT INTO Clients (nom, email, mot_de_passe, date_naissance, adresse, telephone, revenu_mensuel) VALUES 
('Dupont Jean', 'jean.dupont@email.com', 'motdepasse123', '1985-03-15', '123 Rue de la Paix, Paris', '0123456789', 3500.00),
('Martin Marie', 'marie.martin@email.com', 'motdepasse456', '1990-07-22', '456 Avenue Victor Hugo, Lyon', '0987654321', 4200.00);

INSERT INTO Banquaire (nom, email, mot_de_passe) VALUES 
('Durand Sophie', 'sophie.durand@banque.com', 'banquier123'),
('Leroy Michel', 'michel.leroy@banque.com', 'banquier456'); 