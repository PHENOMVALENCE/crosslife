-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 11:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crosslife`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@crosslife.org', '$2y$10$8tWY85ZG1uT2/xcN9j.8L.9D/kX.AIQMJx6XOygu5KXBbd3wH3PyC', 'Administrator', 'super_admin', 'active', '2026-01-29 12:45:18', '2026-01-16 06:14:41', '2026-01-29 09:45:18');

-- --------------------------------------------------------

--
-- Table structure for table `contact_inquiries`
--

CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_inquiries`
--

INSERT INTO `contact_inquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 'Valence Isdory Mwigani', 'mwiganivalence@gmail.com', '0753775184', 'Hello', 'hi', 'read', NULL, '2026-01-16 06:33:06', '2026-01-16 06:33:30'),
(2, 'Valence Isdory Mwigani', 'mwiganivalence@gmail.com', '0753775184', 'Hello', 'Hello Brethren', 'new', NULL, '2026-01-16 10:03:50', '2026-01-16 10:03:50'),
(3, 'Valence Isdory Mwigani', 'mwiganivalence@gmail.com', '0753775184', 'Hello', 'Food', 'new', NULL, '2026-01-16 11:37:39', '2026-01-16 11:37:39'),
(4, 'Valence Isdory Mwigani', 'mwiganivalence@gmail.com', '0753775184', 'Hello', 'Food', 'new', NULL, '2026-01-16 11:37:39', '2026-01-16 11:37:39'),
(5, 'Valence Isdory Mwigani', 'mwiganivalence@gmail.com', '0753775184', 'Church', 'I want to worship', 'new', NULL, '2026-01-28 12:36:08', '2026-01-28 12:36:08'),
(6, 'Nathaniel Mwigani', 'info@promaafrica.com', '0753775184', 'INQUIRY', 'This is an inquiry', 'new', NULL, '2026-01-28 13:08:46', '2026-01-28 13:08:46'),
(7, 'Nathaniel Mwigani', 'info@promaafrica.com', '0753775184', 'INQUIRY', 'Whats up', 'new', NULL, '2026-01-28 13:16:38', '2026-01-28 13:16:38'),
(8, 'Nathaniel Mwigani', 'info@promaafrica.com', '0753775184', 'INQUIRY', 'TESTING 2', 'new', NULL, '2026-01-28 13:47:31', '2026-01-28 13:47:31'),
(9, 'Nathaniel Mwigani', 'info@promaafrica.com', '0753775184', 'INQUIRY', 'retry 3', 'new', NULL, '2026-01-28 13:54:09', '2026-01-28 13:54:09'),
(10, 'Valence Mwigani', 'phenomenalvalence@gmail.com', '0753775184', 'Hello', 'Retry 4', 'read', NULL, '2026-01-28 13:54:32', '2026-01-28 16:36:11');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_programs`
--

