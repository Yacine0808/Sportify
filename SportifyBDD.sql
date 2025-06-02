-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : dim. 01 juin 2025 à 20:32
-- Version du serveur : 5.7.24
-- Version de PHP : 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sportify`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `ID_Admin` int(11) NOT NULL,
  `Name_Admin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LName_Admin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Image_Admin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMail_Admin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Code_Admin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`ID_Admin`, `Name_Admin`, `LName_Admin`, `Image_Admin`, `EMail_Admin`, `Code_Admin`) VALUES
(1, 'Zeyna', 'Tschaen', 'images/Sportify/Zeyna.jpeg', 'zeyna.tschaen@edu.ece.fr', '1234'),
(2, 'Omar', 'Bouarfadinia', 'images/Sportify/Omar.jpeg', 'omar.bouarfadinia@edu.ece.fr', '5678'),
(3, 'Louis', 'Dimambro', 'images/Sportify/Louis.jpeg', 'louis.dimambro@edu.ece.fr', '9101'),
(4, 'Yacine', 'Ramdane', 'images/Sportify/Yacine.jpeg', 'yacine.ramdane@edu.ece.fr', '0808');

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `ID_User` int(11) NOT NULL,
  `Name_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LName_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AdressLigne1_User` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AdressLigne2_User` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CodePostal_User` int(11) NOT NULL,
  `Pays_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telephone_User` int(11) NOT NULL,
  `EMail_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Password_User` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Carte_Etudiant_User` int(11) NOT NULL,
  `Carte_Bleu_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Numero_Carte_User` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nom_Carte_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Date_Expiration_User` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Code_Securite_User` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Ville_User` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`ID_User`, `Name_User`, `LName_User`, `AdressLigne1_User`, `AdressLigne2_User`, `CodePostal_User`, `Pays_User`, `Telephone_User`, `EMail_User`, `Password_User`, `Carte_Etudiant_User`, `Carte_Bleu_User`, `Numero_Carte_User`, `Nom_Carte_User`, `Date_Expiration_User`, `Code_Securite_User`, `Ville_User`) VALUES
(1003, 'CLEMENT', 'Mila', 'rue Helene', 'rue Heliopolis', 75000, 'France', 791458446, 'mila.clement@email.fr', '270820', 87654321, 'VISA', '3456789854326789', 'CLEMENT', '2027-11', '432', 'Paris'),
(1002, 'LEBOEUF', 'Raoul', 'rue Saint Honoré', 'rue Royale', 75001, 'France', 761764489, 'raoul.leboeuf@email.fr', '101976', 12345678, 'VISA', '1234567890123456', 'LEBOEUF', '2025-11', '123', 'Paris');

-- --------------------------------------------------------

--
-- Structure de la table `personel/coach`
--

