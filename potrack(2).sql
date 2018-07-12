-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 06, 2018 at 08:20 AM
-- Server version: 5.7.21-0ubuntu0.16.04.1
-- PHP Version: 7.0.28-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `potrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `clientId` int(11) NOT NULL,
  `clientFirstName` varchar(255) NOT NULL,
  `clientLastName` varchar(255) NOT NULL,
  `clientPic` varchar(200) NOT NULL,
  `clientCompany` varchar(255) NOT NULL,
  `clientTelephone` varchar(200) NOT NULL,
  `clientEmail` varchar(200) NOT NULL,
  `clientBillingAddress` text NOT NULL,
  `clientDeliveryAddress` text NOT NULL,
  `clientCity` varchar(255) NOT NULL,
  `clientCountry` varchar(255) NOT NULL,
  `clientPostal` varchar(255) NOT NULL,
  `clientSalesname` varchar(200) NOT NULL,
  `clientTag` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`clientId`, `clientFirstName`, `clientLastName`, `clientPic`, `clientCompany`, `clientTelephone`, `clientEmail`, `clientBillingAddress`, `clientDeliveryAddress`, `clientCity`, `clientCountry`, `clientPostal`, `clientSalesname`, `clientTag`) VALUES
(1, 'Gurbinder', 'singh', '16195303_1774708252850479_3575709606373731873_n.jpg', 'Antb', '8528263923', 'gsgurbinder12@gmail.com', 'Sirhind', '', 'Sirhind', 'India', '147104', 'Gurbinder', 'google');

-- --------------------------------------------------------

--
-- Table structure for table `orderItems`
--

CREATE TABLE `orderItems` (
  `itemId` int(11) NOT NULL,
  `itemNo` varchar(200) NOT NULL,
  `itemName` varchar(255) NOT NULL,
  `itemDescription` text NOT NULL,
  `itemQuantity` int(11) NOT NULL,
  `itemPrice` int(11) NOT NULL,
  `itemAmount` int(11) NOT NULL,
  `poId` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orderItems`
--

INSERT INTO `orderItems` (`itemId`, `itemNo`, `itemName`, `itemDescription`, `itemQuantity`, `itemPrice`, `itemAmount`, `poId`) VALUES
(5, 'P2', 'Earphones', 'Sony, Samsung ear phones', 10, 90, 90, 1),
(4, 'P1', 'Phone Guardd', 'Phone Guard for mobile', 20, 80, 160, 1),
(3, 'P3', 'Back covers', 'Redmi 5a back covers', 40, 100, 400, 2);

-- --------------------------------------------------------

--
-- Table structure for table `orderLog`
--

CREATE TABLE `orderLog` (
  `logId` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `orderStatus` int(11) NOT NULL,
  `logDate` datetime NOT NULL,
  `logDescription` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orderLog`
--

INSERT INTO `orderLog` (`logId`, `orderId`, `orderStatus`, `logDate`, `logDescription`) VALUES
(1, 1, 1, '2018-04-05 23:36:37', 'Order submitted for design'),
(2, 1, 2, '2018-04-05 23:38:34', 'Design has been started'),
(3, 1, 2, '2018-04-05 23:42:09', 'Design has been started'),
(4, 1, 3, '2018-04-05 23:47:10', 'Design sent for review'),
(5, 1, 4, '2018-04-05 23:47:31', 'Design Compelete'),
(6, 1, 5, '2018-04-05 23:48:41', 'Submitted for production'),
(7, 2, 1, '2018-04-05 23:48:44', 'Order submitted for design'),
(8, 2, 2, '2018-04-05 23:50:54', 'Design has been started'),
(9, 1, 6, '2018-04-05 23:52:17', 'Production Started'),
(10, 2, 3, '2018-04-05 23:53:07', 'Design sent for review'),
(11, 2, 4, '2018-04-06 00:10:32', 'Design Compelete'),
(12, 1, 7, '2018-04-06 00:10:47', 'Production Done'),
(13, 1, 8, '2018-04-06 00:56:11', 'Order ready for shipment'),
(14, 1, 9, '2018-04-06 00:57:15', 'Order shipment started'),
(15, 1, 7, '2018-04-06 08:09:58', 'Production Done'),
(16, 1, 8, '2018-04-06 08:10:57', 'Order ready for shipment'),
(17, 1, 9, '2018-04-06 08:11:39', 'Order shipment started');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderId` int(11) NOT NULL,
  `orderNumber` varchar(255) NOT NULL,
  `orderTelephone` varchar(255) NOT NULL,
  `orderPostal` varchar(255) NOT NULL,
  `orderDueDate` date NOT NULL,
  `orderDescription` text NOT NULL,
  `orderDeliveryAddress` text NOT NULL,
  `clientId` int(11) NOT NULL,
  `orderStatus` tinyint(4) NOT NULL,
  `orderSalesPerson` varchar(255) NOT NULL,
  `orderTotal` int(11) NOT NULL,
  `ClientApprovedStatus` tinyint(4) NOT NULL,
  `orderCreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderId`, `orderNumber`, `orderTelephone`, `orderPostal`, `orderDueDate`, `orderDescription`, `orderDeliveryAddress`, `clientId`, `orderStatus`, `orderSalesPerson`, `orderTotal`, `ClientApprovedStatus`, `orderCreated`) VALUES
