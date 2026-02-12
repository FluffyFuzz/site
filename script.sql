-- ---------------------------------------------
-- ----Creation de la base de donnees-----------
-- ---------------------------------------------


-- Supression des tables
DROP TABLE IF EXISTS ASSIGNATION;
DROP TABLE IF EXISTS INSCRIPTION;
DROP TABLE IF EXISTS MEDIA;
DROP TABLE IF EXISTS REUNION;
DROP TABLE IF EXISTS ACTUALITE;
DROP TABLE IF EXISTS COMPTABILITE;
DROP TABLE IF EXISTS ADHESION;
DROP TABLE IF EXISTS GRADE;
DROP TABLE IF EXISTS COMMANDE;
DROP TABLE IF EXISTS ARTICLE;
DROP TABLE IF EXISTS EVENEMENT;
DROP TABLE IF EXISTS ROLE;
DROP TABLE IF EXISTS MEMBRE;

-- Creation de la base

CREATE TABLE MEMBRE(
                       id_membre INT AUTO_INCREMENT,
                       nom_membre VARCHAR(100) NOT NULL,
                       prenom_membre VARCHAR(100) NOT NULL,
                       email_membre VARCHAR(100) NOT NULL,
                       password_membre VARCHAR(100) NOT NULL,
                       xp_membre INT NOT NULL DEFAULT 0,
                       discord_token_membre VARCHAR(500),
                       pp_membre VARCHAR(500) NOT NULL,
                       tp_membre VARCHAR(3),
                       PRIMARY KEY(id_membre)
);

CREATE TABLE ROLE(
                     id_role INT AUTO_INCREMENT,
                     nom_role VARCHAR(100) NOT NULL,
                     p_log_role BIT NOT NULL,
                     p_boutique_role BIT NOT NULL,
                     p_reunion_role BIT NOT NULL,
                     p_utilisateur_role BIT NOT NULL,
                     p_grade_role BIT NOT NULL,
                     p_roles_role BIT NOT NULL,
                     p_actualite_role BIT NOT NULL,
                     p_evenements_role BIT NOT NULL,
                     p_comptabilite_role BIT NOT NULL,
                     p_achats_role BIT NOT NULL,
                     p_moderation_role BIT NOT NULL,
                     PRIMARY KEY(id_role)
);

CREATE TABLE ARTICLE(
                        id_article INT AUTO_INCREMENT,
                        xp_article INT NOT NULL DEFAULT 1,
                        nom_article VARCHAR(100) NOT NULL,
                        stock_article INT NOT NULL,
                        image_article VARCHAR(500) NOT NULL,
                        categorie_article VARCHAR(100) NOT NULL DEFAULT 'Non défini',
                        reduction_article BIT NOT NULL DEFAULT 1,
                        prix_article FLOAT NOT NULL CHECK (prix_article >= 0),
                        PRIMARY KEY(id_article)
);

CREATE TABLE COMMANDE(
                         id_commande INT AUTO_INCREMENT,
                         statut_commande BIT NOT NULL,
                         prix_commande FLOAT NOT NULL CHECK (prix_commande >= 0),
                         paiement_commande VARCHAR(50) NOT NULL,
                         date_commande DATETIME NOT NULL,
                         qte_commande INT NOT NULL,
                         id_membre INT NOT NULL,
                         id_article INT NOT NULL,
                         PRIMARY KEY(id_commande),
                         FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre),
                         FOREIGN KEY(id_article) REFERENCES ARTICLE(id_article)
);

CREATE TABLE EVENEMENT(
                          id_evenement INT AUTO_INCREMENT,
                          nom_evenement VARCHAR(100) NOT NULL,
                          xp_evenement INT NOT NULL DEFAULT 10,
                          places_evenement INT NOT NULL,
                          prix_evenement INT NOT NULL,
                          reductions_evenement BIT NOT NULL DEFAULT 1,
                          lieu_evenement VARCHAR(50) NOT NULL,
                          date_evenement DATETIME NOT NULL,
                          image_evenement VARCHAR(500),
                          description_evenement VARCHAR(1000),
                          PRIMARY KEY(id_evenement)
);

CREATE TABLE COMPTABILITE(
                             id_comptabilite INT AUTO_INCREMENT,
                             date_comptabilite DATETIME NOT NULL,
                             nom_comptabilite VARCHAR(100) NOT NULL,
                             url_comptabilite VARCHAR(500) NOT NULL,
                             id_membre INT NOT NULL,
                             PRIMARY KEY(id_comptabilite),
                             FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre)
);

CREATE TABLE GRADE(
                      id_grade INT AUTO_INCREMENT,
                      reduction_grade INT NOT NULL,
                      image_grade VARCHAR(500) NOT NULL,
                      prix_grade INT NOT NULL CHECK (prix_grade >= 0),
                      description_grade VARCHAR(500),
                      nom_grade VARCHAR(100) NOT NULL,
                      PRIMARY KEY(id_grade)
);

CREATE TABLE ADHESION(
                         id_adhesion INT AUTO_INCREMENT,
                         date_adhesion DATETIME NOT NULL,
                         prix_adhesion INT NOT NULL,
                         paiement_adhesion VARCHAR(50) NOT NULL,
                         id_membre INT NOT NULL,
                         id_grade INT NOT NULL,
                         PRIMARY KEY(id_adhesion),
                         FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre),
                         FOREIGN KEY(id_grade) REFERENCES GRADE(id_grade)
);

CREATE TABLE MEDIA(
                      id_media INT AUTO_INCREMENT,
                      url_media VARCHAR(500) NOT NULL,
                      date_media DATETIME NOT NULL,
                      id_membre INT NOT NULL,
                      id_evenement INT NOT NULL,
                      PRIMARY KEY(id_media),
                      FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre),
                      FOREIGN KEY(id_evenement) REFERENCES EVENEMENT(id_evenement)
);

CREATE TABLE REUNION(
                        id_reunion INT AUTO_INCREMENT,
                        date_reunion DATETIME NOT NULL,
                        fichier_reunion VARCHAR(300),
                        id_membre INT NOT NULL,
                        PRIMARY KEY(id_reunion),
                        FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre)
);

