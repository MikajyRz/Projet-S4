-- Insertion de données de test
INSERT INTO pret_login (email, mdp) VALUES 
('admin@pret.com', 'admin123');

INSERT INTO pret_fond (motif, montant) VALUES 
('Capital initial', 1000000.00),
('Dépôt supplémentaire', 500000.00);

INSERT INTO pret_client (nom, email) VALUES 
('Jean Dupont', 'jean.dupont@email.com'),
('Marie Martin', 'marie.martin@email.com'),
('Pierre Durand', 'pierre.durand@email.com');

INSERT INTO pret_type_pret (nom, taux, duree, assurance) VALUES 
('Prêt personnel', 8.50, 24, 1.50),
('Prêt immobilier', 6.20, 120, 2.00),
('Prêt étudiant', 5.80, 36, 0.80),
('Prêt professionnel', 9.20, 60, 2.50); 