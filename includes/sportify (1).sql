-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 30, 2025 at 01:08 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sportify`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `ID_Admin` int NOT NULL AUTO_INCREMENT,
  `Name_Admin` varchar(50) NOT NULL,
  `LName_Admin` varchar(50) NOT NULL,
  `Image_Admin` varchar(255) NOT NULL,
  `EMail_Admin` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_Admin`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ID_Admin`, `Name_Admin`, `LName_Admin`, `Image_Admin`, `EMail_Admin`) VALUES
(1, 'Zeyna', 'Tschaen', 'Images/Sportify/Zeyna.jpeg', 'zeyna.tschaen@edu.ece.fr'),
(2, 'Omar', 'Bouarfadinia', 'Images/Sportify/Omar.jpeg', 'omar.bouarfadinia@edu.ece.fr'),
(3, 'Louis', 'Dimambro', 'Images/Sportify/Louis.jpeg', 'louis.dimambro@edu.ece.fr'),
(4, 'Yacine', 'Ramdane', 'Images/Sportify/Yacine.jpeg', 'yacine.ramdane@edu.ece.fr');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `ID_User` int NOT NULL AUTO_INCREMENT,
  `Name_User` varchar(50) NOT NULL,
  `LName_User` varchar(50) NOT NULL,
  `AdressLigne1_User` varchar(100) NOT NULL,
  `AdressLigne2_User` varchar(100) NOT NULL,
  `CodePostal_User` int NOT NULL,
  `Pays_User` varchar(50) NOT NULL,
  `Telephone_User` int NOT NULL,
  `EMail_User` varchar(50) NOT NULL,
  `Carte_Etudiant_User` int NOT NULL,
  `Carte_Bleu_User` varchar(50) NOT NULL,
  `Numero_Carte_User` int NOT NULL,
  `Nom_Carte_User` varchar(50) NOT NULL,
  `Date_Expiration_User` date NOT NULL,
  `Code_Securite_User` int NOT NULL,
  `Ville_User` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_User`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communication`
--

DROP TABLE IF EXISTS `communication`;
CREATE TABLE IF NOT EXISTS `communication` (
  `Type_Comm` varchar(50) NOT NULL,
  `Destinatair_Comm` varchar(50) NOT NULL,
  `Destinateur_Comm` varchar(50) NOT NULL,
  `Date_Comm` datetime NOT NULL,
  `Contenue_Comm` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paiment`
--

DROP TABLE IF EXISTS `paiment`;
CREATE TABLE IF NOT EXISTS `paiment` (
  `ID_Paiment` int NOT NULL AUTO_INCREMENT,
  `Date_Paiment` date NOT NULL,
  `Facture_Paiment` int NOT NULL,
  PRIMARY KEY (`ID_Paiment`)
) ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personel/coach`
--

DROP TABLE IF EXISTS `personel/coach`;
CREATE TABLE IF NOT EXISTS `personel/coach` (
  `ID_Coach` int NOT NULL AUTO_INCREMENT,
  `Name_Coach` varchar(50) NOT NULL,
  `LName_Coach` varchar(50) NOT NULL,
  `Image_Coach` varchar(255) NOT NULL,
  `Specialty_Coach` varchar(255) NOT NULL,
  `Video_Coach` varchar(255) NOT NULL,
  `CV_Coach` varchar(255) NOT NULL,
  `Planning_Coach` datetime NOT NULL,
  `Chat_Coach` int NOT NULL,
  `Video_Chat_Coach` varchar(255) NOT NULL,
  `Audio_Chat_Coach` varchar(255) NOT NULL,
  `EMail_Coach` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_Coach`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdv`
--

DROP TABLE IF EXISTS `rdv`;
CREATE TABLE IF NOT EXISTS `rdv` (
  `Date_RDV` datetime NOT NULL,
  `Statut_RDV` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salle de sport`
--

DROP TABLE IF EXISTS `salle de sport`;
CREATE TABLE IF NOT EXISTS `salle de sport` (
  `ID_Salle` int NOT NULL AUTO_INCREMENT,
  `Numero_Salle` int NOT NULL,
  `EMail_Salle` varchar(50) NOT NULL,
  `Telephone_Salle` int NOT NULL,
  `Planning_Salle` datetime NOT NULL,
  PRIMARY KEY (`ID_Salle`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
