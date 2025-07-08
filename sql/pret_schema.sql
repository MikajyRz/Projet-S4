CREATE DATABASE IF NOT EXISTS pret_db;
USE pret_db;


CREATE TABLE pret_fond (
    id_fond INT AUTO_INCREMENT PRIMARY KEY,
    motif VARCHAR(50),
    montant DECIMAL(15,2) NOT NULL
);

CREATE TABLE pret_client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL
);

CREATE TABLE pret_type_pret (
    id_type_pret INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    taux DECIMAL(15,2) NOT NULL,
    duree INT,
    assurance DECIMAL(5,2) DEFAULT 0
);

CREATE TABLE pret_pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15,2) NOT NULL,
    id_client INT NOT NULL,
    id_type_pret INT NOT NULL,
    date_pret DATE NOT NULL,
    statuts ENUM('en attente', 'valide','refuse') NOT NULL DEFAULT 'en attente',
    FOREIGN KEY (id_client) REFERENCES pret_client(id_client),
    FOREIGN KEY (id_type_pret) REFERENCES pret_type_pret(id_type_pret)
);

CREATE TABLE pret_remboursement (
    id_remboursement INT AUTO_INCREMENT PRIMARY KEY,
    id_pret INT NOT NULL,
    montant_total DECIMAL(15,2) NOT NULL,
    interet DECIMAL(15,2) NOT NULL,
    capital DECIMAL(15,2) NOT NULL,
    capital_restant DECIMAL(15,2) NOT NULL,
    mois INT NOT NULL,
    annee INT NOT NULL,
    assurance DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (id_pret) REFERENCES pret_pret(id_pret)
);

