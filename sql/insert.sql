INSERT INTO pf_EF (nom) VALUES ('Banque Nationale');


INSERT INTO pf_login (email, mdp) VALUES
('admin@pf.com', 'admin123'),
('user1@pf.com', 'password1'),
('user2@pf.com', 'password2');


INSERT INTO pf_EF (nom) VALUES
('Bank Mada'),
('Finance Express'),
('Credit Solidaire');


INSERT INTO pf_fond (motif, montant, id_EF) VALUES
('Investissement initial', 5000000.00, 1),
('Renflouement de caisse', 3000000.00, 2),
('Capital social', 10000000.00, 3);


INSERT INTO pf_client (nom, email) VALUES
('Randrianirina Jean', 'jean.ran@email.com'),
('Rasoanaivo Marie', 'marie.raso@email.com'),
('Rakoto David', 'david.rakoto@email.com');


INSERT INTO pf_type_pret (nom, taux, duree) VALUES
('Prêt personnel', 5.5, 12),
('Crédit immobilier', 7.2, 240),
('Microcrédit', 3.0, 6);


INSERT INTO pf_pret (montant, id_client, id_type_pret, id_EF, date_pret, status) VALUES
(2000000.00, 1, 1, 1, '2025-01-15', 'valide'),
(5000000.00, 2, 2, 2, '2025-02-01', 'en attente'),
(800000.00, 3, 3, 3, '2025-03-10', 'valide');

INSERT INTO pf_remboursement (id_pret, montant_total, interet, capital, capital_restant, mois, annee) VALUES
(1, 2100000.00, 100000.00, 2000000.00, 1500000.00, 1, 2025),
(1, 2100000.00, 100000.00, 2000000.00, 1000000.00, 2, 2025),
(3, 820000.00, 20000.00, 800000.00, 400000.00, 1, 2025);