CREATE TABLE `personel/coach` (
  `ID_Coach` int(11) NOT NULL,
  `Name_Coach` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LName_Coach` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Image_Coach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Specialty_Coach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Video_Coach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CV_Coach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Chat_Coach` int(11) NOT NULL,
  `Video_Chat_Coach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Audio_Chat_Coach` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMail_Coach` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Code_Coach` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Déchargement des données de la table `personel/coach`
--

INSERT INTO `personel/coach` (`ID_Coach`, `Name_Coach`, `LName_Coach`, `Image_Coach`, `Specialty_Coach`, `Video_Coach`, `CV_Coach`, `Chat_Coach`, `Video_Chat_Coach`, `Audio_Chat_Coach`, `EMail_Coach`, `Code_Coach`) VALUES
(15, 'Guy', 'DUMAIS', 'uploads/coach_photos/coach_img_683ca2f1c7827.png', 'musculation', '', 'xml/Coach_15_CV.xml', 1, 'https://facetime.apple.com/join#v=1&p=b2kuxy9iEfCBwyqjrgywXA&k=186yHd5PGILWcT7ZHBsQGhs-vKx-_XQWGbb6OEfyHRM', 'https://wa.me/33761760843', 'guy.dumais@email.fr', '0000'),
(28, 'Evan', 'DUPONT', 'uploads/coach_photos/coach_img_683c5afdcc995.png', 'fitness', '', 'xml/Coach_28_CV.xml', 1, '', '', 'evan.dupont@email.fr', '0000'),
(29, 'Victor', 'HENRY', 'uploads/coach_photos/coach_img_683c5d06d045f.png', 'biking', '', 'xml/Coach_29_CV.xml', 1, '', '', 'victor.henry@email.fr', '2222'),
(30, 'Louise', 'GAUTIER', 'uploads/coach_photos/coach_img_683c61530191a.png', 'cardio-training', '', 'xml/Coach_30_CV.xml', 1, '', '', 'louise.gautier@email.fr', '3333'),
(31, 'Samuel', 'PETIT', 'uploads/coach_photos/coach_img_683c64c2eca37.png', 'cours collectifs', '', 'xml/Coach_31_CV.xml', 1, '', '', 'samuel.petit@email.fr', '4444'),
(32, 'Jules', 'FRANCOIS', 'uploads/coach_photos/coach_img_683c674a871fd.png', 'basketball', '', 'xml/Coach_32_CV.xml', 1, '', '', 'jules.francois@email.fr', '5555'),
(33, 'Simon', 'THOMAS', 'uploads/coach_photos/coach_img_683c70c76f5c9.png', 'football', '', 'xml/Coach_33_CV.xml', 1, '', '', 'simon.thomas@email.fr', '6666'),
(34, 'Antoine', 'GAUTIER', 'uploads/coach_photos/coach_img_683c707e53841.png', 'rugby', '', 'xml/Coach_34_CV.xml', 1, '', '', 'antoine.gautier@email.fr', '7777'),
(35, 'Patrick', 'MOURATOGLOU', 'uploads/coach_photos/coach_img_683c72036a554.png', 'tennis', '', 'xml/Coach_35_CV.xml', 1, '', '', 'patrick.mouratoglou@email.fr', '8888'),
(36, 'Emmanuel', 'TITOT', 'uploads/coach_photos/coach_img_683c72a9c73dd.png', 'natation', '', 'xml/Coach_36_CV.xml', 1, '', '', 'emmanuel.titot@email.fr', '8888'),
(37, 'Samanta', 'SANCHEZ', 'uploads/coach_photos/coach_img_683c75630565f.png', 'plongeon', '', 'xml/Coach_37_CV.xml', 1, '', '', 'samanta.sanchez@email.fr', '9999');

-- --------------------------------------------------------

--
-- Structure de la table `planning_coach`
--

CREATE TABLE `planning_coach` (
  `ID_Planning` int(11) NOT NULL,
  `ID_Coach` int(11) NOT NULL,
  `DayOfWeek` tinyint(4) NOT NULL COMMENT '0=Dimanche, 1=Lundi, …, 6=Samedi',
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `planning_coach`
--

INSERT INTO `planning_coach` (`ID_Planning`, `ID_Coach`, `DayOfWeek`, `StartTime`, `EndTime`) VALUES
(36, 28, 1, '08:00:00', '16:00:00'),
(37, 28, 3, '10:00:00', '18:00:00'),
(38, 28, 5, '08:00:00', '19:00:00'),
(43, 29, 1, '08:00:00', '17:00:00'),
(44, 29, 3, '09:00:00', '18:00:00'),
(45, 29, 4, '08:00:00', '13:00:00'),
(46, 29, 6, '09:00:00', '19:00:00'),
(47, 30, 1, '09:00:00', '15:00:00'),
(48, 30, 3, '08:00:00', '17:00:00'),
(49, 30, 5, '09:30:00', '16:30:00'),
(50, 31, 1, '08:00:00', '14:00:00'),
(51, 31, 3, '09:00:00', '16:00:00'),
(52, 31, 5, '08:00:00', '18:00:00'),
(53, 31, 6, '09:00:00', '17:00:00'),
(54, 32, 1, '08:00:00', '18:00:00'),
(55, 32, 2, '08:00:00', '18:00:00'),
(56, 32, 4, '09:00:00', '18:00:00'),
(57, 32, 5, '09:00:00', '17:00:00'),
(62, 34, 1, '08:00:00', '19:00:00'),
(63, 34, 2, '08:00:00', '19:00:00'),
(64, 34, 3, '08:00:00', '14:30:00'),
(65, 34, 4, '08:00:00', '19:00:00'),
(66, 33, 1, '08:00:00', '19:00:00'),
(67, 33, 2, '08:00:00', '19:00:00'),
(68, 33, 3, '08:00:00', '19:00:00'),
(69, 33, 4, '08:00:00', '19:00:00'),
(70, 35, 1, '08:00:00', '18:00:00'),
(71, 35, 2, '08:00:00', '18:00:00'),
(72, 35, 3, '08:00:00', '17:00:00'),
(73, 35, 5, '10:00:00', '19:00:00'),
(74, 36, 0, '08:00:00', '12:00:00'),
(75, 36, 2, '08:00:00', '18:00:00'),
(76, 36, 4, '08:00:00', '18:00:00'),
(77, 37, 0, '08:00:00', '18:30:00'),
(78, 37, 1, '08:00:00', '18:30:00'),
(79, 37, 3, '10:00:00', '18:30:00'),
(80, 37, 4, '08:00:00', '18:30:00'),
(81, 37, 5, '08:00:00', '18:30:00'),
(85, 15, 0, '08:00:00', '20:00:00'),
(86, 15, 2, '08:00:00', '18:00:00'),
(87, 15, 4, '10:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `planning_salle`
--

CREATE TABLE `planning_salle` (
  `ID_Planning` int(11) NOT NULL,
  `ID_Salle` int(11) NOT NULL,
  `DayOfWeek` tinyint(4) NOT NULL COMMENT '0=dimanche, 1=lundi, ..., 6=samedi',
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `planning_salle`
--

INSERT INTO `planning_salle` (`ID_Planning`, `ID_Salle`, `DayOfWeek`, `StartTime`, `EndTime`) VALUES
(6, 102, 1, '08:00:00', '20:00:00'),
(7, 102, 2, '08:00:00', '20:00:00'),
(8, 102, 3, '08:00:00', '20:00:00'),
(9, 102, 4, '08:00:00', '20:00:00'),
(10, 102, 5, '08:00:00', '20:00:00'),
(11, 102, 6, '08:00:00', '22:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `rdv`
--

CREATE TABLE `rdv` (
  `ID_RDV` int(11) NOT NULL,
  `Date_RDV` datetime NOT NULL,
  `ID_Coach` int(11) NOT NULL,
  `User_id` int(11) NOT NULL,
  `Statut_RDV` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `rdv`
--

INSERT INTO `rdv` (`ID_RDV`, `Date_RDV`, `ID_Coach`, `User_id`, `Statut_RDV`) VALUES
(8, '2025-06-01 13:00:00', 15, 1002, 1),
(7, '2025-06-01 12:00:00', 15, 1002, 0);

-- --------------------------------------------------------

--
-- Structure de la table `rdv_salle`
--

CREATE TABLE `rdv_salle` (
  `ID_Rdv` int(11) NOT NULL,
  `ID_Salle` int(11) NOT NULL,
  `DayOfWeek` tinyint(1) NOT NULL COMMENT '0=Dimanche … 6=Samedi',
  `StartTime` time NOT NULL,
  `User_id` int(11) NOT NULL COMMENT 'ID du client connecté',
  `CreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `rdv_salle`
--

INSERT INTO `rdv_salle` (`ID_Rdv`, `ID_Salle`, `DayOfWeek`, `StartTime`, `User_id`, `CreatedAt`) VALUES
(5, 102, 1, '08:00:00', 1002, '2025-06-01 01:58:25'),
(6, 102, 1, '10:00:00', 1002, '2025-06-01 01:58:28'),
(7, 102, 3, '09:00:00', 1002, '2025-06-01 01:58:35'),
(8, 102, 1, '12:00:00', 1002, '2025-06-01 02:02:58');

-- --------------------------------------------------------

--
-- Structure de la table `salle de sport`
--

CREATE TABLE `salle de sport` (
  `ID_Salle` int(11) NOT NULL,
  `Numero_Salle` int(11) NOT NULL,
  `EMail_Salle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telephone_Salle` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `salle de sport`
--

INSERT INTO `salle de sport` (`ID_Salle`, `Numero_Salle`, `EMail_Salle`, `Telephone_Salle`) VALUES
(102, 1, 'omnes.ece@email.fr', 998898767);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID_Admin`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`ID_User`);

--
-- Index pour la table `personel/coach`
--
ALTER TABLE `personel/coach`
  ADD PRIMARY KEY (`ID_Coach`);

--
-- Index pour la table `planning_coach`
--
ALTER TABLE `planning_coach`
  ADD PRIMARY KEY (`ID_Planning`),
  ADD KEY `idx_planning_coach_coach` (`ID_Coach`);

--
-- Index pour la table `planning_salle`
--
ALTER TABLE `planning_salle`
  ADD PRIMARY KEY (`ID_Planning`),
  ADD KEY `ID_Salle` (`ID_Salle`);

--
-- Index pour la table `rdv`
--
ALTER TABLE `rdv`
  ADD PRIMARY KEY (`ID_RDV`),
  ADD KEY `fk_rdv_coach` (`ID_Coach`),
  ADD KEY `idx_rdv_user` (`User_id`);

--
-- Index pour la table `rdv_salle`
--
ALTER TABLE `rdv_salle`
  ADD PRIMARY KEY (`ID_Rdv`),
  ADD KEY `ID_Salle` (`ID_Salle`),
  ADD KEY `DayOfWeek` (`DayOfWeek`,`StartTime`);

--
-- Index pour la table `salle de sport`
--
ALTER TABLE `salle de sport`
  ADD PRIMARY KEY (`ID_Salle`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `ID_Admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `ID_User` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1004;

--
-- AUTO_INCREMENT pour la table `personel/coach`
--
ALTER TABLE `personel/coach`
  MODIFY `ID_Coach` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `planning_coach`
--
ALTER TABLE `planning_coach`
  MODIFY `ID_Planning` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT pour la table `planning_salle`
--
ALTER TABLE `planning_salle`
  MODIFY `ID_Planning` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `rdv`
--
ALTER TABLE `rdv`
  MODIFY `ID_RDV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `rdv_salle`
--
ALTER TABLE `rdv_salle`
  MODIFY `ID_Rdv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `salle de sport`
--
ALTER TABLE `salle de sport`
  MODIFY `ID_Salle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `planning_coach`
--
ALTER TABLE `planning_coach`
  ADD CONSTRAINT `fk_planning_coach__coach` FOREIGN KEY (`ID_Coach`) REFERENCES `personel/coach` (`ID_Coach`) ON DELETE CASCADE;

--
-- Contraintes pour la table `planning_salle`
--
ALTER TABLE `planning_salle`
  ADD CONSTRAINT `fk_planning_salle_salle` FOREIGN KEY (`ID_Salle`) REFERENCES `salle de sport` (`ID_Salle`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
