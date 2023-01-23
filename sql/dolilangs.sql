-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 23 jan. 2023 à 12:17
-- Version du serveur :  8.0.21
-- Version de PHP : 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dolilangs`
--

-- --------------------------------------------------------

--
-- Structure de la table `dll_langs`
--

DROP TABLE IF EXISTS `dll_langs`;
CREATE TABLE IF NOT EXISTS `dll_langs` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  `label` varchar(64) NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `dll_projects`
--

DROP TABLE IF EXISTS `dll_projects`;
CREATE TABLE IF NOT EXISTS `dll_projects` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `label` text NOT NULL,
  `ref` varchar(32) NOT NULL,
  `folderpath` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `dll_trans`
--

DROP TABLE IF EXISTS `dll_trans`;
CREATE TABLE IF NOT EXISTS `dll_trans` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `lang` varchar(8) NOT NULL,
  `project_id` int NOT NULL,
  `transkey` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `transcontent` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
