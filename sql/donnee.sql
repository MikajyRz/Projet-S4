INSERT INTO Banquaire (nom, email, mot_de_passe) VALUES
('Jean Dupont', 'jean.dupont@banque.fr', 'mdpBanque123'),
('Marie Lambert', 'marie.lambert@banque.fr', 'securePass456'),
('Pierre Garnier', 'pierre.garnier@banque.fr', 'admin789Bank');

INSERT INTO Clients (nom, email, mot_de_passe, date_naissance, adresse, telephone) VALUES
('Sophie Martin', 'sophie.martin@gmail.com', 'clientPass1', '1985-04-12', '12 rue des Lilas, Paris', '0612345678'),
('Thomas Leroy', 'thomas.leroy@hotmail.com', 'monMotDePasse2', '1990-11-25', '45 avenue Victor Hugo, Lyon', '0698765432'),
('Laura Dubois', 'laura.dubois@yahoo.com', 'secure1234', '1978-07-03', '8 boulevard Gambetta, Marseille', '0687654321'),
('Marc Petit', 'marc.petit@gmail.com', 'petitMarc56', '1995-02-18', '32 rue de la République, Lille', '0678912345');

INSERT INTO StatutPret (libelle) VALUES
('En attente'),
('Approuve'),
('Refuse'),
('Rembourse');


INSERT INTO Type_transaction (libelle) VALUES
('fonds'),
('Retrait'),
('prêt'),
('Remboursement');




















INSERT INTO TypeClient (libelle, duree_pret) VALUES
('Particuliers', 30),
('Professionels', 60),
('Grande entreprises', 90);



