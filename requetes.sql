USE AgenceVoyage;
GO

-- =========================================================================
-- INSÉRER DES DONNÉES DE TEST
-- =========================================================================

-- 1. Lieux à visiter
INSERT INTO lieuavisiter (nomlieu, ville, pays, descriptif, prixvisite) VALUES
('Medina', 'Casablanca', 'Maroc', 'Ancienne ville', 10.00),
('Mosquée Hassan II', 'Casablanca', 'Maroc', 'Édifice religieux', 30.00),
('Village Berbère 1', 'Atlas', 'Maroc', 'Découverte culturelle', 15.00),
('Village Berbère 2', 'Atlas', 'Maroc', 'Repas traditionnel', 20.00),
('Mont Toubkal', 'Atlas', 'Maroc', 'Randonnée en montagne', 50.00),
('Jemaa el-Fna', 'Marrakech', 'Maroc', 'Place mythique', 0.00),
('Jardin Majorelle', 'Marrakech', 'Maroc', 'Jardins botaniques', 25.00),
('Musée Non Visité', 'Rabat', 'Maroc', 'Lieu sans etape', 5.00);

-- 2. Circuits (dont le Circuit 7 demandé dans la Q3)
INSERT INTO Circuit (identifiant, descriptif, villedepart, villearrivee, paysarrivee, datedepart, nbrplacedisponible, duree, prixinscription) VALUES
(7, 'Découverte de l''Atlas et des villages berbères', 'Casablanca', 'Marrakech', 'Maroc', '2026-06-10', 20, 7, 500.00),
(8, 'Grand tour du Nord', 'Tanger', 'Fes', 'Maroc', '2026-07-01', 15, 10, 800.00);

-- 3. Etapes pour le Circuit 7 (4 étapes précises pour correspondre à la Q3)
INSERT INTO etape (identifiant, ordre, duree, nomlieu, ville, pays) VALUES
(7, 1, 1, 'Mosquée Hassan II', 'Casablanca', 'Maroc'),
(7, 2, 2, 'Village Berbère 1', 'Atlas', 'Maroc'),
(7, 3, 2, 'Village Berbère 2', 'Atlas', 'Maroc'),
(7, 4, 1, 'Jemaa el-Fna', 'Marrakech', 'Maroc');

-- Etapes pour Circuit 8
INSERT INTO etape (identifiant, ordre, duree, nomlieu, ville, pays) VALUES
(8, 1, 2, 'Medina', 'Casablanca', 'Maroc'),
(8, 2, 3, 'Mont Toubkal', 'Atlas', 'Maroc');

-- 4. Clients
INSERT INTO client (idclient, nom, prenom, datenaissance) VALUES
('C001', 'Dupont', 'Jean', '1980-05-14'),
('C002', 'Martin', 'Sophie', '1992-11-23');
GO


-- =========================================================================
-- QUESTION 3 : Informations sur le circuit 7
-- =========================================================================
PRINT '--- Q3: Affichage Circuit 7 ---';

SELECT 
    CONCAT(
        'Information sur le circuit ', c.identifiant, ' : ', 
        c.descriptif, ', ', 
        c.villedepart, ', ', 
        c.villearrivee, ', ', 
        COUNT(e.identifiant), ' étapes.'
    ) AS ResultatAffiche
FROM Circuit c
LEFT JOIN etape e ON c.identifiant = e.identifiant
WHERE c.identifiant = 7
GROUP BY c.identifiant, c.descriptif, c.villedepart, c.villearrivee;
GO


-- =========================================================================
-- QUESTION 4 : Supprimer un lieu s'il n'est pas visité
-- =========================================================================
PRINT '--- Q4: Procédure SupprimerLieuNonVisite ---';
GO

CREATE OR ALTER PROCEDURE SupprimerLieuNonVisite
    @nomL VARCHAR(50),
    @villeL VARCHAR(50),
    @paysL VARCHAR(50)
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM etape WHERE nomlieu = @nomL AND ville = @villeL AND pays = @paysL)
    BEGIN
        DELETE FROM lieuavisiter WHERE nomlieu = @nomL AND ville = @villeL AND pays = @paysL;
        PRINT CONCAT('Succès : Le lieu "', @nomL, '" a été supprimé.');
    END
    ELSE
    BEGIN
        PRINT CONCAT('Échec : Impossible de supprimer "', @nomL, '", il est visité dans un circuit.');
    END
END
GO

-- Test (Devrait fonctionner sur le Musée Non Visité)
EXEC SupprimerLieuNonVisite 'Musée Non Visité', 'Rabat', 'Maroc';
GO


-- =========================================================================
-- QUESTION 5 : Prix d'un circuit complet
-- =========================================================================
PRINT '--- Q5: Prix d''un circuit touristique complet ---';

SELECT 
    c.identifiant, 
    c.descriptif,
    c.prixinscription AS PrixBase,
    COALESCE(SUM(l.prixvisite), 0) AS CoutsSupplementairesVisites,
    (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) AS PrixComplet
