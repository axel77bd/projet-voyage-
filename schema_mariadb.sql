CREATE DATABASE IF NOT EXISTS AgenceVoyage;
USE AgenceVoyage;

CREATE TABLE Circuit(
   identifiant INT PRIMARY KEY,
   descriptif VARCHAR(50),
   villedepart VARCHAR(50),
   villearrivee VARCHAR(50),
   paysarrivee VARCHAR(50),
   datedepart DATETIME,
   nbrplacedisponible INT,
   duree SMALLINT,
   prixinscription DECIMAL(15,2)
);

CREATE TABLE lieuavisiter(
   nomlieu VARCHAR(50),
   ville VARCHAR(50),
   pays VARCHAR(50),
   descriptif VARCHAR(50),
   prixvisite DECIMAL(15,2),
   PRIMARY KEY(nomlieu, ville, pays)
);

-- Ajout du champ motdepasse pour le client
CREATE TABLE client(
   idclient VARCHAR(50) PRIMARY KEY,
   nom VARCHAR(50),
   prenom VARCHAR(50),
   datenaissance DATETIME,
   motdepasse VARCHAR(255) NOT NULL
);

-- Nouvelle table pour les administrateurs
CREATE TABLE administrateur(
   idadmin INT AUTO_INCREMENT PRIMARY KEY,
   identifiant VARCHAR(50) UNIQUE NOT NULL,
   motdepasse VARCHAR(255) NOT NULL
);

CREATE TABLE etape(
   identifiant INT,
   ordre INT,
   duree SMALLINT,
   nomlieu VARCHAR(50) NOT NULL,
   ville VARCHAR(50) NOT NULL,
   pays VARCHAR(50) NOT NULL,
   PRIMARY KEY(identifiant, ordre),
   FOREIGN KEY(identifiant) REFERENCES Circuit(identifiant) ON DELETE CASCADE,
   FOREIGN KEY(nomlieu, ville, pays) REFERENCES lieuavisiter(nomlieu, ville, pays) ON DELETE CASCADE
);

CREATE TABLE reservation(
   identifiant INT,
   idclient VARCHAR(50),
   datereservation DATE,
   nbplacedispo INT,
   PRIMARY KEY(identifiant, idclient),
   FOREIGN KEY(identifiant) REFERENCES Circuit(identifiant) ON DELETE CASCADE,
   FOREIGN KEY(idclient) REFERENCES client(idclient) ON DELETE CASCADE
);

-- InsertAdmin : identifiant "admin", mot de passe "admin123" (haché bcyrpt généré par PHP)
INSERT INTO administrateur (identifiant, motdepasse) 
VALUES ('admin', '$2y$10$3tKXZ.GkQh62mX721OZZ1uvL6.sV0E4JmNpwjZ8j28q5XzDkYYlS2');
