-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2026 at 10:53 AM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS product_colors;
DROP TABLE IF EXISTS product_sizes;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `recipient_address` text NOT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `recipient_contact` varchar(20) NOT NULL,
  `notes` text DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_method`, `recipient_name`, `recipient_address`, `recipient_email`, `recipient_contact`, `notes`, `created_at`) VALUES
(1, 4, 45000.00, 'processing', 'cod', 'Denise Ashley Caberto', 'Zabarte', 'denisevich@gmail.com', '09123456789', 'Pakidelivery dapat ng nakahanger at walang lukot', '2026-04-30 12:44:22'),
(2, 2, 49500.00, 'delivered', 'gcash', 'Neil Asher Katug', 'Bahay', 'katugneilasher@gmail.com', '09123456789', 'Sasabog baay nyo pag panget packaging', '2026-04-30 12:57:34');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `size_label` varchar(20) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_hex` varchar(7) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `size_label`, `color_name`, `color_hex`, `quantity`, `price`, `image_url`) VALUES
(1, 1, 3, 'Wedding Dress', 'Medium', 'Assorted', '#fffafa', 1, 45000.00, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcTBuzlIgxZae4TAgR3Cd4X8Ig82MirHtYgrSIgUFRxb5KRn0FO31NFUNXerYYfVorhYV8VM5_A7jYsE34TeMGgGfubhwHqn0ygTwkSeXX_logB_j-gOKyd-VA'),
(2, 2, 3, 'Wedding Dress', 'Medium', 'Assorted', '#fffafa', 1, 45000.00, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcTBuzlIgxZae4TAgR3Cd4X8Ig82MirHtYgrSIgUFRxb5KRn0FO31NFUNXerYYfVorhYV8VM5_A7jYsE34TeMGgGfubhwHqn0ygTwkSeXX_logB_j-gOKyd-VA'),
(3, 2, 4, 'Perfurmed Socks', 'Small', 'Blue', '#1100ff', 1, 4500.00, 'data:image/webp;base64,UklGRv4TAABXRUJQVlA4IPITAABwZQCdASq1ALUAPkkejUQioaEU6aYAKASEs4Bj7iGWP97Ba+vRIQJk43zq6h/Q79ImQHH/zb84fyv7v7Vu7Gc4xI+z/licwR6b7A/9B/u3o8aXnq72FzI9w89zi68z0Ah06BAH/qernaTB4RN+ixzub8sm3pCe+Li9ktDmqLVrbYDEXa4HsVGPYq9l+lpXoamG6SK+oKIWpB+vyxziGJO0GXoYDF+/0wXfZvmdlplzdFSM4sMoblMBz/c6X5uRBFl5lwOgo05JTide829TmZ7b60HRCse3levbz6xViDIYi1vHNG5DF8EyoI+scmxDDV5vReKrZkSb9M8t7BgZmDLJIbeX8fw8FS+frwGBO4oEpnuVF5rYB5XwKb+ZNWwT3HdlDtpjRa4EzWKwhNWO2HHSSN5tabkgj/PLlsfOCk9RsQhRkIDeB+3EcjOZOUGOKMpX4');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT '',
  `category` varchar(100) DEFAULT 'Tops',
  `condition_label` varchar(50) DEFAULT 'Good',
  `seller_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `base_price`, `image_url`, `category`, `condition_label`, `seller_id`, `created_at`) VALUES
(1, 'Ripped jeans', 'Comfy and Fashionable jeans', 1000.00, 'https://www.google.com/imgres?q=ripped%20jeans&imgurl=https%3A%2F%2Fimg.lazcdn.com%2Fg%2Fp%2Ff9bf1911d2c654f7dc734604dc6d1c58.jpg_720x720q80.jpg&imgrefurl=https%3A%2F%2Fwww.lazada.com.ph%2Fproducts%2Fwomen-high-waisted-baggy-ripped-jeans-boyfriend-fashion-large-denim-baggy-blue-jeans-for-girls-i2442213572.html&docid=xbrFFDEI-5bLMM&tbnid=dbJaoEy0_f6fWM&vet=12ahUKEwiTs5nV3JyUAxWIyzgGHZ-HMX8QnPAOegQIIRAB..i&w=720&h=720&hcb=2&ved=2ahUKEwiTs5nV3JyUAxWIyzgGHZ-HMX8QnPAOegQIIRAB', 'Bottom', 'Good', NULL, '2026-04-30 11:20:07'),
(3, 'Wedding Dress', 'Good for a bride kapag crinushback ka', 45000.00, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcTBuzlIgxZae4TAgR3Cd4X8Ig82MirHtYgrSIgUFRxb5KRn0FO31NFUNXerYYfVorhYV8VM5_A7jYsE34TeMGgGfubhwHqn0ygTwkSeXX_logB_j-gOKyd-VA', 'Dresses', 'Excellent', NULL, '2026-04-30 11:26:10'),
(4, 'Perfurmed Socks', 'Comfy socks scented with moonlight perfume.', 4500.00, 'data:image/webp;base64,UklGRv4TAABXRUJQVlA4IPITAABwZQCdASq1ALUAPkkejUQioaEU6aYAKASEs4Bj7iGWP97Ba+vRIQJk43zq6h/Q79ImQHH/zb84fyv7v7Vu7Gc4xI+z/licwR6b7A/9B/u3o8aXnq72FzI9w89zi68z0Ah06BAH/qernaTB4RN+ixzub8sm3pCe+Li9ktDmqLVrbYDEXa4HsVGPYq9l+lpXoamG6SK+oKIWpB+vyxziGJO0GXoYDF+/0wXfZvmdlplzdFSM4sMoblMBz/c6X5uRBFl5lwOgo05JTide829TmZ7b60HRCse3levbz6xViDIYi1vHNG5DF8EyoI+scmxDDV5vReKrZkSb9M8t7BgZmDLJIbeX8fw8FS+frwGBO4oEpnuVF5rYB5XwKb+ZNWwT3HdlDtpjRa4EzWKwhNWO2HHSSN5tabkgj/PLlsfOCk9RsQhRkIDeB+3EcjOZOUGOKMpX4', 'Sets', 'Good', 4, '2026-04-30 12:55:09');

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_hex` varchar(7) NOT NULL DEFAULT '#888888'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `color_name`, `color_hex`) VALUES
(1, 1, 'Red', '#ff0000'),
(3, 3, 'Assorted', '#fffafa'),
(4, 4, 'Gray', '#888888'),
(5, 4, 'Black', '#000000'),
(6, 4, 'White', '#ffffff'),
(7, 4, 'Red', '#ff0000'),
(8, 4, 'Blue', '#1100ff'),
(9, 4, 'Purple', '#8c00ff');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_label` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size_label`) VALUES
(1, 1, 'Free Size'),
(3, 3, 'Medium'),
(4, 4, 'Free Size'),
(5, 4, 'XS'),
(6, 4, 'Small');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size_id`, `color_id`, `stock`) VALUES
(1, 1, 1, 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact` varchar(20) DEFAULT '',
  `address` text DEFAULT '',
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `contact`, `address`, `role`, `created_at`) VALUES
(1, 'UkaiAdmin', 'UkaiAdmin@gmail.com', '$2y$10$Km7ApWHiO46eygbVEQCtlOC7kqKOUUywfFDPxZhR83BmFXvjIvn/S', '', '', 'admin', '2026-04-30 11:04:50'),
(2, 'Neil Asher Katug', 'katugneilasher@gmail.com', '$2y$10$eYyLwXwxmfpzjQB9QPj.6eYaTWfwYveqV37VObdQlN4z14qJ0HEh6', '', '', 'user', '2026-04-30 08:00:27'),
(3, 'John Nash Dela Cruz', 'nash@gmail.com', '$2y$10$SDbU6uIxo1Cr..t17hRfK.8tIrVD0Rhiur4vgykhd4SQKBgl72UDG', '', '', 'user', '2026-04-30 08:03:56'),
(4, 'Denise Ashley Caberto', 'denisevich@gmail.com', '$2y$10$YHYmUzan6.oiou4AjDMLMufYPjzuPJshGL2QYlSPbRx5VkMahZ4aG', '', '', 'user', '2026-04-30 11:16:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`variant_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_seller` (`seller_id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_variant` (`product_id`,`size_id`,`color_id`),
  ADD KEY `size_id` (`size_id`),
  ADD KEY `color_id` (`color_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variants_ibfk_2` FOREIGN KEY (`size_id`) REFERENCES `product_sizes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variants_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `product_colors` (`id`) ON DELETE CASCADE;
COMMIT;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
