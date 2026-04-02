CREATE TABLE IF NOT EXISTS `Utilisateur` (
  `Id_user` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(50) DEFAULT NULL,
  `Prenom` varchar(50) DEFAULT NULL,
  `Date_naissance` date DEFAULT NULL,
  `Formation` varchar(50) DEFAULT NULL,
  `Description` text,
  `Email` varchar(50) DEFAULT NULL,
  `Password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Role` tinyint DEFAULT NULL,
  `est_gere_par` int DEFAULT NULL,
  PRIMARY KEY (`Id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Entreprise` (
  `Id_entreprise` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(50) DEFAULT NULL,
  `Description` text,
  `Email_contact` varchar(50) DEFAULT NULL,
  `Telephone` varchar(50) DEFAULT NULL,
  `Est_actif` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`Id_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Competence` (
  `Id_competence` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Offre` (
  `Id_offre` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(50) DEFAULT NULL,
  `Description` text,
  `Remuneration` decimal(15,2) DEFAULT NULL,
  `Date_offre` date DEFAULT NULL,
  `Duree_mois` int DEFAULT NULL,
  `Nombre_place` tinyint DEFAULT NULL,
  `Id_entreprise` int NOT NULL,
  PRIMARY KEY (`Id_offre`),
  KEY `Id_entreprise` (`Id_entreprise`),
  CONSTRAINT `Offre_ibfk_1` FOREIGN KEY (`Id_entreprise`) REFERENCES `Entreprise` (`Id_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Candidater` (
  `Id_user` int NOT NULL,
  `Id_offre` int NOT NULL,
  `Cv` varchar(50) DEFAULT NULL,
  `Date_` datetime DEFAULT NULL,
  `LM` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Id_user`,`Id_offre`),
  KEY `Id_offre` (`Id_offre`),
  CONSTRAINT `Candidater_ibfk_1` FOREIGN KEY (`Id_user`) REFERENCES `Utilisateur` (`Id_user`),
  CONSTRAINT `Candidater_ibfk_2` FOREIGN KEY (`Id_offre`) REFERENCES `Offre` (`Id_offre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Evaluer` (
  `Id_user` int NOT NULL,
  `Id_entreprise` int NOT NULL,
  `Note` int DEFAULT NULL,
  PRIMARY KEY (`Id_user`,`Id_entreprise`),
  KEY `Id_entreprise` (`Id_entreprise`),
  CONSTRAINT `Evaluer_ibfk_1` FOREIGN KEY (`Id_user`) REFERENCES `Utilisateur` (`Id_user`),
  CONSTRAINT `Evaluer_ibfk_2` FOREIGN KEY (`Id_entreprise`) REFERENCES `Entreprise` (`Id_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Requiert` (
  `Id_competence` int NOT NULL,
  `Id_offre` int NOT NULL,
  PRIMARY KEY (`Id_competence`,`Id_offre`),
  KEY `Id_offre` (`Id_offre`),
  CONSTRAINT `Requiert_ibfk_1` FOREIGN KEY (`Id_competence`) REFERENCES `Competence` (`Id_competence`),
  CONSTRAINT `Requiert_ibfk_2` FOREIGN KEY (`Id_offre`) REFERENCES `Offre` (`Id_offre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Wishlist` (
  `Id_user` int NOT NULL,
  `Id_offre` int NOT NULL,
  PRIMARY KEY (`Id_user`,`Id_offre`),
  KEY `Id_offre` (`Id_offre`),
  CONSTRAINT `Wishlist_ibfk_1` FOREIGN KEY (`Id_user`) REFERENCES `Utilisateur` (`Id_user`),
  CONSTRAINT `Wishlist_ibfk_2` FOREIGN KEY (`Id_offre`) REFERENCES `Offre` (`Id_offre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