FROM Circuit c
LEFT JOIN etape e ON c.identifiant = e.identifiant
LEFT JOIN lieuavisiter l ON e.nomlieu = l.nomlieu AND e.ville = l.ville AND e.pays = l.pays
GROUP BY c.identifiant, c.descriptif, c.prixinscription;
GO


-- =========================================================================
-- QUESTION 6 : Circuts compatibles (Budget, Dates, Places)
-- =========================================================================
PRINT '--- Q6: Procédure RechercherCircuits ---';
GO

CREATE OR ALTER PROCEDURE RechercherCircuits
    @BudgetMax DECIMAL(15,2),
    @DateDebutVacances DATETIME,
    @DateFinVacances DATETIME,
    @NbPlacesNecessaires INT
AS
BEGIN
    SELECT 
        c.identifiant, 
        (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) AS PrixComplet, 
        c.datedepart AS DateDeDebut, 
        c.duree
    FROM Circuit c
    LEFT JOIN etape e ON c.identifiant = e.identifiant
    LEFT JOIN lieuavisiter l ON e.nomlieu = l.nomlieu AND e.ville = l.ville AND e.pays = l.pays
    WHERE c.nbrplacedisponible >= @NbPlacesNecessaires
      AND c.datedepart >= @DateDebutVacances
      AND DATEADD(day, c.duree, c.datedepart) <= @DateFinVacances
    GROUP BY c.identifiant, c.prixinscription, c.datedepart, c.duree, c.nbrplacedisponible
    HAVING (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) <= @BudgetMax;
END
GO

-- Test : Budget max 1000€, de Juin à Aout, besoin de 2 places
EXEC RechercherCircuits @BudgetMax = 1000.00, @DateDebutVacances = '2026-06-01', @DateFinVacances = '2026-08-30', @NbPlacesNecessaires = 2;
GO


-- =========================================================================
-- QUESTION 7 : Supprimer une étape d’un circuit et renuméroter
-- =========================================================================
PRINT '--- Q7: Procédure SupprimerEtapeCircuit ---';
GO

CREATE OR ALTER PROCEDURE SupprimerEtapeCircuit
    @idCircuit INT,
    @ordreASupprimer INT
AS
BEGIN
    IF EXISTS(SELECT 1 FROM etape WHERE identifiant = @idCircuit AND ordre = @ordreASupprimer)
    BEGIN
        -- Suppression
        DELETE FROM etape WHERE identifiant = @idCircuit AND ordre = @ordreASupprimer;
        
        -- Renumérotation continue
        UPDATE etape SET ordre = ordre - 1 WHERE identifiant = @idCircuit AND ordre > @ordreASupprimer;
        
        PRINT CONCAT('L''étape ', @ordreASupprimer, ' du circuit ', @idCircuit, ' a été supprimée. Numérotation réajustée.');
    END
    ELSE
    BEGIN
        PRINT 'Erreur : Aucune étape trouvée à cet ordre.';
    END
END
GO

-- Test : on supprime l'étape 2 du circuit 7
EXEC SupprimerEtapeCircuit @idCircuit = 7, @ordreASupprimer = 2;
-- Vérification de la suite (ordre devrait être 1, 2, 3)
SELECT identifiant, ordre, nomlieu FROM etape WHERE identifiant = 7 ORDER BY ordre;
GO


-- =========================================================================
-- QUESTION 8 : Ajouter une réservation d’un circuit
-- =========================================================================
PRINT '--- Q8: Procédure AjouterReservation ---';
GO

CREATE OR ALTER PROCEDURE AjouterReservation
    @idCircuit INT,
    @idClient VARCHAR(50),
    @nbPlaces INT
AS
BEGIN
    DECLARE @placesRestantes INT;
    
    -- Savoir combien de places sont dispo
    SELECT @placesRestantes = nbrplacedisponible FROM Circuit WHERE identifiant = @idCircuit;
    
    IF @placesRestantes >= @nbPlaces
    BEGIN
        -- Ajout
        INSERT INTO reservation (identifiant, idclient, datereservation, nbplacedispo)
        VALUES (@idCircuit, @idClient, GETDATE(), @nbPlaces);
        
        -- Mise à jour des dispos dans la table Circuit (Important)
        UPDATE Circuit SET nbrplacedisponible = nbrplacedisponible - @nbPlaces WHERE identifiant = @idCircuit;
        
        PRINT CONCAT('Succès de la réservation ! ', @nbPlaces, ' place(s) réservée(s).');
    END
    ELSE
    BEGIN
        PRINT CONCAT('Échec de la réservation : il ne reste que ', COALESCE(@placesRestantes, 0), ' place(s).');
    END
END
GO

-- Test de la réservation pour le client C001 sur le circuit 7
EXEC AjouterReservation @idCircuit = 7, @idClient = 'C001', @nbPlaces = 3;
SELECT identifiant, nbrplacedisponible FROM Circuit WHERE identifiant = 7;
GO