CREATE TABLE ACTUALITE(
                          id_actualite INT AUTO_INCREMENT,
                          image_actualite VARCHAR(300),
                          titre_actualite VARCHAR(100) NOT NULL,
                          contenu_actualite VARCHAR(1000),
                          date_actualite DATETIME NOT NULL,
                          id_membre INT NOT NULL,
                          PRIMARY KEY(id_actualite),
                          FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre)
);

CREATE TABLE ASSIGNATION(
                            id_membre INT,
                            id_role INT,
                            PRIMARY KEY(id_membre, id_role),
                            FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre),
                            FOREIGN KEY(id_role) REFERENCES ROLE(id_role)
);

CREATE TABLE INSCRIPTION(
                            id_membre INT ,
                            id_evenement INT,
                            date_inscription DATETIME NOT NULL,
                            paiement_inscription VARCHAR(50) NOT NULL,
                            prix_inscription DECIMAL(15,2) NOT NULL,
                            PRIMARY KEY(id_membre, id_evenement),
                            FOREIGN KEY(id_membre) REFERENCES MEMBRE(id_membre),
                            FOREIGN KEY(id_evenement) REFERENCES EVENEMENT(id_evenement)
);



-- ---------------------------------------------
-- ---------Insertion des donnees---------------
-- ---------------------------------------------


