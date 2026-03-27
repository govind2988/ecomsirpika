-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2025 at 09:36 AM
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
-- Database: `ecomapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$VScnoGltMQc3KKJj1PDDm.wd6cFCS9uWDDlA3XjQ2CAOFWkJChpLO');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(128, 1, 43, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'test', NULL, 'Active', '2025-07-08 11:27:20'),
(2, 'Clothing', NULL, 'Active', '2025-07-08 11:30:06'),
(3, 'Electronics', NULL, 'Active', '2025-07-08 11:30:15'),
(4, 'Test1', NULL, 'Active', '2025-07-08 16:59:13'),
(5, 'Test4', NULL, 'Active', '2025-07-11 08:33:13'),
(6, 'Test Category', NULL, 'Active', '2025-07-20 18:59:10');

-- --------------------------------------------------------

--
-- Table structure for table `cms_pages`
--

CREATE TABLE `cms_pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cms_pages`
--

INSERT INTO `cms_pages` (`id`, `title`, `slug`, `content`, `created_at`, `updated_at`, `image`) VALUES
(1, 'About Us', 'about', '<p>This is the about us page.</p>', '2025-07-12 07:40:09', '2025-07-14 18:04:14', NULL),
(2, 'Terms and Conditions', 'terms', 'These are the terms.', '2025-07-12 07:40:09', '2025-07-12 07:40:09', NULL),
(4, 'test', 'test', '<p>test page.. ignore or delete it in production release...</p>', '2025-07-14 18:08:07', '2025-07-15 11:02:17', '');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `name`, `email`, `message`, `submitted_at`) VALUES
(1, 'test33', 'test33@example.com', 'test33', '2025-07-18 16:18:16'),
(2, 'test33', 'test33@example.com', '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890', '2025-07-18 16:25:04'),
(3, 'test33', 'test33@example.com', 'test 33 message..', '2025-07-18 16:43:31'),
(4, 'sen', 'sen@example.com', 'test msg..', '2025-07-08 19:47:02'),
(5, 'sen', 'sen@example.com', 'test msg..', '2025-07-08 23:13:22'),
(6, 'test', 'test2@example.com', 'test msg', '2025-07-18 08:03:19'),
(7, 'test33', 'test@t.co', 'test33', '2025-07-18 08:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Processing',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(15) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `quantity`, `status`, `order_date`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `total`, `created_at`) VALUES
(1, 1, NULL, NULL, 'Completed', '2025-07-20 05:01:29', 'sen kumar', '1234567890', 'sk@info.com', 'sk address, sk city - sk0001', 490.00, '2025-07-20 10:31:29'),
(2, 1, NULL, NULL, 'Completed', '2025-07-20 12:24:57', 'test33', '9123456789', 'sk@info.com', 'test33 address.', 370.00, '2025-07-20 17:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 26, 1, 10.00),
(2, 1, 40, 1, 100.00),
(3, 1, 34, 2, 190.00),
(4, 2, 40, 1, 100.00),
(5, 2, 34, 1, 190.00),
(6, 2, 33, 1, 80.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `signature` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `rrp_price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `stock`, `image`, `created_at`, `category`, `category_id`, `rrp_price`, `sale_price`, `youtube_url`) VALUES
(1, 'T-Shirt', NULL, 200, 'test3_g.jpg', '2025-07-08 02:15:15', 'Clothing', 2, 450.00, 350.00, '/'),
(2, 'Headphones', NULL, 100, 'swapped2.jpg', '2025-07-08 02:15:15', 'Electronics', 3, 200.00, 190.00, NULL),
(3, 'test33', 'test33 description..', 100, 'test3_g.jpg', '2025-07-08 11:33:07', 'test', 1, 100.00, 95.00, NULL),
(22, 'Test12', 'test 12 product', 100, '', '2025-07-11 08:20:27', NULL, 1, 12.00, 10.00, NULL),
(23, 'test11', 'test11', 100, '', '2025-07-11 08:25:50', NULL, 1, 12.00, 10.00, NULL),
(24, 'test15', 'test15 product', 100, '', '2025-07-11 08:28:31', NULL, 1, 12.00, 10.00, NULL),
(25, 'test16', 'test16', 100, '', '2025-07-11 08:31:55', NULL, 1, 12.00, 10.00, NULL),
(26, 'Test4', 'test4', 100, '', '2025-07-11 08:33:33', NULL, 5, 12.00, 10.00, NULL),
(27, 'product 1000', 'product 1000', 100, '', '2025-07-12 06:27:10', NULL, 1, 1000.00, 800.00, NULL),
(28, 'test 1001', 'product 1001', 100, '', '2025-07-12 06:28:54', NULL, 1, 1001.00, 801.00, NULL),
(29, 'test1002', 'test 1002', 100, NULL, '2025-07-12 06:36:54', NULL, 1, 1002.00, 802.00, NULL),
(30, 'test1006', 'test 1006 product..', 100, '', '2025-07-12 08:01:30', NULL, 1, 1006.00, 807.00, NULL),
(31, 'test106', 'test 106 product..', 100, '', '2025-07-12 08:04:58', NULL, 1, 106.00, 87.00, NULL),
(32, 'test 205', 'test 205 product..', 200, 'test3_g.jpg', '2025-07-12 08:06:33', NULL, 1, 205.00, 186.00, NULL),
(33, 'test43', 'test 43', 200, 'test3_g.jpg', '2025-07-12 09:02:15', NULL, 1, 100.00, 80.00, NULL),
(34, 'test 209', 'test 209', 200, 'WhatsApp Image 2023-12-09 at 4.14.52 PM.jpeg', '2025-07-12 10:41:46', NULL, 1, 209.00, 190.00, 'https://www.youtube.com/shorts/SQfqkEtcXc8/'),
(35, 'test210', 'test 210 product..', 100, '', '2025-07-12 10:45:01', NULL, 5, 100.00, 80.00, NULL),
(36, 'test211', 'test 211 product', 300, '../uploads/img_687250ef46771.jpg', '2025-07-12 12:11:28', NULL, 4, 100.00, 80.00, NULL),
(37, 'test213', 'test 213 product..', 160, '', '2025-07-12 12:16:41', NULL, 5, 100.00, 85.00, NULL),
(38, 'test214', 'test 214 product..', 150, '../uploads/img_687252ea7eea7.jpg', '2025-07-12 12:19:56', NULL, 5, 100.00, 90.00, NULL),
(39, 'test216', 'test 216 product', 200, '../uploads/img_6873c6762e8df.jpg', '2025-07-13 14:45:11', NULL, 4, 100.00, 85.00, 'https://www.youtube.com/shorts/SQfqkEtcXc8'),
(40, 'sample product 1', 'sample product 1', 100, '', '2025-07-15 14:34:57', NULL, 1, 100.00, 0.00, ''),
(41, 'sample product 2', 'sample product 2', 100, '', '2025-07-19 15:18:17', NULL, 4, 100.00, 90.00, ''),
(42, 'test34', 'test34', 100, '', '2025-07-20 16:57:53', NULL, 4, 100.00, 90.00, ''),
(43, 'test35', 'test35', 100, '', '2025-07-20 17:54:44', NULL, 1, 100.00, 90.00, ''),
(52, 'Test Product1', 'Test Product1 Description', 100, NULL, '2025-07-21 03:47:26', NULL, 6, 100.00, 90.00, NULL),
(53, 'Test Product2', 'Test Product2 Description', 100, NULL, '2025-07-21 03:47:26', NULL, 6, 100.00, NULL, NULL),
(54, 'Test Product3', 'Test Product3 Description', 100, NULL, '2025-07-21 03:47:26', NULL, 6, 100.00, 90.00, NULL),
(55, 'Test Product4', 'Test Product4 Description', 100, NULL, '2025-07-21 03:47:26', NULL, 6, 100.00, 90.00, NULL),
(56, 'Test Product5', 'Test Product5 Description', 100, NULL, '2025-07-21 04:59:03', NULL, 6, 100.00, 90.00, NULL),
(57, 'Test Product6', 'Test Product6 Description', 100, NULL, '2025-07-21 04:59:03', NULL, 6, 100.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `whatsapp_no` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `website_link` varchar(255) DEFAULT NULL,
  `google_map` text DEFAULT NULL,
  `fb_link` varchar(255) DEFAULT NULL,
  `insta_link` varchar(255) DEFAULT NULL,
  `linkedin_link` varchar(255) DEFAULT NULL,
  `x_link` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `google_analytics` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `fb` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `x` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `header_message` text DEFAULT NULL,
  `banner_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `company_name`, `logo`, `contact_no`, `whatsapp_no`, `contact_email`, `address`, `website_link`, `google_map`, `fb_link`, `insta_link`, `linkedin_link`, `x_link`, `youtube_link`, `meta_title`, `meta_description`, `meta_keywords`, `google_analytics`, `website`, `fb`, `instagram`, `linkedin`, `twitter`, `youtube`, `x`, `favicon`, `header_message`, `banner_images`) VALUES
(1, 'My Company', 'swapped(2).jpg', '+911234567890', '', 'test45@test45.com', 'test', NULL, '', NULL, NULL, NULL, NULL, NULL, 'test', 'test', 'test', '', '', 'https://www.facebook.com/lotusinfosys/', 'https://www.instagram.com/lotusinfosys/', 'https://in.linkedin.com/company/lotusinfosys', NULL, 'https://www.youtube.com/user/LotusInfosys', 'https://x.com/Lotusinfosys', 'login-bg.jpg', 'Engae Koomapattiku vangaa...', '[\"68809093ae77e_banner2.avif\",\"68809093ae9d7_venkat_crackers.jpg\",\"68809093aec7f_venkat_diwali.jpeg\"]');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'test2', 'test2@example.com', '*BCF4F28E525ED7EE4664FFFF4DAE13EC14A6ABE1', '2025-07-08 14:52:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_pages`
--
ALTER TABLE `cms_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cms_pages`
--
ALTER TABLE `cms_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
