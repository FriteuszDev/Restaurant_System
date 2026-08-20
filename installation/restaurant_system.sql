-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2026 at 06:07 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_system`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `menu`
--

CREATE TABLE `menu` (
  `dish_id` int(11) NOT NULL,
  `dish_name` varchar(30) DEFAULT NULL,
  `dish_desc` varchar(150) DEFAULT NULL,
  `dish_price` decimal(10,2) DEFAULT NULL,
  `dish_img` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`dish_id`, `dish_name`, `dish_desc`, `dish_price`, `dish_img`) VALUES
(1, 'Stek z Polędwicy Wołowej', 'Soczysty stek.\nMasło ziołowe.\nPieczone ziemniaki.', 89.99, 'stek_wolowy.jpg'),
(5, 'Krem z Pomidorów z Mozzarellą', 'Krem pomidorowy.\nMini mozzarella.\nOliwa bazyliowa.', 24.00, 'krem_pomidorowy.jpg'),
(6, 'Burger Klasyczny Wołowy', '100% wołowiny.\nCheddar i boczek.\nDomowe frytki.', 46.50, 'burger_klasyczny.jpg'),
(8, 'Kaczka Pieczona z Jabłkami', 'Pieczoń kaczka.\nJabłka i majeranek.\nKopytka.', 76.00, 'kaczka_jablka.jpg'),
(9, 'Pierogi z Cielęciną', 'Ręczne pierogi.\nFarsz z cielęciny.\nCebulka i śmietana.', 36.00, 'pierogi_cielecina.jpg'),
(10, 'Tradycyjne Tiramisu', 'Włoski deser.\nMascarpone espresso.\nGorzkie kakao.', 28.00, 'tiramisu.jpg'),
(13, 'Risotto z Borowikami', 'Kremowe risotto.\nŚwieże borowiki.\nParmezan i masło.', 49.00, 'risotto_borowiki.jpg'),
(14, 'Carpaccio z Polędwicy', 'Cienkie plastry.\nKapary i rukola.\nOliwa truflowa.', 42.00, 'carpaccio.jpg'),
(15, 'Zupa Rybna', 'Gęsta zupa rybna.\nŚwieże ryby.\nWarzywa i zioła.', 29.00, 'zupa_rybna.jpg'),
(16, 'Sernik Wiedeński', 'Puszysty sernik.\nKruchutki spód.\nPolewa czekoladowa.', 22.00, 'sernik.jpg'),
(17, 'Spaghetti Bolognese', 'Włoski makaron.\nMięsny sos ragu.\nŚwieża bazylia.', 39.00, 'spaghetti.jpg'),
(18, 'Pstrąg z Masłem Ziołowym', 'Cały pstrąg z pieca.\nMasło ziołowe.\nCząstki cytryny.', 58.00, 'pstrag.jpg'),
(19, 'Placki Ziemniaczane', 'Chrupiące placki.\nSos gulaszowy.\nKwaśna śmietana.', 34.00, 'placki.jpg'),
(20, 'Krewetki w Białym Wini', 'Soczyste krewetki.\nBiałe wino i czosnek.\nPietruszka i bagietka.', 55.00, 'krewetki.jpg'),
(23, 'Rosół', 'rosół drobiowy', 29.99, 'rosol.png'),
(25, '123', '123', 123.00, '123'),
(27, 'asdasd', '	Soczyste krewetki. Białe wino i czosnek. Pietrusz', 123.00, 'asd'),
(28, 'Schabowy', 'Schabowy z ziemniakami', 35.99, 'schabowy.png'),
(29, 'test', 'test', 12.22, 'logo.png'),
(30, 'asd', 'asd', 12.00, NULL),
(31, 'asd', 'asd', 12.00, NULL),
(32, 'asd', 'asd', 12.00, 'logo.png'),
(33, '123', '123', 123.00, 'logo.png'),
(34, '123', '123', 123.00, 'logo.png'),
(35, '123', '123', 123.00, 'logo.png'),
(36, '123', '123', 123.00, 'logo.png'),
(37, '123', '123', 123.00, 'logo.png'),
(38, 'Marek Kanarek', '123', 12.99, 'logo.png');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`dish_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `dish_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
