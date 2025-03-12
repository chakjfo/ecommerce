-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2025 at 06:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(10) NOT NULL,
  `added_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `size`, `added_at`) VALUES
(1, 13, 14, 2, 'S', '2025-03-07 02:01:03'),
(2, 13, 18, 1, 'S', '2025-03-07 02:01:55'),
(46, 13, 23, 1, 'S', '2025-03-12 07:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_edited` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `date_added`, `date_edited`) VALUES
(7, 'Tee', '2025-03-06 19:06:52', '2025-03-12 05:30:40'),
(8, 'Sweats', '2025-03-06 19:07:04', '2025-03-06 19:07:04'),
(9, 'Pants', '2025-03-06 19:07:10', '2025-03-06 19:07:10'),
(10, 'Accessories', '2025-03-06 19:07:26', '2025-03-06 19:07:26'),
(11, 'Fragrance', '2025-03-06 19:07:35', '2025-03-06 19:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `customer_orders`
--

CREATE TABLE `customer_orders` (
  `order_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `payment_method` enum('Cash','Credit Card','Debit Card','PayPal') NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `TotalAmount` decimal(10,2) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_status` enum('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `shipping_address` varchar(255) NOT NULL,
  `payment_method` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `sizes` varchar(255) NOT NULL,
  `StockQuantity` int(11) NOT NULL,
  `images` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `categories` varchar(255) NOT NULL,
  `edited_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `ProductName`, `Description`, `Price`, `sizes`, `StockQuantity`, `images`, `created_at`, `categories`, `edited_at`, `updated_at`) VALUES
(24, 'Waterlilies', 'Tax not included. Free shipping.', 40.00, '', 15, '[\"uploads\\/products\\/product_67d0d3eba3fef_1741738987.jpg\",\"uploads\\/products\\/product_67d0d3eba4be6_1741738987.jpg\"]', '2025-03-12 00:23:07', '11', '2025-03-12 04:20:29', '2025-03-12 04:20:29'),
(25, 'DESIDERATA Hoodie', 'Tax not included. Free shipping.', 30.00, '', 10, '[\"uploads\\/products\\/product_67d0db0dc493c_1741740813.jpg\",\"uploads\\/products\\/product_67d0db0dc55ab_1741740813.jpg\",\"uploads\\/products\\/product_67d0db0dc5b88_1741740813.jpg\"]', '2025-03-12 00:53:33', '8', '2025-03-12 00:53:33', '2025-03-12 00:53:33'),
(26, 'DAMN Crewneck', 'Tax not included. Free shipping.', 22.00, '', 10, '[\"uploads\\/products\\/product_67d0db8f54cdc_1741740943.jpg\",\"uploads\\/products\\/product_67d0db8f551a4_1741740943.jpg\"]', '2025-03-12 00:55:43', '8', '2025-03-12 00:55:43', '2025-03-12 00:55:43'),
(27, 'HORSE Hoodie', 'Tax not included. Free shipping.', 30.00, '', 19, '[\"uploads\\/products\\/product_67d0e4b8ddae0_1741743288.jpg\",\"uploads\\/products\\/product_67d0e4b8ded0a_1741743288.jpg\",\"uploads\\/products\\/product_67d0e4b8df1ea_1741743288.jpg\"]', '2025-03-12 01:34:48', '8', '2025-03-12 02:17:09', '2025-03-12 02:17:09'),
(28, 'QUID PRO QUO Hoodie', 'Tax not included. Free shipping.', 30.00, '', 9, '[\"uploads\\/products\\/product_67d0e4e7640ad_1741743335.jpg\",\"uploads\\/products\\/product_67d0e4e7644d7_1741743335.jpg\",\"uploads\\/products\\/product_67d0e4e764c21_1741743335.jpg\"]', '2025-03-12 01:35:35', '8', '2025-03-12 01:35:35', '2025-03-12 01:35:35'),
(29, 'LETTERMARK Hoodie', 'Tax not included. Free shipping.', 30.00, '', 10, '[\"uploads\\/products\\/product_67d0e52e4a6b5_1741743406.jpg\",\"uploads\\/products\\/product_67d0e52e4acd9_1741743406.jpg\"]', '2025-03-12 01:36:46', '8', '2025-03-12 01:36:46', '2025-03-12 01:36:46'),
(30, 'DUMPLING Bag', 'Tax not included. Free Shipping.', 10.00, '', 20, '[\"uploads\\/products\\/product_67d0fef36a9dc_1741750003.png\"]', '2025-03-12 03:26:43', '10', '2025-03-12 03:26:43', '2025-03-12 03:26:43'),
(31, 'TAGS LOGO Tote Bag', 'Tax not included. Free Shipping.', 10.00, '', 6, '[\"uploads\\/products\\/product_67d0ff194e83a_1741750041.png\"]', '2025-03-12 03:27:21', '10', '2025-03-12 03:27:57', '2025-03-12 03:27:57'),
(32, 'BRUSH Tee', 'Tax not included. Free shipping.', 15.00, '', 8, '[\"uploads\\/products\\/product_67d101e83c2a9_1741750760.jpg\",\"uploads\\/products\\/product_67d101e83ceac_1741750760.jpg\",\"uploads\\/products\\/product_67d101e83d3da_1741750760.jpg\"]', '2025-03-12 03:39:20', '7', '2025-03-12 03:39:20', '2025-03-12 03:39:20'),
(33, 'EARTH Tee', 'Tax not included. Free shipping.', 15.00, '', 5, '[\"uploads\\/products\\/product_67d10202cfed2_1741750786.jpg\",\"uploads\\/products\\/product_67d10202d04ad_1741750786.jpg\"]', '2025-03-12 03:39:46', '7', '2025-03-12 03:39:46', '2025-03-12 03:39:46'),
(34, 'JUDAS Tee', 'Tax not included. Free shipping.', 15.00, '', 10, '[\"uploads\\/products\\/product_67d10217b43c6_1741750807.jpg\",\"uploads\\/products\\/product_67d10217b4c8f_1741750807.jpg\"]', '2025-03-12 03:40:07', '7', '2025-03-12 03:40:07', '2025-03-12 03:40:07'),
(35, 'MERCURIAL Tee', 'Tax not included. Free shipping.', 15.00, '', 10, '[\"uploads\\/products\\/product_67d1023b0def3_1741750843.jpg\",\"uploads\\/products\\/product_67d1023b0e338_1741750843.jpg\",\"uploads\\/products\\/product_67d1023b0e855_1741750843.jpg\"]', '2025-03-12 03:40:43', '7', '2025-03-12 03:40:43', '2025-03-12 03:40:43'),
(36, 'FALSE PROPHET Tee', 'Tax not included. Free shipping.', 15.00, '', 9, '[\"uploads\\/products\\/product_67d1025f125cd_1741750879.jpg\",\"uploads\\/products\\/product_67d1025f12a0a_1741750879.jpg\"]', '2025-03-12 03:41:19', '7', '2025-03-12 04:14:34', '2025-03-12 04:14:34'),
(37, 'SCRIBBLE Sweat Pants', 'Tax not included. Free shipping.', 27.00, '', 10, '[\"uploads\\/products\\/product_67d106353875b_1741751861.jpg\",\"uploads\\/products\\/product_67d1063538dcb_1741751861.jpg\"]', '2025-03-12 03:57:41', '9', '2025-03-12 03:57:41', '2025-03-12 03:57:41'),
(38, 'OG LOGO Sweat Pants', 'Tax not included. Free shipping.', 17.00, '', 10, '[\"uploads\\/products\\/product_67d1064e99683_1741751886.jpg\",\"uploads\\/products\\/product_67d1064e99b8e_1741751886.jpg\"]', '2025-03-12 03:58:06', '9', '2025-03-12 03:58:06', '2025-03-12 03:58:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Role` enum('customer','admin') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Password`, `Email`, `PhoneNumber`, `Role`) VALUES
(7, 'admin', '$2y$10$HHm1pKneD3dGX2EtYjejF.0m3Y3wXrAhC37tDGi3IhUR0gqXbtgIq', 'admin@gmail.com', '639381946512', 'admin'),
(13, 'test', '$2y$10$7c/RjCF2mQ0/OpEWw9RDx.wwHT9oRR3K/hqytf1KKxsiOJn4r7pA2', 'sfdjojoijojf@gmail.com', '093424223', 'customer'),
(15, 'test2', '$2y$10$F.LZfKKQ6j2.pfvO3mVRLeUELheEgA8.6h0Zqkoaz0vDs74M4zUmy', 'test2@gmail.com', '09323223232', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `customer_orders_ibfk_1` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customer_orders`
--
ALTER TABLE `customer_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD CONSTRAINT `customer_orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_customer_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
