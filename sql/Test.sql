-- Création de la base de données
CREATE DATABASE IF NOT EXISTS etablissement_financier;
USE etablissement_financier;

-- Table administrateur
-- Stocke les informations des administrateurs pour l'authentification
CREATE TABLE administrateur (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL
);

-- Table client
-- Stocke les informations des clients
CREATE TABLE client (
    id_client INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL, -- Mot de passe haché (bcrypt)
    telephone VARCHAR(20),
    adresse TEXT,
    penalise BOOLEAN NOT NULL DEFAULT FALSE, -- Indique si le client est pénalisé
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table etablissement_financier
-- Stocke les informations sur les fonds de l’établissement
CREATE TABLE etablissement_financier (
    id_etablissement INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    fonds_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    date_maj DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table type_pret
-- Stocke les différents types de prêts proposés
CREATE TABLE type_pret (
    id_type_pret INT PRIMARY KEY AUTO_INCREMENT,
    nom_pret VARCHAR(50) NOT NULL UNIQUE,
    taux_interet DECIMAL(5,2) NOT NULL,
    duree_max_mois INT NOT NULL,
    montant_max DECIMAL(15,2) NOT NULL
);

-- Table pret
-- Gère les demandes de prêts des clients
CREATE TABLE pret (
    id_pret INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    id_type_pret INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_demande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    duree_mois INT NOT NULL,
    statut ENUM('en attente', 'approuvé', 'refusé', 'remboursé') NOT NULL DEFAULT 'en attente',
    montant_rembourse DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE RESTRICT,
    FOREIGN KEY (id_type_pret) REFERENCES type_pret(id_type_pret) ON DELETE RESTRICT
);

-- Table transaction_fonds
-- Enregistre les ajouts ou retraits de fonds dans l’établissement
CREATE TABLE transaction_fonds (
    id_transaction INT PRIMARY KEY AUTO_INCREMENT,
    id_etablissement INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    type_transaction ENUM('ajout', 'retrait') NOT NULL,
    date_transaction DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (id_etablissement) REFERENCES etablissement_financier(id_etablissement) ON DELETE RESTRICT
);

-- Table remboursement
-- Suit les remboursements effectués pour les prêts
CREATE TABLE remboursement (
    id_remboursement INT PRIMARY KEY AUTO_INCREMENT,
    id_pret INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_remboursement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pret) REFERENCES pret(id_pret) ON DELETE RESTRICT
);

-- Table penalite
-- Enregistre les pénalités appliquées aux clients pour les prêts en retard
CREATE TABLE penalite (
    id_penalite INT PRIMARY KEY AUTO_INCREMENT,
    id_pret INT NOT NULL,
    id_client INT NOT NULL,
    id_admin INT NOT NULL,
    description TEXT,
    date_penalite DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pret) REFERENCES pret(id_pret) ON DELETE RESTRICT,
    FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE RESTRICT,
    FOREIGN KEY (id_admin) REFERENCES administrateur(id_admin) ON DELETE RESTRICT
);

-- Index pour optimiser les recherches fréquentes
CREATE INDEX idx_pret_id_client ON pret(id_client);
CREATE INDEX idx_pret_id_type_pret ON pret(id_type_pret);
CREATE INDEX idx_transaction_id_etablissement ON transaction_fonds(id_etablissement);
CREATE INDEX idx_remboursement_id_pret ON remboursement(id_pret);
CREATE INDEX idx_penalite_id_client ON penalite(id_client);

-- Données initiales (optionnelles pour tests)
INSERT INTO etablissement_financier (nom, fonds_total) VALUES ('Banque Centrale', 1000000.00);
INSERT INTO type_pret (nom_pret, taux_interet, duree_max_mois, montant_max) VALUES 
    ('Prêt Personnel', 5.50, 60, 50000.00),
    ('Prêt Immobilier', 3.75, 240, 500000.00);
INSERT INTO administrateur (nom, email, mot_de_passe) VALUES