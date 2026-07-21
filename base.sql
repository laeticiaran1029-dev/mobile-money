PRAGMA foreign_keys = ON;

-- 1. Nettoyage (dans l'ordre inverse des dépendances)
DROP TABLE IF EXISTS historique_operation;
DROP TABLE IF EXISTS frais;
DROP TABLE IF EXISTS commissions;
DROP TABLE IF EXISTS prefixes;
DROP TABLE IF EXISTS operateurs;
DROP TABLE IF EXISTS comptes;
DROP TABLE IF EXISTS operation;

-- 2. Création des tables parentes (indépendantes)
CREATE TABLE operateurs (
    idOperateur INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE operation (
    idOperation INTEGER PRIMARY KEY AUTOINCREMENT,
    type VARCHAR(20) NOT NULL CHECK (type IN ('depot', 'retrait', 'transfert'))
);

CREATE TABLE comptes (
    idCompte INTEGER PRIMARY KEY AUTOINCREMENT,
    numeroTel VARCHAR(20) NOT NULL UNIQUE,
    solde REAL DEFAULT 0,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Création des tables enfants (avec clés étrangères)
CREATE TABLE prefixes (
    idPrefixe INTEGER PRIMARY KEY AUTOINCREMENT,
    valeur VARCHAR(10) NOT NULL UNIQUE,
    idOperateur INTEGER NOT NULL,
    statut INTEGER DEFAULT 1 CHECK (statut IN (0, 1)),
    FOREIGN KEY (idOperateur) REFERENCES operateurs(idOperateur)
);

-- Table pour stocker la matrice des taux de commission entre opérateurs
CREATE TABLE commissions (
    idOperateurSource INTEGER NOT NULL,
    idOperateurDestinataire INTEGER NOT NULL,
    taux REAL NOT NULL,
    PRIMARY KEY (idOperateurSource, idOperateurDestinataire),
    FOREIGN KEY (idOperateurSource) REFERENCES operateurs(idOperateur),
    FOREIGN KEY (idOperateurDestinataire) REFERENCES operateurs(idOperateur)
);

CREATE TABLE frais (
    idFrais INTEGER PRIMARY KEY AUTOINCREMENT,
    idOperation INTEGER NOT NULL,
    idOperateur INTEGER NOT NULL,
    montantMin REAL NOT NULL,
    montantMax REAL NOT NULL,
    frais REAL NOT NULL,
    FOREIGN KEY (idOperation) REFERENCES operation(idOperation),
    FOREIGN KEY (idOperateur) REFERENCES operateurs(idOperateur)
);

CREATE TABLE historique_operation (
    idHistorique INTEGER PRIMARY KEY AUTOINCREMENT,
    idCompte INTEGER NOT NULL,
    idCompteDestinataire INTEGER, -- Nullable pour envois externes
    idOperation INTEGER NOT NULL,
    montant REAL NOT NULL,
    fraisTotal REAL NOT NULL,
    dateTransaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Nouvelles colonnes v2 intégrées directement :
    numeroDestinataire TEXT,
    idOperateurDestinataire INTEGER,
    commission REAL DEFAULT 0,
    fraisRetraitInclus REAL DEFAULT 0,
    FOREIGN KEY (idCompte) REFERENCES comptes(idCompte),
    FOREIGN KEY (idCompteDestinataire) REFERENCES comptes(idCompte),
    FOREIGN KEY (idOperation) REFERENCES operation(idOperation),
    FOREIGN KEY (idOperateurDestinataire) REFERENCES operateurs(idOperateur)
);

-- 4. Insertion des données (Seed)
INSERT INTO operateurs (nom) VALUES
('Yas'),
('Orange'),
('Airtel');

-- Taux croises source -> destinataire, en %, appliques en plus du bareme.
INSERT INTO commissions (idOperateurSource, idOperateurDestinataire, taux) VALUES
(1, 1, 0.0),   -- Yas    vers Yas
(1, 2, 2.5),   -- Yas    vers Orange
(1, 3, 2.0),   -- Yas    vers Airtel
(2, 1, 2.5),   -- Orange vers Yas
(2, 2, 1.0),   -- Orange vers Orange
(2, 3, 3.5),   -- Orange vers Airtel
(3, 1, 3.0),   -- Airtel vers Yas
(3, 2, 4.0),   -- Airtel vers Orange
(3, 3, 0.2);   -- Airtel vers Airtel

INSERT INTO prefixes (valeur, idOperateur, statut) VALUES
('034', 1, 1),
('038', 1, 1),
('032', 2, 1),
('037', 2, 1),
('033', 3, 1),
('031', 2, 1);

INSERT INTO operation (type) VALUES
('depot'),      
('retrait'),    
('transfert'); 

INSERT INTO comptes (numeroTel, solde, nom, prenom) VALUES
('0331000001', 150000.0, 'Rakoto', 'Jean'),
('0372000002', 50000.0, 'Rasoa', 'Marie'),
('0333000003', 2500000.0, 'Randria', 'Paul'),
('0341000004', 300000.0, 'Ratsimba', 'Hery'),
('0321000005', 80000.0, 'Andria', 'Lova');

-- Frais pour les retraits (idOperation = 2) chez Yas (idOperateur = 1)
INSERT INTO frais (idOperation, idOperateur, montantMin, montantMax, frais) VALUES
(2, 1, 100, 1000, 50),
(2, 1, 1001, 5000, 50),
(2, 1, 5001, 10000, 100),
(2, 1, 10001, 25000, 200),
(2, 1, 25001, 50000, 400),
(2, 1, 50001, 100000, 800),
(2, 1, 100001, 250000, 1500),
(2, 1, 250001, 500000, 1500),
(2, 1, 500001, 1000000, 2500),
(2, 1, 1000001, 2000000, 3000);

-- Frais pour les transferts (idOperation = 3) chez Yas (idOperateur = 1)
INSERT INTO frais (idOperation, idOperateur, montantMin, montantMax, frais) VALUES
(3, 1, 100, 1000, 20),
(3, 1, 1001, 5000, 50),
(3, 1, 5001, 10000, 100),
(3, 1, 10001, 50000, 200),
(3, 1, 50001, 100000, 500),
(3, 1, 100001, 1000000, 1000);

-- Retraits chez Orange (2) et Airtel (3)
INSERT INTO frais (idOperation, idOperateur, montantMin, montantMax, frais) VALUES
(2, 2, 100, 5000, 60),
(2, 2, 5001, 25000, 250),
(2, 2, 25001, 100000, 900),
(2, 2, 100001, 1000000, 2000),
(2, 3, 100, 5000, 40),
(2, 3, 5001, 25000, 180),
(2, 3, 25001, 100000, 700),
(2, 3, 100001, 1000000, 1800);

-- Transferts chez Orange (2) et Airtel (3)
INSERT INTO frais (idOperation, idOperateur, montantMin, montantMax, frais) VALUES
(3, 2, 100, 5000, 40),
(3, 2, 5001, 50000, 220),
(3, 2, 50001, 1000000, 900),
(3, 3, 100, 5000, 30),
(3, 3, 5001, 50000, 150),
(3, 3, 50001, 1000000, 800);

-- Historique initial de démonstration
INSERT INTO historique_operation (idCompte, idCompteDestinataire, idOperation, montant, fraisTotal)
VALUES (1, NULL, 1, 100000, 0);

INSERT INTO historique_operation (idCompte, idCompteDestinataire, idOperation, montant, fraisTotal)
VALUES (1, NULL, 2, 15000, 200);

INSERT INTO historique_operation (idCompte, idCompteDestinataire, idOperation, montant, fraisTotal)
VALUES (3, 2, 3, 10000, 100);