-- Ajout des roles
INSERT INTO ROLE (nom_role, p_log_role, p_boutique_role, p_reunion_role, p_utilisateur_role, p_grade_role, p_roles_role, p_actualite_role, p_evenements_role, p_comptabilite_role, p_achats_role, p_moderation_role) VALUES
('referent', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
('president', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
('comptable', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
('superette', 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0),
('animateur', 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0),
('infos', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0);

-- Insertion des membres
INSERT INTO MEMBRE (nom_membre, prenom_membre, email_membre, password_membre, xp_membre, discord_token_membre, pp_membre, tp_membre) VALUES
('RUFFAULT--RAVENEL', 'Gemino', 'gemino.ruffault@example.com', 'password1', 50, NULL, 'http://files.bdeinfo.fr/fes2fse1f21se.jpg', '11A'),
('HANNIER', 'Axelle', 'axelle.hannier@example.com', 'password2', 18, NULL, 'http://files.bdeinfo.fr/fesfe43sf.jpg', '12C'),
('DAUVERGNE', 'Julien', 'julien.dauvergne@example.com', 'password3', 0, 'g4rd64g6rd4g8f4e64h5bv231h5th44g5ht6h87yj8ty6', 'http://files.bdeinfo.fr/gprdgrd5.jpg','31A'),
('DELAYE', 'Baptiste', 'baptiste.delahay@example.com', 'password4', 0, NULL, 'http://files.bdeinfo.fr/h5th42fth.jpg', '32D'),
('VIEILLARD', 'Nathalie', 'nathalie.vieillard@example.com', 'password5', 11, NULL, 'http://files.bdeinfo.fr/jygjgy56yjg.jpg', NULL),
('HAVARD', 'Barnabe', 'barnabe.havard@example.com', 'password6', 0, 'kiuilui4l8iul654hg2g', 'http://files.bdeinfo.fr/fesifo45ht45h.jpg', '11A'),
('FEVRIER', 'Theo', 'theo.fevrier@example.com', 'password7', 0, NULL, 'http://files.bdeinfo.fr/gr68grg.jpg', NULL),
('GOUIN', 'Tom', 'tom.gouin@example.com', 'password8', 12, NULL, 'http://files.bdeinfo.fr/fesf4556fe.jpg', NULL),
('CONGNARD', 'Evann', 'evann.congnard@example.com', 'password9', 0, NULL, 'http://files.bdeinfo.fr/2f1e2sfs.jpg', '31A'),
('LE COZ', 'Erwan', 'erwan.lecoz@example.com', 'password10', 0, NULL, 'http://files.bdeinfo.fr/fesf45ef6s4fes6.jpg', '31B');

-- Definition des roles
INSERT INTO ASSIGNATION (id_membre, id_role) VALUES
(5, 1),  -- Nathalie Vieillard devient referent
(1, 2),  -- Gemino RUFFAULT--RAVENEL devient president
(6, 6),  -- Barnabe HAVARD a le role "infos"
(3, 6),  -- Julien DAUVERGNE a le role "infos"
(6, 3);  -- Barnabe HAVARD a egalement le role "comptable"

-- Ajout des grades
INSERT INTO GRADE (reduction_grade, image_grade, prix_grade, description_grade, nom_grade) VALUES
(0, 'http://files.bdeinfo.fr/grade_fer.jpg', 5, 'Un grade de base en fer.', 'Fer'),
(0, 'http://files.bdeinfo.fr/grade_or.jpg', 10, 'Un grade sup�rieur en or.', 'Or'),
(10, 'http://files.bdeinfo.fr/grade_diamand.jpg', 13, 'Le grade ultime en diamant.', 'Diamant');

-- Insertion des adh�sions
INSERT INTO ADHESION (date_adhesion, prix_adhesion, paiement_adhesion, id_membre, id_grade) VALUES
('2024-05-02 11:44:18', 13, 'TPE', 7, 3),	 -- Theo Fevrier achete le grade Diamant
('2024-05-02 11:44:18', 10, 'PayPal', 8, 2),  -- Tom Gouin achete le grade Diamant
('2019-11-18 10:34:09', 8, 'TPE', 2, 2),	 -- Axelle HANNIER achete le grade or, elle paye uniquement 8e car il coutait moins cher a l'epoque
('2024-05-02 11:44:18', 5, 'Especes', 10, 1); -- Erwan le Coz achete le grade Fer

-- Ajout des articles de la boutique
INSERT INTO ARTICLE (xp_article, nom_article, stock_article, image_article, reduction_article, prix_article) VALUES
(1, 'Canette de Coca', 50, 'http://files.bdeinfo.fr/coca.jpg', 0, 1.50),
(1, 'Coca Cherry', 30, 'http://files.bdeinfo.fr/coca_cherry.jpg', 0, 1.75),
(20, 'Lipton Ice Tea', 40, 'http://files.bdeinfo.fr/lipton.jpg', 0, 1.50),
(1, 'Formule Cafe', 20, 'http://files.bdeinfo.fr/_cafe.jpg', 0, 2.00),
(1, 'Bueno White', 25, 'http://files.bdeinfo.fr/_bueno_white.jpg', 1, 1.20),
(1, 'Bueno', 35, 'http://files.bdeinfo.fr/bueno.jpg', 1, 1.20),
(10, 'Snack Chips', 60, 'http://files.bdeinfo.fr/chips.jpg', 0, 1.00),
(1, 'Barre de Chocolat', 0, 'http://files.bdeinfo.fr/chocolat.jpg', 0, 1.50),
(1, 'Jus d Orange', 55, 'http://files.bdeinfo.fr/jus_orange.jpg', 0, 1.30),
(30, 'Volvic', 70, 'http://files.bdeinfo.fr/volvic.jpg', 0, 0.80);

-- Ajout des commandes
INSERT INTO COMMANDE (statut_commande, prix_commande, paiement_commande, date_commande, qte_commande, id_membre, id_article) VALUES
(1, 3.00, 'Carte de credit', NOW(), 2, 1, 1),  -- Gemino achete 2 canettes de Coca
(0, 3.50, 'Especes', NOW(), 1, 2, 2),          -- Axelle achete 1 Coca Cherry
(0, 2.00, 'TPE', NOW(), 1, 3, 3),              -- Julien achete 1 Lipton Ice Tea
(0, 4.00, 'PayPal', NOW(), 2, 4, 4),           -- Barnabe achete 2 Formule Cafe
(0, 1.20, 'Carte de credit', NOW(), 5, 2, 5),  -- Axelle achete 5 Bueno White
(0, 1.20, 'Especes', NOW(), 10, 6, 6),         -- Theo achete 10 Bueno
(0, 1.00, 'TPE', NOW(), 3, 7, 7),              -- Tom achete 3 Snack Chips
(0, 4.50, 'Carte de credit', NOW(), 2, 8, 8),  -- Erwan achete 2 Barres de Chocolat
(1, 2.60, 'PayPal', NOW(), 2, 9, 9),           -- Evann achete 2 Jus d Orange
(1, 1.60, 'Especes', NOW(), 4, 10, 10);       -- Baptiste achete 4 Volvic

-- Ajout des evenements
INSERT INTO EVENEMENT (nom_evenement, xp_evenement, places_evenement, prix_evenement, reductions_evenement, lieu_evenement, date_evenement) VALUES
('LAN Minecraft', 10, 100, 20, 1, 'Amphi 1', '2024-09-15 10:00:00'),
('Competition CSGO', 10, 200, 15, 1, 'Amphi 1', '2024-10-20 14:00:00'),
('Raclette', 10, 30, 25, 0, 'TD2', '2024-11-05 18:30:00'),
('Loup Garou', 10, 150, 10, 1, 'TD2', '2024-12-10 19:00:00'),
('LAN Mario Kart', 10, 1, 5, 1, 'Amphi 3', '2025-01-15 15:00:00'),
('Barbecue de l''ADIIL', 10, 300, 10, 1, 'Parking du Batiment Dep. Informatique', '2025-05-01 12:00:00'),
('Raclette 2', 10, 75, 12, 0, 'TD2', '2025-05-20 18:30:00'),
('Course de Caddie Carrefour', 10, 100, 10, 1, 'Carrefour', '2025-06-10 10:00:00'),
('Soiree Bar', 10, 200, 15, 1, 'Bar l''After Work', '2025-06-20 20:00:00'),
('Barbecue de Depart', 10, 50, 30, 0, 'Centre de Conferences', '2025-07-01 12:00:00');


-- Ajout des inscriptions
INSERT INTO INSCRIPTION (id_membre, id_evenement, date_inscription, paiement_inscription, prix_inscription) VALUES
(1, 3, '2024-10-15 11:06:05', 'TPE', 25),
(4, 1, '2024-09-08 12:14:18', 'Paypal', 20),
(4, 6, '2025-04-26 09:04:05', 'Espece', 10),
(8, 5, '2025-01-13 17:26:32', 'Espece', 4.50),
(5, 10, '2025-06-15 14:31:56', 'TPE', 30),
(6, 8, '2024-05-12 8:56:01', 'Paypal', 10),
(7, 10, '2025-04-02 13:04:02', 'Carte de credit', 30),
(8, 1, '2024-09-13 16:16:45', 'TPE', 18),
(10, 9, '2024-06-19 10:08:00', 'TPE', 15),
(9, 7, '2025-05-05 13:02:18','Carte de credit', 12);


-- Ajout des actualites
INSERT INTO ACTUALITE (image_actualite, titre_actualite, contenu_actualite, date_actualite, id_membre) VALUES
('http://files.bdeinfo.fr/photoIntegration2024.jpg', 'Un soiree d''integration haute en couleur', 'Hier soir se tenait la soiree d''integration de notre chere BDE. Elle fut remarquable, nous en gardons un tres bon souvenir ! Merci a tous d''etre venu nombreux !', '2024-09-12 19:30:00', 1),
('http://files.bdeinfo.fr/photoNouvelleSalleEtude.jpg', 'Inauguration de la nouvelle salle d etude', 'La nouvelle salle d etude equipee de postes informatiques dernier cri est desormais ouverte a tous les etudiants. Venez la decouvrir !', '2024-09-18 10:15:00', 1),
('http://files.bdeinfo.fr/photoNouvelEquipementSport.jpg', 'Nouveau matariel sportif au gymnase', 'Le gymnase de l IUT a ete equipe de nouveaux appareils de musculation. Une bonne nouvelle pour les amateurs de sport.', '2024-09-25 14:45:00', 1),
('http://files.bdeinfo.fr/photoPartenariatEntreprise.jpg', 'Partenariat avec une entreprise locale', 'Un nouveau partenariat a ete signe entre l IUT et TechMayenne entreprise specialisee dans le developpement d applications web. De belles opportunites de stages en perspective.', '2024-10-01 09:00:00', 1),
('http://files.bdeinfo.fr/photoRenovationBibliotheque.jpg', 'Renovation de la bibliotheque', 'La bibliotheque de l IUT a subi une renovation complete et propose desormais plus d espace et de nouveaux ouvrages en informatique.', '2024-10-05 11:20:00', 1),
('http://files.bdeinfo.fr/photoResultatsConcoursTech.jpg', 'Resultats du concours de technologie', 'Les resultats du concours de technologie viennent de tomber ! Felicitations a tous les participants, et en particulier aux vainqueurs du departement informatique Theo et Alban.', '2024-10-10 16:00:00', 1),
('http://files.bdeinfo.fr/photoSemaineIntegration.jpg', 'Retour sur la semaine d integration', 'La semaine d integration s est achevee avec succes. Merci a tous ceux qui ont contribue a rendre ces moments inoubliables pour les nouveaux etudiants.', '2024-10-15 18:50:00', 1),
('http://files.bdeinfo.fr/photoNouveauSiteWeb.jpg', 'Lancement du nouveau site web du BDE', 'Le BDE est fier de vous annoncer le lancement de son nouveau site web, entierement repense pour faciliter l acces aux informations et evenements.', '2024-10-20 13:30:00', 1),
('http://files.bdeinfo.fr/photoCollecteVetements.jpg', 'Collecte de vetements reussie', 'La collecte de vetements organisee par le BDE a permis de rassembler plus de 200 kg de vetements qui seront distribues a des associations locales.', '2024-10-22 10:45:00', 1),
('http://files.bdeinfo.fr/photoConferenceInnovations.jpg', 'Conference sur les innovations technologiques', 'Une conference sur les innovations technologiques recentes s est tenue a l IUT. Des intervenants de renom ont partage leurs experiences avec les etudiants.', '2024-10-22 17:15:00', 1);

-- Ajout de la comptabilite
INSERT INTO COMPTABILITE (date_comptabilite, nom_comptabilite, url_comptabilite, id_membre) VALUES
('2024-03-05', 'Compta fev2024', 'http://files.bdeinfo.fr/comptaFev2024.xls', 6),
('2023-12-10', 'Compta nov2023', 'http://files.bdeinfo.fr/comptaNov2023.xls', 6),
('2024-01-07', 'Compta dec2023', 'http://files.bdeinfo.fr/comptaDec2023.xls', 6),
('2024-02-05', 'Compta janv2024', 'http://files.bdeinfo.fr/comptaJanv2024.xls', 6),
('2024-04-10', 'Compta mars2024', 'http://files.bdeinfo.fr/comptaMars2024.xls', 6),
('2024-05-07', 'Compta avril2024', 'http://files.bdeinfo.fr/comptaAvril2024.xls', 6),
('2024-06-10', 'Compta mai2024', 'http://files.bdeinfo.fr/comptaMai2024.xls', 6),
('2024-07-05', 'Compta juin2024', 'http://files.bdeinfo.fr/comptaJuin2024.xls', 6),
('2024-08-12', 'Compta juillet2024', 'http://files.bdeinfo.fr/comptaJuillet2024.xls', 6),
('2024-09-09', 'Compta aout2024', 'http://files.bdeinfo.fr/comptaAout2024.xls', 6);


-- Ajout des reunions
INSERT INTO REUNION (date_reunion, fichier_reunion, id_membre) VALUES
('2024-09-08', 'http://files.bdeinfo.fr/CR433.odt', 3),
('2024-09-15', 'http://files.bdeinfo.fr/CR434.odt', 5),
('2024-09-22', 'http://files.bdeinfo.fr/CR435.odt', 6),
('2024-09-29', 'http://files.bdeinfo.fr/CR436.odt', 3),
('2024-10-06', 'http://files.bdeinfo.fr/CR437.odt', 5),
('2024-10-13', 'http://files.bdeinfo.fr/CR438.odt', 6),
('2024-10-20', 'http://files.bdeinfo.fr/CR439.odt', 3),
('2024-10-27', 'http://files.bdeinfo.fr/CR440.odt', 5),
('2024-11-03', 'http://files.bdeinfo.fr/CR441.odt', 6),
('2024-11-10', 'http://files.bdeinfo.fr/CR442.odt', 3);


-- Ajout des medias
INSERT INTO MEDIA (url_media, date_media, id_membre, id_evenement) VALUES
('http://files.bdeinfo.fr/hjrehr.mp4', '2024-10-21', 3, 2),
('http://files.bdeinfo.fr/fhir.jpeg', '2024-10-21', 5, 2),
('http://files.bdeinfo.fr/uyjhghg.mp4', '2024-11-05', 6, 3),
('http://files.bdeinfo.fr/rtuhght.jpeg', '2024-09-17', 3, 1),
('http://files.bdeinfo.fr/ytraztru.mp4', '2024-12-13', 8, 4),
('http://files.bdeinfo.fr/rtghyy.jpeg', '2024-10-11', 6, 4),
('http://files.bdeinfo.fr/ythghtr.mp4', '2025-01-15', 3, 5),
('http://files.bdeinfo.fr/tuhyy.jpeg', '2024-09-18', 10, 1),
('http://files.bdeinfo.fr/reyhy.mp4', '2025-05-02', 6, 6),
('http://files.bdeinfo.fr/yryert.jpeg', '2024-05-02', 9, 6);


-- -------------------------------------------------------------------------------------
-- --------------------Commandes de l'administrateur------------------------------------
-- -------------------------------------------------------------------------------------


/***************************************************/
/*ADMINISTRATEUR : Afficher l'historique des achats*/
/***************************************************/

/* Contexte : un administrateur souhaite consulter l'historique des achats*/

/*Fonctionnement : On affiche l'enesmble des commandes d'articles, des
inscriptions aux evenements et des adhesions à des grades au travers d'une
seule vue.
De ce fait les 3 commandes SELECT doivent contenir les mêmes types et
libelles de données
Pour une meilleurs lisibilit, on affiches ces achats du plus recent
au plus lointain*/

DROP VIEW IF EXISTS HISTORIQUE;


CREATE VIEW HISTORIQUE AS
SELECT
    'Commande' AS type_transaction,
    ARTICLE.nom_article AS element,
	COMMANDE.qte_commande AS quantite,
    MEMBRE.nom_membre AS nom_utilisateur,
	MEMBRE.prenom_membre AS prenom_membre,
	COMMANDE.statut_commande AS recupere,
    COMMANDE.date_commande AS date_transaction,
    COMMANDE.paiement_commande AS mode_paiement,
    COMMANDE.prix_commande AS montant
FROM COMMANDE
INNER JOIN ARTICLE ON ARTICLE.id_article = COMMANDE.id_article
INNER JOIN MEMBRE ON MEMBRE.id_membre = COMMANDE.id_membre

UNION ALL -- Permet de joindre le resultat de deux reqetes SELECT

SELECT
    'Inscription' AS type_transaction,
    EVENEMENT.nom_evenement AS element,
	1 AS quantite,
    MEMBRE.nom_membre AS utilisateur,
	MEMBRE.prenom_membre AS prenom_membre,
	1 AS recupere,
    INSCRIPTION.date_inscription AS date_transaction,
    INSCRIPTION.paiement_inscription AS mode_paiement,
    INSCRIPTION.prix_inscription AS montant
FROM INSCRIPTION
INNER JOIN EVENEMENT ON EVENEMENT.id_evenement = INSCRIPTION.id_evenement
INNER JOIN MEMBRE ON MEMBRE.id_membre = INSCRIPTION.id_membre

UNION ALL

SELECT
    'Adhesion' AS type_transaction,
    GRADE.nom_grade AS element,
	1 AS quantite,
    MEMBRE.nom_membre AS nom_utilisateur,
	MEMBRE.prenom_membre AS prenom_membre,
	1 AS recupere,
    ADHESION.date_adhesion AS date_transaction,
    ADHESION.paiement_adhesion AS mode_paiement,
    ADHESION.prix_adhesion AS montant
FROM ADHESION
INNER JOIN GRADE ON GRADE.id_grade = ADHESION.id_grade
INNER JOIN MEMBRE ON MEMBRE.id_membre = ADHESION.id_membre;



/* Test */
SELECT * FROM HISTORIQUE ORDER BY date_transaction;



/*******************************************************************/
/*ADMINISTRATEUR : Afficher la liste des permissions de chaque r�le*/
/******************************************************************/
DROP VIEW IF EXISTS LISTE_PERMISSIONS;

CREATE VIEW LISTE_PERMISSIONS
AS
       -- Explications :
       -- Un LEFT JOIN est utilise pour s'assurer que tous les utilisateurs sont affiches dans la table
       -- Dans le cadre ou un utilisateur n'a pas de role (= une ligne de NULL), on les remplace par des 0 (= aucune permission).
       -- Les permissions sont stockees sous forme de BIT, on les transforme en 1 ou 0 sous forme d'entiers
       -- Ensuite, on prends la plus grande valeur. Si c'est 0, il n'a pas la permission, si c'est 1, il l'a.
SELECT MEMBRE.id_membre,
       MAX(CAST(COALESCE(p_log_role, 0) AS INT))          AS 'Acces aux logs',
       MAX(CAST(COALESCE(p_boutique_role, 0) AS INT)) AS 'Gestion de la boutique',
       MAX(CAST(COALESCE(p_reunion_role, 0) AS INT))  AS 'Gestion des reunions',
       MAX(CAST(COALESCE(p_utilisateur_role, 0) AS INT))AS 'Gestion des utilisateurs',
       MAX(CAST(COALESCE(p_grade_role, 0) AS INT))        AS 'Gestion des grades',
       MAX(CAST(COALESCE(p_roles_role, 0) AS INT))        AS 'Gestion des roles',
       MAX(CAST(COALESCE(p_actualite_role, 0) AS INT))    AS 'Gestion des actualites',
       MAX(CAST(COALESCE(p_evenements_role, 0) AS INT))   AS 'Gestion des evenements',
       MAX(CAST(COALESCE(p_comptabilite_role, 0) AS INT)) AS 'Gestion de la comptabilite',
       MAX(CAST(COALESCE(p_achats_role, 0) AS INT))       AS 'Acces aux achats',
       MAX(CAST(COALESCE(p_moderation_role, 0) AS INT))   AS 'Moderation'
FROM MEMBRE
         LEFT JOIN ASSIGNATION ON MEMBRE.id_membre = ASSIGNATION.id_membre
         LEFT JOIN ROLE ON ASSIGNATION.id_role = ROLE.id_role
GROUP BY MEMBRE.id_membre;


/* Test */
SELECT * FROM LISTE_PERMISSIONS; -- Liste des permissions pour tous les membres
SELECT * FROM LISTE_PERMISSIONS WHERE id_membre = 2; -- Liste des permissions pour le membre 2







/*******************************************************************/
/*ADMINISTRATEUR : Ajouter un role  un utilisateur*/
/******************************************************************/
INSERT into ASSIGNATION values (2, 4);
SELECT * FROM LISTE_PERMISSIONS WHERE id_membre = 2;






/***************************************/
/*ADMINISTRATEUR : ANNULER une commande*/
/***************************************/

/* Contexte : un administrateur souhaite annuler une commande
faite par un membre*/

/*Fonctionnement : Cette procedure supprime la commande de la liste
des commandes, annule l'augmentation de l'XP du membre et reaugment 
les stocks de l'article convcerne par la commande*/

DROP PROCEDURE IF EXISTS refund_transaction;

DELIMITER $$

CREATE PROCEDURE refund_transaction
(IN _id_commande INT)
BEGIN
        DECLARE _id_article INT;
        DECLARE _id_membre INT;
        DECLARE _xp_article INT;
        DECLARE _qtty_bought INT;
        
        SET _id_membre = (SELECT id_membre FROM COMMANDE WHERE id_commande = _id_commande);
        SET _id_article = (SELECT id_article FROM COMMANDE WHERE id_commande = _id_commande);
        SET _qtty_bought = (SELECT qte_commande FROM COMMANDE WHERE id_commande = _id_commande);
        SET _xp_article = (SELECT xp_article FROM ARTICLE WHERE id_article = _id_article);
        
        UPDATE MEMBRE SET xp_membre = xp_membre - _xp_article * _qtty_bought WHERE id_membre = _id_membre;
        UPDATE ARTICLE SET stock_article = stock_article + _qtty_bought WHERE id_article = _id_article;
        
        DELETE FROM COMMANDE WHERE id_commande = _id_commande;
END$$

DELIMITER ;


/* Test */
SELECT * FROM COMMANDE WHERE id_commande = 5; -- On souhaite annuler la commande 5 faite par le membre 2
SELECT * FROM ARTICLE WHERE id_article = 5; -- Avant l'annulation, il restait 25 exemplaires de l'article concerne par la commande
SELECT * FROM MEMBRE WHERE id_membre = 2;-- Avant l'annulation de la commande le membre 2 avait 18 d'XP
CALL refund_transaction(5); -- On annule la commande 5
SELECT * FROM COMMANDE WHERE id_commande = 5; -- la commande a bien ete annule
SELECT * FROM ARTICLE WHERE id_article = 5; -- Les stocks de l'article sont bien augmentes
SELECT * FROM MEMBRE WHERE id_membre = 2; -- Le membre a bien ete deduit des XP qu'il avait gagne avec la commande



/****************************************************************/
/*ADMINISTRATEUR : Supprimer un evenement et la galerie associee*/
/****************************************************************/

/* Contexte : Une administrateur supprime un evenement. Les images
associees doivent donc etre supprimees*/

/*Fonctionnement : On recupere l'ID de l'article qui va etre supprime,
on supprime toutes les images associees a l'evenement, puis on supprime
l'evenement*/

DROP PROCEDURE IF EXISTS delete_event;

DELIMITER $$
CREATE PROCEDURE delete_event
(IN _id_event INT)
    BEGIN
        DELETE FROM MEDIA WHERE id_evenement = _id_event;
		DELETE FROM EVENEMENT WHERE id_evenement = _id_event;
    END$$

DELIMITER ;
/* Test */
SELECT * FROM MEDIA WHERE id_evenement = 2;    -- Deux memdias sont en lien avec l'evenement
CALL delete_event(2); --  On supprime l'evenement
SELECT * FROM MEDIA WHERE id_evenement = 2;    -- Les deux medias ont été supprimes





/*****************************************************************************************/
/*ADMINISTRATEUR : Verifier qu'un utilisateur a les permissions pour creer une actualite */
/*****************************************************************************************/

/* Contexte : Une administrateur souhaite creer une actualites*/

/*Fonctionnement : Apr�s l'insertion d'un evenement on verifie que
l'utilisateur a les permissions pour le faire sinon on annule*/

DROP TRIGGER IF EXISTS permissions_create_event;

DELIMITER $$
CREATE TRIGGER permissions_create_event AFTER INSERT ON ACTUALITE FOR EACH ROW
	BEGIN
		DECLARE _user_id INT;
		DECLARE _has_perms INT;
		SET _user_id = NEW.id_membre;
		SET _has_perms = (SELECT `Gestion des actualites` FROM LISTE_PERMISSIONS WHERE id_membre = _user_id);

		IF (_has_perms = 0) THEN
			-- ROLLBACK TRANSACTION n'existe pas en MySQL, on utilise donc une erreur pour annuler l'insertion
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vous n''avez pas les permissions pour ajouter une actualite';
		END IF;
	END$$

DELIMITER ;

/* Test */
-- Le membre 2 n'a pas les permissions pour ajouter une actualit� donc l'actualit� ne va pas �tre cr��e
-- INSERT INTO ACTUALITE (image_actualite, titre_actualite, contenu_actualite, date_actualite, id_membre) VALUES ('http://files.bdeinfo.fr/lougarou2024.jpg', 'Un loup garou qui a fait son effet', 'Hier soir a eu lieu le goup garou annuel et le moins qu''on puisse dire c''est qu''on va recommencer et bientot!', '2024-10-25 19:45:00', 2);
-- SELECT* FROM ACTUALITE;

-- Le membre 1 a les permissions pour ajouter une actualit� donc l'actualit� va bien �tre cr��e
-- INSERT INTO ACTUALITE (image_actualite, titre_actualite, contenu_actualite, date_actualite, id_membre) VALUES ('http://files.bdeinfo.fr/lougarou2024.jpg', 'Un loup garou qui a fait son effet', 'Hier soir a eu lieu le goup garou annuel et le moins qu''on puisse dire c''est qu''on va recommencer et bientot!', '2024-10-25 19:45:00', 1);
-- SELECT* FROM ACTUALITE;










-- -------------------------------------------------------------------------------------
-- -----------------------Commandes du membre-------------------------------------------
-- -------------------------------------------------------------------------------------




/************************************************************************/
/*Membre : Ajouter un media (photo ou vid�o) � la galerie d'un �v�nement*/
/************************************************************************/

/* Contexte : un membre souhaite ajouter une photo ou une vid�o � la galerie.*/

/*Fonctionnement : on fait une insertion dans media*/

INSERT INTO MEDIA (url_media, date_media, id_membre, id_evenement) VALUES('https://bdeinfo.fr/image_345.png', '2024-10-23', 3, 1);

/* Test */
SELECT * FROM MEDIA; -- l'image a bien �t� ajout�e




/*****************************************************************/
/*Membre : Voir ses son profil avec ses informations personnelles*/
/*****************************************************************/

/* Contexte : Cette commande sera utilis�e lorsque le membre acc�dera 
� son profil. 
On r�cup�re les informations � afficher dans la page web.*/

/*Fonctionnement : on fait un SELECT des informations n�cessaires dans membre*/

SELECT MEMBRE.prenom_membre, MEMBRE.nom_membre, MEMBRE.email_membre, MEMBRE.pp_membre, MEMBRE.xp_membre, MEMBRE.tp_membre from MEMBRE where MEMBRE.id_membre = 3;




/************************************************************************/
/*Membre : Voir les m�dias partag� par un utilisateur pour un �v�nements*/
/************************************************************************/

/* Contexte : On souhaite qu'un membre puisse voir les m�dias qu'il a 
partag� pour un �v�nement */

/*Fonctionnement : on affiche les m�dias d'un utilisateur pour un �v�nement*/

SELECT MEDIA.url_media from MEDIA WHERE MEDIA.id_membre = 3 AND MEDIA.id_evenement = 2;






/*******************************************************************/
/*Membre : Achat d'articles par un membre*/
/******************************************************************/

/* Contexte : un membre souhaite acheter des articles sur la boutique.*/

drop procedure if exists achat_article;

DELIMITER $$

create procedure achat_article(
    IN _id_membre_acheteur INT,
    IN _id_article_achat INT,
    IN _quantite INT,
    IN _mode_paiement VARCHAR(50)
)
BEGIN

 	DECLARE _prix_art INT;
 	DECLARE _reduc_grade float;
 	DECLARE _xp_gagne int;
    DECLARE _is_reductible BOOL;

 	-- On recupere l'xp gagné par article, son prix unitaire et si les reductions sont applicable sur l'article.
 	set _xp_gagne = (select ARTICLE.xp_article from ARTICLE where ARTICLE.id_article = _id_article_achat);
 	set _prix_art = (select ARTICLE.prix_article from ARTICLE where ARTICLE.id_article = _id_article_achat);
 	set _is_reductible = (select ARTICLE.reduction_article from ARTICLE where ARTICLE.id_article = _id_article_achat);
    
 	-- On recupere la reduction applicable du grade, en prenant le Grade le plus recent du membre
 	set _reduc_grade = (
    	select GRADE.reduction_grade from GRADE
    	join ADHESION on ADHESION.id_grade = GRADE.id_grade
    	where ADHESION.date_adhesion = (select MAX(date_adhesion) from ADHESION where ADHESION.id_membre = _id_membre_acheteur)
    	and ADHESION.id_membre = _id_membre_acheteur);

 	-- RETOUR :
   	-- NULL ou 0 si, respectivement, le membre ne poss�de de grade ou que son grade n'offre pas de r�duction.
   	-- Ou alors la valeur de la r�duction en pourcentage.

 	-- Par consequent, on convertit le pourcentage en valeur multipliable.
 	if (_reduc_grade IS NULL OR _reduc_grade = 0 OR _is_reductible = 0) THEN
            SET _reduc_grade = 1;
 	ELSE
 	        set _reduc_grade = 1-_reduc_grade/100;
    END IF;

 	-- Insertion
 	insert into COMMANDE
    	(statut_commande, prix_commande, paiement_commande, date_commande, qte_commande, id_membre, id_article)
 	VALUES
    	(0, (_prix_art*_reduc_grade)* _quantite, _mode_paiement, NOW(), _quantite, _id_membre_acheteur, _id_article_achat);

 	-- Ajout d'XP
 	update MEMBRE set MEMBRE.xp_membre = MEMBRE.xp_membre + (_quantite*_xp_gagne) where MEMBRE.id_membre = _id_membre_acheteur;

	-- Baisse des stocks
 	update ARTICLE set ARTICLE.stock_article = ARTICLE.stock_article - _quantite where ARTICLE.id_article = _id_article_achat;
end$$

DELIMITER ;



/* Test */

CALL achat_article(7, 6, 1, 'TPE'); -- Un membre ayant le grade diamant ach�te un article reductible.
select * from COMMANDE; -- la reduction a ete appliquee sur le prix paye


select MEMBRE.xp_membre from MEMBRE where MEMBRE.id_membre = 7; -- Ce membre a pour le moment 1 d'XP
SELECT ARTICLE.stock_article FROM ARTICLE WHERE ARTICLE.id_article = 1; -- Le stock de l'article est de 50

CALL achat_article(7, 1, 2, 'TPE'); -- Un membre ayant le grade diamant ach�te un article non-reductible.
select * from COMMANDE; -- la reduction n'a pas ete applique sur le prix paye

select MEMBRE.xp_membre from MEMBRE where MEMBRE.id_membre = 7; -- l XP a ete credite
SELECT ARTICLE.stock_article FROM ARTICLE WHERE ARTICLE.id_article = 1; -- Le stock a baisse de 2





/*******************************************************************/
/*Membre : Supprimer son compte utilisateur*/
/******************************************************************/

/* Contexte : un membre souhaite supprimer son compte.*/

/*Fonctionnement : On supprime, les donnees personnelles de l'utilisateurs
 et les medias qu'il a partage de ce fait les administrateur ne pas faire
 un delete de membre permet aux administrateur d'avoir une meilleure 
 lisibilite de l'historique des achats*/

DROP PROCEDURE IF EXISTS suppressionCompte;

DELIMITER $$

CREATE PROCEDURE suppressionCompte
(IN _id_utilisateur_supprime INT)
BEGIN
	-- Dans un soucis de suivie des donnees on conserve l'identifiant du membre mais on enleve/anonymise ses donnees personnelles
	UPDATE MEMBRE SET nom_membre='N/A', prenom_membre= 'N/A', email_membre='N/A', password_membre ='N/A', xp_membre = 0, discord_token_membre = 'N/A', pp_membre ='N/A' WHERE id_membre = _id_utilisateur_supprime;
	-- En revanche les medias partages par le membre sont definitivement supprimees
	DELETE FROM MEDIA WHERE id_membre = _id_utilisateur_supprime;
	-- De meme le membre perd ses roles
	DELETE FROM ASSIGNATION WHERE id_membre = _id_utilisateur_supprime;
END$$

DELIMITER ;


/* Test */
CALL suppressionCompte(5); -- Le membre ayant l'identifiant 5 souhaite supprimer son compte



/*******************************************************************/
/*Membre : Annuler l'inscription si pas assez de place */
/******************************************************************/

/* Contexte : un membre souhaite s'inscrire a un evenement.*/

/*Fonctionnement : Apres l'insertion le SGBD annule l'inscription
automatiquement si l'evenements est deja complet. */

DROP trigger IF EXISTS verif_places_event;

DELIMITER $$

CREATE TRIGGER verif_places_eventb AFTER INSERT ON INSCRIPTION FOR EACH ROW
BEGIN
    DECLARE _id_evenement_inscription INT;
    DECLARE _places_restantes INT;

    SET _id_evenement_inscription = NEW.id_evenement;

    -- Calcul des places restantes
    SET _places_restantes = (
        SELECT EVENEMENT.places_evenement - COUNT(*)
        FROM EVENEMENT
                 JOIN INSCRIPTION ON INSCRIPTION.id_evenement = EVENEMENT.id_evenement
        WHERE EVENEMENT.id_evenement = _id_evenement_inscription
        GROUP BY EVENEMENT.id_evenement, EVENEMENT.places_evenement
    );

    IF _places_restantes <= 0 THEN
        -- ROLLBACK TRANSACTION n'existe pas sur MySQL, on utilise donc une erreur pour annuler l'insertion
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Il n''y a plus de places disponibles pour cet evenement';
    END IF;
END$$

DELIMITER ;


/* Test */
SELECT EVENEMENT.places_evenement - COUNT(*) as places_restantes from EVENEMENT
            join INSCRIPTION on INSCRIPTION.id_evenement = EVENEMENT.id_evenement
            group by EVENEMENT.id_evenement, EVENEMENT.places_evenement
            having EVENEMENT.id_evenement = 5;

-- insert into INSCRIPTION VALUES(2, 5, NOW(), 'PAYPAL', 12); -- Erreur: transaction annuler parce que pas assez de places

SELECT EVENEMENT.places_evenement - COUNT(*) as places_restantes from EVENEMENT
            join INSCRIPTION on INSCRIPTION.id_evenement = EVENEMENT.id_evenement
            group by EVENEMENT.id_evenement, EVENEMENT.places_evenement
            having EVENEMENT.id_evenement = 5;








-- -------------------------------------------------------------------------------------
-- -----------------------Commandes du visiteur-----------------------------------------
-- -------------------------------------------------------------------------------------


/*****************************************************************************************************************************/
/*Visiteur : Afficher la liste des evenments et le nombre de place disponible*/
/*****************************************************************************************************************************/

/* Contexte : un visiteur souhaite consulter la liste 
des evenements et leurs informations.*/

/*Fonctionnement : On affiche les informations de la 
table evenement et on compte le nombre de places
restantes pour chaque evenement
De plus on trie les evenements par anciennete*/


SELECT 
    EVENEMENT.nom_evenement, 
	 -- On deduit du nombre total de places, le nombre d'inscription pour obtenir le nombre de places encore disponible pour l'evenement
    (EVENEMENT.places_evenement - (SELECT COUNT(*) FROM INSCRIPTION WHERE INSCRIPTION.id_evenement = EVENEMENT.id_evenement)) AS Place_disponibles,
    EVENEMENT.xp_evenement, 
    EVENEMENT.reductions_evenement, 
    EVENEMENT.places_evenement, 
    EVENEMENT.prix_evenement, 
    EVENEMENT.lieu_evenement, 
    EVENEMENT.date_evenement 
FROM 
    EVENEMENT
ORDER BY 
    EVENEMENT.date_evenement DESC;




/*******************************************************/
/*Visiteur : Afficher la liste des articles disponibles*/

/******************************************************/
/* Contexte : un visiteur souhaite consulter la liste 
des articles disponibles.*/

/*Fonctionnement : On affiche les informations de la 
table article et on verifie qu'il y a au moins un
exemplaire de chaque article en stock
De plus, pour une meilleur lecture on trie les 
articles dans l'ordre alphabetique*/

SELECT nom_article, xp_article, reduction_article, prix_article FROM ARTICLE WHERE STOCK_article > 0 ORDER BY nom_article;



/*******************************************************/
/*Visiteur : Rechercher une actualite par titre*/

/******************************************************/
/* Contexte : un visiteur souhaite voir les actualit�s 
dont le titre contient un certain mot cl�.*/

/*Fonctionnement : On affiche toutes les actualit�s dont le titre contient le mot cl�*/

SELECT titre_actualite, image_actualite, date_actualite FROM ACTUALITE WHERE titre_actualite LIKE '%integration%' ORDER BY titre_actualite;

SELECT * FROM ACTUALITE;



/*******************************/
/*Visiteur : Cr�ation de compte*/
/*******************************/

/* Contexte : un visiteurs souhaite cr�er un compte .*/

/*Fonctionnement : On affiche toutes les actualit�s dont le titre contient le mot cl�*/


DROP PROCEDURE IF EXISTS creationCompte;

DELIMITER $$

CREATE PROCEDURE creationCompte
(
    IN _name_user VARCHAR(100),
    IN _firstName_user VARCHAR(100),
    IN _email_user VARCHAR(100),
    IN _password_user VARCHAR(100),
    IN _pp_user VARCHAR(500)
)

BEGIN
	IF NOT EXISTS (SELECT * FROM MEMBRE WHERE email_membre = _email_user) THEN
		INSERT INTO MEMBRE (nom_membre, prenom_membre, email_membre, password_membre, pp_membre) VALUES (_name_user, _firstName_user, _email_user, _password_user, _pp_user);
	END IF;
END$$

DELIMITER ;


CALL creationCompte('HANNIER', 'axelle', 'axelle.hannier@example.com', 'krjtj4jykjyi8oi', 'http://files.bdeinfo.fr/defaultPP.jpg');
CALL creationCompte('DUPONT', 'jean', 'dupont.jean@example.com', 'hjhrethe2454rrhit', 'http://files.bdeinfo.fr/defaultPP.jpg');

SELECT*FROM MEMBRE;


-- fixs for deployment
ALTER TABLE EVENEMENT
    ADD COLUMN deleted BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE GRADE
    ADD COLUMN deleted BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE ARTICLE
    ADD COLUMN deleted BOOLEAN NOT NULL DEFAULT FALSE;


DROP VIEW IF EXISTS LISTE_PERMISSIONS;

CREATE VIEW LISTE_PERMISSIONS AS
SELECT 
    MEMBRE.id_membre,

    MAX(CAST(COALESCE(p_log_role, 0) AS UNSIGNED))          AS p_log,
    MAX(CAST(COALESCE(p_boutique_role, 0) AS UNSIGNED))     AS p_boutique,
    MAX(CAST(COALESCE(p_reunion_role, 0) AS UNSIGNED))      AS p_reunion,
    MAX(CAST(COALESCE(p_utilisateur_role, 0) AS UNSIGNED))  AS p_utilisateur,
    MAX(CAST(COALESCE(p_grade_role, 0) AS UNSIGNED))        AS p_grade,
    MAX(CAST(COALESCE(p_roles_role, 0) AS UNSIGNED))        AS p_role,
    MAX(CAST(COALESCE(p_actualite_role, 0) AS UNSIGNED))    AS p_actualite,
    MAX(CAST(COALESCE(p_evenements_role, 0) AS UNSIGNED))   AS p_evenement,
    MAX(CAST(COALESCE(p_comptabilite_role, 0) AS UNSIGNED)) AS p_comptabilite,
    MAX(CAST(COALESCE(p_achats_role, 0) AS UNSIGNED))       AS p_achat,
    MAX(CAST(COALESCE(p_moderation_role, 0) AS UNSIGNED))   AS p_moderation

FROM MEMBRE
LEFT JOIN ASSIGNATION ON MEMBRE.id_membre = ASSIGNATION.id_membre
LEFT JOIN ROLE        ON ASSIGNATION.id_role = ROLE.id_role
GROUP BY MEMBRE.id_membre;

UPDATE MEMBRE SET password_membre = '$2y$10$4ZyDaDMApbY0w8RBahD6m.CPxJ/5Gaqojoql/6XPwnzN0fkg1R4zq';
