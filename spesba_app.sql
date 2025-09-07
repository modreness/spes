-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 07, 2025 at 10:04 PM
-- Server version: 10.6.23-MariaDB-log
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spesba_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `akcija` text DEFAULT NULL,
  `tabela` varchar(50) DEFAULT NULL,
  `zapis_id` int(11) DEFAULT NULL,
  `datum_vrijeme` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cjenovnik`
--

CREATE TABLE `cjenovnik` (
  `id` int(11) NOT NULL,
  `naziv` varchar(255) DEFAULT NULL,
  `opis` text DEFAULT NULL,
  `cijena` decimal(10,2) DEFAULT NULL,
  `kategorija_id` int(11) DEFAULT NULL,
  `aktivan` tinyint(1) DEFAULT 1,
  `datum_unosa` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `godisnji_odmori`
--

CREATE TABLE `godisnji_odmori` (
  `id` int(11) NOT NULL,
  `korisnik_id` int(11) DEFAULT NULL,
  `datum_od` date DEFAULT NULL,
  `datum_do` date DEFAULT NULL,
  `razlog` text DEFAULT NULL,
  `unosio_id` int(11) DEFAULT NULL,
  `datum_unosa` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kartoni`
--

CREATE TABLE `kartoni` (
  `id` int(11) NOT NULL,
  `pacijent_id` int(11) DEFAULT NULL,
  `datum_otvaranja` date DEFAULT NULL,
  `datum_rodjenja` date DEFAULT NULL,
  `adresa` text DEFAULT NULL,
  `telefon` varchar(50) DEFAULT NULL,
  `jmbg` varchar(20) DEFAULT NULL,
  `spol` enum('muški','ženski','drugo') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `broj_upisa` varchar(50) DEFAULT NULL,
  `anamneza` text DEFAULT NULL,
  `dijagnoza` text DEFAULT NULL,
  `rehabilitacija` text DEFAULT NULL,
  `pocetna_procjena` text DEFAULT NULL,
  `biljeske` text DEFAULT NULL,
  `napomena` text DEFAULT NULL,
  `otvorio_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kartoni`
--

INSERT INTO `kartoni` (`id`, `pacijent_id`, `datum_otvaranja`, `datum_rodjenja`, `adresa`, `telefon`, `jmbg`, `spol`, `email`, `broj_upisa`, `anamneza`, `dijagnoza`, `rehabilitacija`, `pocetna_procjena`, `biljeske`, `napomena`, `otvorio_id`) VALUES
(3, 11, '2025-07-03', '2025-07-09', 'Test', '063000123', '1010101010101010', 'ženski', NULL, '11', 'test', 'test', 'test', 'test', 'test', 'test', 1),
(4, 9, '2025-07-04', '2025-07-11', '', '', '', 'ženski', NULL, '', '', '', '', '', '', '', 1),
(5, 5, '2025-07-04', '2025-07-04', 'Test', 'test', '1708990102016', 'drugo', NULL, '0199', 'test', 'test', 'test', 'test', 'test', 'test', 1),
(6, 13, '2025-07-04', '2025-07-04', 'test', 'test', '1708990102015', 'muški', 'testonja@spes.ba', 'Test', 'test', 'test', 'test', 'test', 'test', 'test', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kategorije_usluga`
--

CREATE TABLE `kategorije_usluga` (
  `id` int(11) NOT NULL,
  `naziv` varchar(255) DEFAULT NULL,
  `opis` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nalazi`
--

CREATE TABLE `nalazi` (
  `id` int(11) NOT NULL,
  `pacijent_id` int(11) DEFAULT NULL,
  `naziv` varchar(255) DEFAULT NULL,
  `opis` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `datum_upload` datetime DEFAULT current_timestamp(),
  `dodao_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(13, 'recepcioner@spes.ba', 'bfbdde13379b93357da0a651597571c768063febcf26f2663047fabd18dd9cba', '2025-06-16 09:19:39', '2025-06-16 06:19:39');

-- --------------------------------------------------------

--
-- Table structure for table `rasporedi_sedmicni`
--

CREATE TABLE `rasporedi_sedmicni` (
  `id` int(11) NOT NULL,
  `terapeut_id` int(11) DEFAULT NULL,
  `datum_od` date DEFAULT NULL,
  `datum_do` date DEFAULT NULL,
  `dan` enum('pon','uto','sri','cet','pet','sub','ned') DEFAULT NULL,
  `smjena` enum('jutro','popodne','vecer') NOT NULL,
  `pocetak` time DEFAULT NULL,
  `kraj` time DEFAULT NULL,
  `unosio_id` int(11) DEFAULT NULL,
  `datum_unosa` timestamp NOT NULL DEFAULT current_timestamp(),
  `unosio_ime` varchar(100) DEFAULT NULL,
  `unosio_prezime` varchar(100) DEFAULT NULL,
  `terapeut_ime` varchar(100) DEFAULT NULL,
  `terapeut_prezime` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rasporedi_sedmicni`
--

INSERT INTO `rasporedi_sedmicni` (`id`, `terapeut_id`, `datum_od`, `datum_do`, `dan`, `smjena`, `pocetak`, `kraj`, `unosio_id`, `datum_unosa`, `unosio_ime`, `unosio_prezime`, `terapeut_ime`, `terapeut_prezime`) VALUES
(1, NULL, '2025-05-12', '2025-05-18', 'pon', 'jutro', '08:00:00', '13:00:00', 3, '2025-05-05 19:46:31', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(2, NULL, '2025-05-12', '2025-05-12', 'pon', 'jutro', NULL, NULL, 3, '2025-05-05 18:04:50', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(3, NULL, '2025-05-13', '2025-05-13', 'uto', 'jutro', NULL, NULL, 3, '2025-05-05 18:04:50', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(4, NULL, '2025-05-14', '2025-05-14', 'sri', 'jutro', NULL, NULL, 3, '2025-05-05 18:04:50', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(5, NULL, '2025-05-15', '2025-05-15', 'cet', 'jutro', NULL, NULL, 3, '2025-05-05 18:04:50', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(6, NULL, '2025-05-16', '2025-05-16', 'pet', 'jutro', NULL, NULL, 3, '2025-05-05 18:04:50', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(7, NULL, '2025-05-17', '2025-05-17', 'sub', 'popodne', NULL, NULL, 3, '2025-05-05 18:04:50', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(8, NULL, '2025-05-19', '2025-05-19', 'pon', 'jutro', NULL, NULL, 3, '2025-05-19 05:27:57', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(9, NULL, '2025-05-20', '2025-05-20', 'uto', 'jutro', NULL, NULL, 3, '2025-05-19 05:27:57', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(10, NULL, '2025-05-21', '2025-05-21', 'sri', 'popodne', NULL, NULL, 3, '2025-05-19 05:27:57', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(11, NULL, '2025-05-22', '2025-05-22', 'cet', 'popodne', NULL, NULL, 3, '2025-05-19 05:27:57', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(12, NULL, '2025-05-23', '2025-05-23', 'pet', 'vecer', NULL, NULL, 3, '2025-05-19 05:27:57', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(13, NULL, '2025-06-16', '2025-06-16', 'pon', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(14, NULL, '2025-06-17', '2025-06-17', 'uto', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(15, NULL, '2025-06-18', '2025-06-18', 'sri', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(16, NULL, '2025-06-19', '2025-06-19', 'cet', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(17, NULL, '2025-06-20', '2025-06-20', 'pet', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(18, NULL, '2025-06-21', '2025-06-21', 'sub', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic'),
(19, NULL, '2025-06-22', '2025-06-22', 'ned', 'jutro', NULL, NULL, 3, '2025-06-09 08:27:14', NULL, NULL, 'Terapeeeeut', 'Terapeutic');

-- --------------------------------------------------------

--
-- Table structure for table `termini`
--

CREATE TABLE `termini` (
  `id` int(11) NOT NULL,
  `pacijent_id` int(11) DEFAULT NULL,
  `terapeut_id` int(11) DEFAULT NULL,
  `usluga_id` int(11) DEFAULT NULL,
  `datum_vrijeme` datetime DEFAULT NULL,
  `status` enum('slobodan','zakazan','otkazan','obavljen') DEFAULT 'zakazan',
  `tip_zakazivanja` enum('online','recepcioner') DEFAULT 'recepcioner',
  `napomena` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transakcije`
--

CREATE TABLE `transakcije` (
  `id` int(11) NOT NULL,
  `pacijent_id` int(11) DEFAULT NULL,
  `termin_id` int(11) DEFAULT NULL,
  `cijena_u_momentu` decimal(10,2) DEFAULT NULL,
  `status` enum('uspesno','neuspesno','na_cekanju') DEFAULT NULL,
  `metod` enum('gotovina','kartica','paypal','stripe') DEFAULT NULL,
  `datum` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tretmani`
--

CREATE TABLE `tretmani` (
  `id` int(11) NOT NULL,
  `karton_id` int(11) DEFAULT NULL,
  `termin_id` int(11) DEFAULT NULL,
  `datum` datetime DEFAULT current_timestamp(),
  `stanje_prije` text DEFAULT NULL,
  `terapija` text DEFAULT NULL,
  `stanje_poslije` text DEFAULT NULL,
  `unio_id` int(11) DEFAULT NULL,
  `terapeut_id` int(11) DEFAULT NULL,
  `terapeut_ime` varchar(100) DEFAULT NULL,
  `terapeut_prezime` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tretmani`
--

INSERT INTO `tretmani` (`id`, `karton_id`, `termin_id`, `datum`, `stanje_prije`, `terapija`, `stanje_poslije`, `unio_id`, `terapeut_id`, `terapeut_ime`, `terapeut_prezime`) VALUES
(1, 4, NULL, '2025-07-06 23:23:51', 'Testno stanje', 'Odlična terapija', 'Sve super', 1, NULL, NULL, NULL),
(2, 5, NULL, '2025-07-06 23:27:09', 'Test', 'test', 'test', 1, NULL, NULL, NULL),
(4, 6, NULL, '2025-07-06 23:29:38', 'test', 'test', 'test', 1, 16, 'Ajdin', 'Skiljan'),
(8, 3, NULL, '2025-07-21 09:06:18', 'test', 'test', 'test', 1, NULL, NULL, NULL),
(9, 6, NULL, '2025-07-21 14:42:37', 'odličan', 'odličan', 'odličan', 1, 15, 'Testniii', 'Terapeut'),
(10, 6, NULL, '2025-07-21 14:51:40', 'It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 'It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 'It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 1, 15, 'Testniii', 'Terapeut'),
(12, 6, NULL, '2025-07-28 10:22:10', 'test', 'test', 'test', 1, 2, NULL, NULL),
(13, 6, NULL, '2025-07-31 13:16:32', 'test', 'test', 'test', 1, 2, 'Terapeeeeut', 'Terapeutic');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `ime` varchar(100) DEFAULT NULL,
  `prezime` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `lozinka` varchar(255) DEFAULT NULL,
  `uloga` enum('admin','terapeut','recepcioner','pacijent') NOT NULL,
  `aktivan` tinyint(1) DEFAULT 0,
  `pristup_svim_pacijentima` tinyint(1) DEFAULT 1,
  `datum_kreiranja` timestamp NOT NULL DEFAULT current_timestamp(),
  `slika` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ime`, `prezime`, `email`, `username`, `lozinka`, `uloga`, `aktivan`, `pristup_svim_pacijentima`, `datum_kreiranja`, `slika`, `last_login`) VALUES
(1, 'Žan', 'Anđić', 'admin@spes.ba', 'zan.andjic', '$2y$10$spoY9GKEqA.63PXvYYETWOkhLPKqLvqoiHx.IyQbq1pGonKL0/aq.', 'admin', 1, 1, '2025-04-29 07:03:45', '20250615_225900_Žan_Anđić.webp', '2025-08-25 09:43:11'),
(3, 'Recepcio', 'Recepcionist', 'recepcioner@spes.ba', NULL, '$2y$10$fK8gBvQXzoxfmKo4a8RvDef8idts.kHr5fzH.XBDPN3af4dw8AItK', 'recepcioner', 1, 1, '2025-04-29 07:03:45', '20250615_225812_Recepcio_Recepcionist1.webp', NULL),
(5, 'Testni', 'Pacijent', 'pacijent@spes.ba', 'testni.pacijent', '$2y$10$Bjw5suKAwbwk35o8nif4EOr2bqs0sShDsgREzCKFwt2v2Z2nxTZzq', 'pacijent', 0, 1, '2025-06-15 21:03:26', NULL, NULL),
(6, 'Biljana', 'Lovrinovic', 'babel@galopdigital.com', 'biljana.lovrinovic', '$2y$10$aMkarIEcqHv0e1DCQ73q9.uqgZ9Zrkaq/oHcPjYtE0nbmr6YaaFUG', 'admin', 0, 1, '2025-06-16 07:54:31', '20250620_092709_Biljana_Lovrinovic.png', '2025-06-20 09:38:36'),
(8, 'Anja', 'Petrić', 'apetric@galopdigital.com', 'anja.petric', '$2y$10$RiSoeYeYL.q/LhMu9kyw4O4BtjtCFZ7OBBKJvZSS6Q6HYjA1RS8tG', 'admin', 0, 1, '2025-06-25 04:51:35', NULL, NULL),
(9, 'Inela', 'Brajić', 'ibrajic@galopdigital.com', 'inela.brajic', '$2y$10$bi5Lu0Hc8uU..xXRywbDTupyBD1WNIKd97OSmOMB6dFz1Zio1caSy', 'pacijent', 0, 1, '2025-06-30 20:28:48', NULL, NULL),
(11, 'Inela', 'Test', 'test@gmail.com', 'inela.petric', '$2y$10$O9ZmGsbBJwiYlta2pWjDTOAoI30IjtSAFFA3hGE/QTfBy1XGeO7KK', 'pacijent', 0, 1, '2025-07-02 20:48:18', NULL, NULL),
(13, 'Testonja', 'Testonja', 'testonja@spes.ba', 'testonja.testonja', '$2y$10$SFt0Oyx8UjbqSCiygAYVGeL6T35QVFMr6AONLs92ueXiJsWIvWhJm', 'pacijent', 0, 1, '2025-07-04 19:03:56', NULL, NULL),
(14, 'Amela', 'Barleci', 'abarleci@galopdigital.com', 'amela.barleci', '$2y$10$dEDawW2POY5p83.zO6jaKe5xAmUyeE9jfVnj55zFOJY.v7GwmOqAm', 'admin', 0, 1, '2025-07-07 05:25:10', NULL, '2025-07-07 13:36:43'),
(16, 'Ajdin', 'Skiljan', 'ajdin@spes.ba', 'ajdin.skiljan', '$2y$10$YU7xtLmZW4wBFy2vqKRZye2ZrdvriM/0llhe1GVxbLjBf/rFqz3by', 'terapeut', 0, 1, '2025-07-31 11:49:21', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ustanova_podaci`
--

CREATE TABLE `ustanova_podaci` (
  `id` int(11) NOT NULL,
  `naziv` varchar(255) DEFAULT NULL,
  `adresa` text DEFAULT NULL,
  `telefon` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `napomena` text DEFAULT NULL,
  `aktivan` tinyint(1) DEFAULT 1,
  `datum_izmjene` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cjenovnik`
--
ALTER TABLE `cjenovnik`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategorija_id` (`kategorija_id`);

--
-- Indexes for table `godisnji_odmori`
--
ALTER TABLE `godisnji_odmori`
  ADD PRIMARY KEY (`id`),
  ADD KEY `korisnik_id` (`korisnik_id`),
  ADD KEY `unosio_id` (`unosio_id`);

--
-- Indexes for table `kartoni`
--
ALTER TABLE `kartoni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pacijent_id` (`pacijent_id`),
  ADD KEY `otvorio_id` (`otvorio_id`);

--
-- Indexes for table `kategorije_usluga`
--
ALTER TABLE `kategorije_usluga`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nalazi`
--
ALTER TABLE `nalazi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pacijent_id` (`pacijent_id`),
  ADD KEY `dodao_id` (`dodao_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rasporedi_sedmicni`
--
ALTER TABLE `rasporedi_sedmicni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `terapeut_id` (`terapeut_id`),
  ADD KEY `unosio_id` (`unosio_id`);

--
-- Indexes for table `termini`
--
ALTER TABLE `termini`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pacijent_id` (`pacijent_id`),
  ADD KEY `terapeut_id` (`terapeut_id`),
  ADD KEY `usluga_id` (`usluga_id`);

--
-- Indexes for table `transakcije`
--
ALTER TABLE `transakcije`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pacijent_id` (`pacijent_id`),
  ADD KEY `termin_id` (`termin_id`);

--
-- Indexes for table `tretmani`
--
ALTER TABLE `tretmani`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karton_id` (`karton_id`),
  ADD KEY `termin_id` (`termin_id`),
  ADD KEY `uneo_id` (`unio_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `ustanova_podaci`
--
ALTER TABLE `ustanova_podaci`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cjenovnik`
--
ALTER TABLE `cjenovnik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `godisnji_odmori`
--
ALTER TABLE `godisnji_odmori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kartoni`
--
ALTER TABLE `kartoni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kategorije_usluga`
--
ALTER TABLE `kategorije_usluga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nalazi`
--
ALTER TABLE `nalazi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `rasporedi_sedmicni`
--
ALTER TABLE `rasporedi_sedmicni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `termini`
--
ALTER TABLE `termini`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transakcije`
--
ALTER TABLE `transakcije`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tretmani`
--
ALTER TABLE `tretmani`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ustanova_podaci`
--
ALTER TABLE `ustanova_podaci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cjenovnik`
--
ALTER TABLE `cjenovnik`
  ADD CONSTRAINT `cjenovnik_ibfk_1` FOREIGN KEY (`kategorija_id`) REFERENCES `kategorije_usluga` (`id`);

--
-- Constraints for table `godisnji_odmori`
--
ALTER TABLE `godisnji_odmori`
  ADD CONSTRAINT `godisnji_odmori_ibfk_1` FOREIGN KEY (`korisnik_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `godisnji_odmori_ibfk_2` FOREIGN KEY (`unosio_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `kartoni`
--
ALTER TABLE `kartoni`
  ADD CONSTRAINT `kartoni_ibfk_1` FOREIGN KEY (`pacijent_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `kartoni_ibfk_2` FOREIGN KEY (`otvorio_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `nalazi`
--
ALTER TABLE `nalazi`
  ADD CONSTRAINT `nalazi_ibfk_1` FOREIGN KEY (`pacijent_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `nalazi_ibfk_2` FOREIGN KEY (`dodao_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rasporedi_sedmicni`
--
ALTER TABLE `rasporedi_sedmicni`
  ADD CONSTRAINT `rasporedi_sedmicni_ibfk_1` FOREIGN KEY (`terapeut_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rasporedi_sedmicni_ibfk_2` FOREIGN KEY (`unosio_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `termini`
--
ALTER TABLE `termini`
  ADD CONSTRAINT `termini_ibfk_1` FOREIGN KEY (`pacijent_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `termini_ibfk_2` FOREIGN KEY (`terapeut_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `termini_ibfk_3` FOREIGN KEY (`usluga_id`) REFERENCES `cjenovnik` (`id`);

--
-- Constraints for table `transakcije`
--
ALTER TABLE `transakcije`
  ADD CONSTRAINT `transakcije_ibfk_1` FOREIGN KEY (`pacijent_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transakcije_ibfk_2` FOREIGN KEY (`termin_id`) REFERENCES `termini` (`id`);

--
-- Constraints for table `tretmani`
--
ALTER TABLE `tretmani`
  ADD CONSTRAINT `tretmani_ibfk_1` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`),
  ADD CONSTRAINT `tretmani_ibfk_2` FOREIGN KEY (`termin_id`) REFERENCES `termini` (`id`),
  ADD CONSTRAINT `tretmani_ibfk_3` FOREIGN KEY (`unio_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
