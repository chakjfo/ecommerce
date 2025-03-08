-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2025 at 09:34 AM
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
(2, 13, 18, 1, 'S', '2025-03-07 02:01:55');

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
(7, 'Tee', '2025-03-06 19:06:52', '2025-03-06 19:06:52'),
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

--
-- Dumping data for table `customer_orders`
--

INSERT INTO `customer_orders` (`order_id`, `customer_name`, `product_id`, `email`, `phone`, `address`, `payment_method`, `quantity`, `order_date`) VALUES
(3, 'Charish Blase Pulido', 20, 'cha.pulido04@gmail.com', '09381946512', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', '', 1, '2025-03-06 20:36:46'),
(3, 'Charish Blase Pulido', 21, 'cha.pulido04@gmail.com', '09381946512', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', '', 1, '2025-03-06 20:36:46'),
(3, 'Charish Blase Pulido', 22, 'cha.pulido04@gmail.com', '09381946512', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', '', 1, '2025-03-06 20:36:46'),
(4, 'Charish Blase Pulido', 21, 'cha.pulido04@gmail.com', '09381946512', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', 'PayPal', 1, '2025-03-06 20:41:25'),
(5, 'Charish Blase Pulido', 22, 'cha.pulido04@gmail.com', '09381946512', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', 'PayPal', 1, '2025-03-06 20:42:18'),
(6, 'Charish Blase Pulido', 21, 'cha.pulido04@gmail.com', '09381946512', 'Purok 4 Slaughter, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', '', 1, '2025-03-06 20:45:30');

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
  `delivery_status` varchar(50) DEFAULT 'Pending',
  `shipping_address` varchar(255) NOT NULL,
  `payment_method` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `UserID`, `OrderDate`, `TotalAmount`, `delivery_date`, `delivery_status`, `shipping_address`, `payment_method`) VALUES
(3, 13, '2025-03-06 20:36:46', 83.75, '2025-03-16', 'Pending', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', 'cash_on_delivery'),
(4, 13, '2025-03-06 20:41:25', 35.15, '2025-03-16', 'Pending', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', 'paypal'),
(5, 13, '2025-03-06 20:42:18', 35.15, '2025-03-16', 'Pending', 'Purok 4, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', 'paypal'),
(6, 13, '2025-03-06 20:45:30', 35.15, '2025-03-16', 'Pending', 'Purok 4 Slaughter, Saavedra Street, Toril, Davao City, CITY OF DAVAO, Davao del Sur 8000', 'credit_card');

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
(20, 'JESUS SAVES', 'Tax not included. Free Shipping.', 18.00, '', 0, '[\"uploads\\/products\\/product_67c9f2f1a08c9_1741288177.png\"]', '2025-03-06 19:09:37', '7', '2025-03-06 20:36:46', '2025-03-06 20:36:46'),
(21, 'OG Logo', 'Tax not included. Free shipping.', 27.00, '', 0, '[\"uploads\\/products\\/product_67c9f3e166dec_1741288417.png\",\"uploads\\/products\\/product_67c9f3e167223_1741288417.png\"]', '2025-03-06 19:13:37', '9', '2025-03-06 20:45:30', '2025-03-06 20:45:30'),
(22, 'Scribble Logo Sweat Pants', 'Tax not included. Free shipping.', 27.00, '', 0, '[\"uploads\\/products\\/product_67c9f43f82ef4_1741288511.png\",\"uploads\\/products\\/product_67c9f43f83263_1741288511.png\"]', '2025-03-06 19:15:11', '8', '2025-03-06 20:42:18', '2025-03-06 20:42:18');

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
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `customer_orders`
--
ALTER TABLE `customer_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD CONSTRAINT `customer_orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
