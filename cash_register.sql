-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 10, 2021 at 12:03 PM
-- Server version: 10.1.47-MariaDB-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cash_register`
--
CREATE DATABASE IF NOT EXISTS `cash_register` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cash_register`;

-- --------------------------------------------------------

--
-- Table structure for table `denomination`
--

DROP TABLE IF EXISTS `denomination`;
CREATE TABLE IF NOT EXISTS `denomination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `plural` varchar(64) DEFAULT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `i_lang_id` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `denomination`
--

INSERT INTO `denomination` (`id`, `lang_id`, `name`, `plural`, `value`) VALUES
(1, 1, 'penny', 'pennies', '0.01'),
(2, 1, 'nickel', 'nickels', '0.05'),
(3, 1, 'dime', 'dimes', '0.10'),
(4, 1, 'quarter', 'quarters', '0.25'),
(5, 1, 'dollar', 'dollars', '1.00'),
(6, 1, 'five-dollar bill', 'five-dollar bills', '5.00'),
(7, 1, 'ten-dollar bill', 'ten-dollar bills', '10.00'),
(8, 1, 'twenty-dollar bill', 'twenty-dollar bills', '20.00'),
(9, 2, 'pièce de 1 centime d\'euro', 'pièces de 1 centime d\'euro', '0.01'),
(10, 2, 'pièce de 2 centimes d\'euro', 'pièces de 2 centimes d\'euro', '0.02'),
(11, 2, 'pièce de 5 centimes d\'euro', 'pièces de 5 centimes d\'euro', '0.05'),
(12, 2, 'pièce de 10 centimes d\'euro', 'pièces de 10 centimes d\'euro', '0.10'),
(13, 2, 'pièce de 20 centimes d\'euro', 'pièces de 20 centimes d\'euro', '0.20'),
(14, 2, 'pièce de 50 centimes d\'euro', 'pièces de 50 centimes d\'euro', '0.50'),
(15, 2, 'pièce de 1 euro', 'pièces de 1 euro', '1.00'),
(16, 2, 'pièce de 2 euros', 'pièces de 2 euros', '2.00'),
(19, 2, 'billet de 5 euros', 'billets de 5 euros', '5.00'),
(20, 2, 'billet de 10 euros', 'billets de 10 euros', '10.00'),
(21, 2, 'billet de 20 euros', 'billets de 20 euros', '20.00'),
(22, 2, 'billet de 50 euros', 'billets de 50 euros', '50.00');

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ui_language` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `code`, `name`) VALUES
(1, 'en-US', 'English (United States)'),
(2, 'fr-FR', 'French (France)');

-- --------------------------------------------------------

--
-- Table structure for table `localization`
--

DROP TABLE IF EXISTS `localization`;
CREATE TABLE IF NOT EXISTS `localization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ui_lang_code` (`code`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `localization`
--

INSERT INTO `localization` (`id`, `code`, `lang_id`, `content`) VALUES
(1, 'PHPUNIT_TEST', 1, 'phpunit test'),
(2, 'FILEUPLOAD', 1, 'Upload your file of amounts to calculate change denominations owed.'),
(3, 'FILEUPLOAD', 2, 'Téléchargez votre fichier de montants pour calculer les coupures de change dues.'),
(4, 'BTNSUBMIT', 1, 'Submit'),
(5, 'BTNSUBMIT', 2, 'Soumettre'),
(6, 'INPUTBOX', 1, 'Input'),
(7, 'INPUTBOX', 2, 'Entrée'),
(8, 'OUTPUTBOX', 1, 'Output'),
(9, 'OUTPUTBOX', 2, 'Sortie'),
(10, 'SAVEASFILE', 1, 'Save output as file'),
(11, 'SAVEASFILE', 2, 'Enregistrer la sortie en tant que fichier'),
(12, 'CASHIERINTERFACE', 1, 'Cashier Interface'),
(13, 'CASHIERINTERFACE', 2, 'Interface de Caisse');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `denomination`
--
ALTER TABLE `denomination`
  ADD CONSTRAINT `fk_denomination_lang_id` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `localization`
--
ALTER TABLE `localization`
  ADD CONSTRAINT `fk_localization_lang_id` FOREIGN KEY (`lang_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE;

--
-- Create test user
--
FLUSH PRIVILEGES;
CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
GRANT SELECT ON cash_register.* TO 'test'@'localhost';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
