

PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS historique_operation;
DROP TABLE IF EXISTS frais;
DROP TABLE IF EXISTS comptes;
DROP TABLE IF EXISTS operation;
DROP TABLE IF EXISTS prefixes;



CREATE TABLE prefixes (
    idPrefixe INTEGER PRIMARY KEY AUTOINCREMENT,
    valeur VARCHAR(10) NOT NULL UNIQUE,
    statut INTEGER DEFAULT 1 CHECK (statut IN (0, 1))
);

CREATE TABLE comptes (
    idCompte INTEGER PRIMARY KEY AUTOINCREMENT,
    numeroTel VARCHAR(20) NOT NULL UNIQUE,
    solde REAL DEFAULT 0,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE operation (
    idOperation INTEGER PRIMARY KEY AUTOINCREMENT,
    type VARCHAR(20) NOT NULL CHECK (type IN ('depot', 'retrait', 'transfert'))
);

CREATE TABLE frais (
    idFrais INTEGER PRIMARY KEY AUTOINCREMENT,
    idOperation INTEGER NOT NULL,
    montantMin REAL NOT NULL,
    montantMax REAL NOT NULL,
    frais REAL NOT NULL,
    FOREIGN KEY (idOperation) REFERENCES operation(idOperation)
);

CREATE TABLE historique_operation (
    idHistorique INTEGER PRIMARY KEY AUTOINCREMENT,
    idCompte INTEGER NOT NULL,
    idCompteDestinataire INTEGER,
    idOperation INTEGER NOT NULL,
    montant REAL NOT NULL,
    fraisTotal REAL NOT NULL,
    dateTransaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idCompte) REFERENCES comptes(idCompte),
    FOREIGN KEY (idCompteDestinataire) REFERENCES comptes(idCompte),
    FOREIGN KEY (idOperation) REFERENCES operation(idOperation)
);


INSERT INTO prefixes (valeur, statut) VALUES
('032', 1),
('033', 1),
('034', 1),
('035', 1),
('037', 1),
('038', 1);


INSERT INTO operation (type) VALUES
('depot'),      
('retrait'),    
('transfert'); 


INSERT INTO comptes (numeroTel, solde, nom, prenom) VALUES
('0331000001', 150000.0, 'Rakoto', 'Jean'),     
('0372000002', 50000.0, 'Rasoa', 'Marie'),       
('0333000003', 2500000.0, 'Randria', 'Paul');    


INSERT INTO frais (idOperation, montantMin, montantMax, frais) VALUES
(2, 100, 1000, 50),
(2, 1001, 5000, 50),
(2, 5001, 10000, 100),
(2, 10001, 25000, 200),
(2, 25001, 50000, 400),
(2, 50001, 100000, 800),
(2, 100001, 250000, 1500),
(2, 250001, 500000, 1500),
(2, 500001, 1000000, 2500),
(2, 1000001, 2000000, 3000);


INSERT INTO frais (idOperation, montantMin, montantMax, frais) VALUES
(3, 100, 1000, 20),
(3, 1001, 5000, 50),
(3, 5001, 10000, 100),
(3, 10001, 50000, 200),
(3, 50001, 100000, 500),
(3, 100001, 1000000, 1000);


INSERT INTO historique_operation (idCompte, idCompteDestinataire, idOperation, montant, fraisTotal)
VALUES (1, NULL, 1, 100000, 0);


INSERT INTO historique_operation (idCompte, idCompteDestinataire, idOperation, montant, fraisTotal)
VALUES (1, NULL, 2, 15000, 200);


INSERT INTO historique_operation (idCompte, idCompteDestinataire, idOperation, montant, fraisTotal)
VALUES (3, 2, 3, 10000, 100);
