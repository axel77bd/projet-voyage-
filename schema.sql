-- Création de la base de données
CREATE DATABASE AgenceVoyage;
GO

USE AgenceVoyage;
GO

CREATE TABLE Circuit(
   identifiant INT,
   descriptif VARCHAR(50),
   villedepart VARCHAR(50),
   villearrivee VARCHAR(50),
   paysarrivee VARCHAR(50), -- Corrigé: changé de DATETIME à VARCHAR(50)
   datedepart DATETIME,
   nbrplacedisponible INT,
   duree SMALLINT,
   prixinscription DECIMAL(15,2),
   PRIMARY KEY(identifiant)
);

CREATE TABLE lieuavisiter(
   nomlieu VARCHAR(50),
   ville VARCHAR(50),
   pays VARCHAR(50),
   descriptif VARCHAR(50),
   prixvisite DECIMAL(15,2),
   PRIMARY KEY(nomlieu, ville, pays)
);

CREATE TABLE client(
   idclient VARCHAR(50),
   nom VARCHAR(50),
   prenom VARCHAR(50),
   datenaissance DATETIME,
   PRIMARY KEY(idclient)
);

CREATE TABLE etape(
   identifiant INT,
   ordre INT,
   duree SMALLINT,
   nomlieu VARCHAR(50) NOT NULL,
   ville VARCHAR(50) NOT NULL,
   pays VARCHAR(50) NOT NULL,
   PRIMARY KEY(identifiant, ordre),
   FOREIGN KEY(identifiant) REFERENCES Circuit(identifiant),
   FOREIGN KEY(nomlieu, ville, pays) REFERENCES lieuavisiter(nomlieu, ville, pays)
);

CREATE TABLE reservation(
   identifiant INT,
   idclient VARCHAR(50),
   datereservation DATE,
   nbplacedispo INT,
   PRIMARY KEY(identifiant, idclient),
   FOREIGN KEY(identifiant) REFERENCES Circuit(identifiant),
   FOREIGN KEY(idclient) REFERENCES client(idclient)
);
