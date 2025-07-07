CREATE DATABASE pret;
USE pret;

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
    telephone VARCHAR(15) NOT NULL
);

CREATE TABLE Fond_Etablissement (
    id_fond_etablissement INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    montant DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    date_maj DATE NOT NULL    
);

CREATE TABLE TypePret (
    id_type_pret INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL,
    taux DECIMAL(5, 2) NOT NULL,
    duree_max_mois INT NOT NULL,
    montant_max DECIMAL(15, 2) NOT NULL
);

CREATE TABLE Pret (
    id_pret INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_type_pret INT NOT NULL,
    date_demande DATE NOT NULL,
    duree_mois INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    id_statut_pret INT NOT NULL,
    FOREIGN KEY (id_client) REFERENCES Clients(id_client),
    FOREIGN KEY (id_type_pret) REFERENCES TypePret(id_type_pret),
    FOREIGN KEY (id_statut_pret) REFERENCES StatutPret(id_statut_pret)
);


-- CREATE TABLE TypeEtablissement (
--     id_type_etablissement INT AUTO_INCREMENT PRIMARY KEY,
--     libelle VARCHAR(50) NOT NULL
-- );





-- CREATE TABLE TypeClient (
--     id_type_client INT AUTO_INCREMENT PRIMARY KEY,
--     libelle VARCHAR(50) NOT NULL,
--     duree_pret INT NOT NULL,
-- );




-- CREATE TABLE StatutPret (
--     id_statut_pret INT AUTO_INCREMENT PRIMARY KEY,
--     libelle VARCHAR(50) NOT NULL
-- );


-- CREATE TABLE Compte (
--     id_compte INT AUTO_INCREMENT PRIMARY KEY,
--     id_client INT NOT NULL,
--     solde DECIMAL(10, 2) NOT NULL DEFAULT 0,
--     date_creation DATE NOT NULL,
--     FOREIGN KEY (id_client) REFERENCES Clients(id_client)
-- );