(1, 'PO201800001', '', '', '2018-04-04', 'test order 1', 'Sirhind mandi', 1, 9, '', 2500, 0, '0000-00-00 00:00:00'),
(2, 'PO201800002', '', '', '2018-02-02', 'new order', 'd d ffdfdsfsf ', 1, 4, '', 4000, 0, '2018-04-05 11:31:00');

-- --------------------------------------------------------

--
-- Table structure for table `poCommentFiles`
--

CREATE TABLE `poCommentFiles` (
  `tidd` int(11) NOT NULL,
  `commentId` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `poCommentFiles`
--

INSERT INTO `poCommentFiles` (`tidd`, `commentId`, `filename`) VALUES
(1, '1', 'attachments433124880.docx'),
(2, '3', 'screenshot-www.newreigndistro.com-2018-04-03-23-00-21.png');

-- --------------------------------------------------------

--
-- Table structure for table `poComments`
--

CREATE TABLE `poComments` (
  `pomsgId` int(11) NOT NULL,
  `pomsgClientId` int(11) NOT NULL,
  `pomsgPOld` varchar(200) NOT NULL,
  `pomsgComment` text NOT NULL,
  `pomsgDate` date NOT NULL,
  `pomsgAddedBy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `poComments`
--

INSERT INTO `poComments` (`pomsgId`, `pomsgClientId`, `pomsgPOld`, `pomsgComment`, `pomsgDate`, `pomsgAddedBy`) VALUES
(1, 0, '1', 'cadasdas', '2018-04-05', 4),
(2, 0, '1', 'fdsfdsffds f dsf sdf sd', '2018-04-05', 1),
(3, 0, '1', 'dasdaas', '2018-04-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` int(11) NOT NULL,
  `userName` varchar(200) NOT NULL,
  `userPassword` varchar(200) NOT NULL,
  `fullName` varchar(200) NOT NULL,
  `userEmail` varchar(200) NOT NULL,
  `userAddedOn` datetime NOT NULL,
  `userType` tinyint(4) NOT NULL,
  `userStatus` tinyint(4) NOT NULL,
  `isCLient` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userName`, `userPassword`, `fullName`, `userEmail`, `userAddedOn`, `userType`, `userStatus`, `isCLient`) VALUES
(1, 'sales', 'e10adc3949ba59abbe56e057f20f883e', 'Gurbinder singh', 'gssgurbinder12@gmail.com', '2017-12-19 02:00:00', 1, 1, 0),
(2, 'designer', 'e10adc3949ba59abbe56e057f20f883e', 'Sukhwinder singh', 'sukhii5942@gmail.com', '2017-12-19 02:00:00', 2, 1, 0),
(3, 'production', 'e10adc3949ba59abbe56e057f20f883e', 'Sukhwinder singh', 'sukhii5942111@gmail.com', '2017-12-19 02:00:00', 3, 1, 0),
(4, 'gsgurbinder12@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'Gurbindersingh', 'gsgurbinder12@gmail.com', '2018-04-05 11:27:00', 4, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`clientId`);

--
-- Indexes for table `orderItems`
--
ALTER TABLE `orderItems`
  ADD PRIMARY KEY (`itemId`);

--
-- Indexes for table `orderLog`
--
ALTER TABLE `orderLog`
  ADD PRIMARY KEY (`logId`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderId`);

--
-- Indexes for table `poCommentFiles`
--
ALTER TABLE `poCommentFiles`
  ADD PRIMARY KEY (`tidd`);

--
-- Indexes for table `poComments`
--
ALTER TABLE `poComments`
  ADD PRIMARY KEY (`pomsgId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `clientId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orderItems`
--
ALTER TABLE `orderItems`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orderLog`
--
ALTER TABLE `orderLog`
  MODIFY `logId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `poCommentFiles`
--
ALTER TABLE `poCommentFiles`
  MODIFY `tidd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `poComments`
--
ALTER TABLE `poComments`
  MODIFY `pomsgId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