CREATE TABLE `discipleship_programs` (
  `id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `features` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `status` enum('active','inactive','upcoming') DEFAULT 'active',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `end_date`, `end_time`, `location`, `event_type`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Fasting', '21 DAYS', '2026-01-09', '22:00:00', '2026-01-31', '23:00:00', 'Google meet', 'Prayer', '/assets/img/uploads/events/event_6975ffd001055.png', 'ongoing', '2026-01-16 06:35:12', '2026-01-25 11:34:40');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `feedback_type` enum('suggestion','concern','praise','other') DEFAULT 'other',
  `message` text NOT NULL,
  `status` enum('new','reviewed','addressed','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `feedback_type`, `message`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'suggestion', 'Hi', 'reviewed', NULL, '2026-01-16 06:31:38', '2026-01-16 09:56:04'),
(2, 'Valence Mwigani', 'phenomenalvalence@gmail.com', 'suggestion', 'Hi', 'reviewed', NULL, '2026-01-16 06:31:48', '2026-01-16 06:32:47'),
(3, 'V', 'mwiganivalence@gmail.com', 'praise', 'I love GOD', 'new', NULL, '2026-01-16 09:55:38', '2026-01-16 09:55:38'),
(4, 'V', 'mwiganivalence@gmail.com', 'praise', 'I love GOD', 'new', NULL, '2026-01-16 09:55:41', '2026-01-16 09:55:41'),
(5, NULL, NULL, 'concern', 'Food after church', 'new', NULL, '2026-01-16 11:37:25', '2026-01-16 11:37:25'),
(6, NULL, NULL, 'concern', 'Food after church', 'new', NULL, '2026-01-16 11:37:25', '2026-01-16 11:37:25'),
(7, NULL, NULL, 'praise', 'Halleluajah', 'new', NULL, '2026-01-16 13:13:10', '2026-01-16 13:13:10'),
(8, NULL, NULL, 'praise', 'Hallelujah', 'new', NULL, '2026-01-16 13:15:46', '2026-01-16 13:15:46'),
(9, NULL, NULL, 'praise', 'hi', 'new', NULL, '2026-01-16 13:32:35', '2026-01-16 13:32:35'),
(10, NULL, NULL, 'praise', 'hi', 'new', NULL, '2026-01-16 13:32:37', '2026-01-16 13:32:37'),
(11, 'Valence Isdory Mwigani', 'mwiganivalence@gmail.com', 'praise', 'I love Jesus', 'new', NULL, '2026-01-28 12:36:33', '2026-01-28 12:36:33'),
(12, 'Vene', NULL, 'concern', 'Food after service', 'new', NULL, '2026-01-28 12:59:54', '2026-01-28 12:59:54'),
(13, NULL, NULL, 'praise', 'God is good', 'new', NULL, '2026-01-28 13:09:33', '2026-01-28 13:09:33'),
(14, NULL, NULL, 'suggestion', 'Whats up', 'new', NULL, '2026-01-28 13:24:56', '2026-01-28 13:24:56'),
(15, NULL, NULL, 'suggestion', 'TESTING 2', 'new', NULL, '2026-01-28 13:48:05', '2026-01-28 13:48:05'),
(16, NULL, NULL, 'suggestion', 'Retry 4', 'new', NULL, '2026-01-28 16:32:55', '2026-01-28 16:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `leadership`
--

CREATE TABLE `leadership` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ministries`
--

CREATE TABLE `ministries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `leader_name` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ministries`
--

INSERT INTO `ministries` (`id`, `name`, `description`, `image_url`, `leader_name`, `contact_email`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(3, 'Teaching Ministry', 'Dedicated to preaching the Gospel of the Cross, the Message of Sonship, the Gospel of the Kingdom of God, and the Gospel of Immortality through systematic teaching and exposition of God&#039;s Word.', 'http://localhost/crosslife/assets/img/_MG_4880.jpg', '', '', 'active', 3, '2026-01-16 09:45:20', '2026-01-27 11:42:53'),
(4, 'Discipleship Ministry', 'Through the School of Christ Academy, we provide structured discipleship programs including Foundation Classes, Leadership Training, and Ministry Development to equip believers for the work of ministry.', 'http://localhost/crosslife/assets/img/_MG_4902.jpg', 'Amani', '', 'active', 3, '2026-01-16 09:45:20', '2026-01-27 11:43:12'),
(5, 'Prayer Ministry', 'A community of Life, Love, Sonship, and Prayer, committed to intercession for the church, the nation, and the global body of Christ.', 'http://localhost/crosslife/assets/img/_MG_5021.jpg', '', '', 'active', 3, '2026-01-16 09:45:20', '2026-01-16 09:45:20'),
(6, 'Outreach Ministry', 'Reaching the global community by showing the Way, revealing the Truth, and sharing Life through Christ, establishing a global network of manifested Sons of God.', 'http://localhost/crosslife/assets/img/uploads/ministry_1769682650_697b36da955d3.jpg', 'Amani', '', 'active', 4, '2026-01-16 09:45:20', '2026-01-29 10:30:50'),
(7, 'Worship Ministry', 'Leading the church in worship, recognizing that worship is central to the life of CrossLife as we live in Zion, the realm of Christ.', 'http://localhost/crosslife/assets/img/_MG_5282.jpg', '', '', 'active', 5, '2026-01-16 09:45:20', '2026-01-16 09:45:20'),
(8, 'Fellowship Ministry', 'Creating an environment where believers experience the Life of God and grow in their identity in Christ, welcoming people from diverse backgrounds, ages, and walks of life.', 'http://localhost/crosslife/assets/img/uploads/ministry_1768557579_696a0c0bc525f.jpeg', '', '', 'active', 6, '2026-01-16 09:45:20', '2026-01-16 09:59:39');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` enum('active','unsubscribed','bounced') DEFAULT 'active',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_subscriptions`
--

INSERT INTO `newsletter_subscriptions` (`id`, `email`, `name`, `status`, `subscribed_at`, `unsubscribed_at`, `updated_at`) VALUES
(1, 'mwiganivalence@gmail.com', NULL, 'active', '2026-01-28 12:55:50', NULL, '2026-01-28 12:55:50'),
(2, 'mwiganivence@gmail.com', NULL, 'active', '2026-01-28 12:56:38', NULL, '2026-01-28 12:56:38'),
(3, 'iryntito@gmail.com', NULL, 'active', '2026-01-28 13:03:12', NULL, '2026-01-28 13:03:12'),
(4, 'phenomenalvalence@gmail.com', NULL, 'active', '2026-01-28 13:06:04', NULL, '2026-01-28 13:06:04'),
(5, 'humphreyprotas@promaafrica.com', NULL, 'active', '2026-01-28 13:23:03', NULL, '2026-01-28 13:23:03'),
(6, 'info@promaafrica.com', NULL, 'active', '2026-01-28 13:48:21', NULL, '2026-01-28 13:48:21'),
(7, 'mwiganivalence@gmail.coms', NULL, 'active', '2026-01-28 13:54:44', NULL, '2026-01-28 13:54:44'),
(8, 'thewayministry01@gmail.com', NULL, 'active', '2026-01-28 20:37:12', NULL, '2026-01-28 20:37:12'),
(9, 'glorytuntufye@gmail.com', NULL, 'active', '2026-01-28 20:38:04', NULL, '2026-01-28 20:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_requests`
--

CREATE TABLE `prayer_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `prayer_request` text NOT NULL,
  `status` enum('new','prayed','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prayer_requests`
--

INSERT INTO `prayer_requests` (`id`, `name`, `email`, `prayer_request`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 'Valence Mwigani', 'phenomenalvalence@gmail.com', 'I want to finish this website', 'prayed', NULL, '2026-01-28 13:09:07', '2026-01-28 13:09:49'),
(2, NULL, NULL, 'Healing', 'new', NULL, '2026-01-28 13:25:12', '2026-01-28 13:25:12'),
(3, NULL, NULL, 'TESTING 2', 'new', NULL, '2026-01-28 13:47:44', '2026-01-28 13:47:44'),
(4, NULL, NULL, 'Worship', 'new', NULL, '2026-01-28 16:34:05', '2026-01-28 16:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `sermons`
--

CREATE TABLE `sermons` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `speaker` varchar(100) DEFAULT NULL,
  `sermon_type` enum('video','audio') DEFAULT 'video',
  `youtube_url` varchar(500) DEFAULT NULL,
  `audio_url` varchar(500) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `sermon_date` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('published','draft') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `updated_at`) VALUES
(1, 'site_name', 'CMN', 'text', '2026-01-28 12:49:23'),
(2, 'site_email', 'karibuni@crosslife.org', 'email', '2026-01-16 09:53:29'),
(3, 'site_phone', '+255 (0)6 531 265 83', 'text', '2026-01-16 06:14:41'),
(4, 'site_location', 'Dar es Salaam, Tanzania', 'text', '2026-01-16 06:14:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `discipleship_programs`
--
ALTER TABLE `discipleship_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_feedback_type` (`feedback_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `leadership`
--
ALTER TABLE `leadership`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `ministries`
--
ALTER TABLE `ministries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `sermons`
--
ALTER TABLE `sermons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sermon_date` (`sermon_date`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `discipleship_programs`
--
ALTER TABLE `discipleship_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leadership`
--
ALTER TABLE `leadership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ministries`
--
ALTER TABLE `ministries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sermons`
--
ALTER TABLE `sermons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
