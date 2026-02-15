-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2026 at 09:33 AM
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
(1, 'admin', 'admin@crosslife.org', '$2y$10$8tWY85ZG1uT2/xcN9j.8L.9D/kX.AIQMJx6XOygu5KXBbd3wH3PyC', 'Administrator', 'super_admin', 'active', '2026-02-03 18:20:04', '2026-01-16 06:14:41', '2026-02-03 15:20:04'),
(2, 'ValenceDev', 'mwiganivalence@gmail.com', '$2y$10$f0VyQyhL3.g6RKvH8RI.xeeGppoBOIwft4akU4elKgrTs4kERI5t6', 'Valence Mwigani', 'super_admin', 'active', '2026-01-30 09:24:55', '2026-01-30 06:23:06', '2026-01-30 06:24:55');

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
(10, 'Valence Mwigani', 'phenomenalvalence@gmail.com', '0753775184', 'Hello', 'Retry 4', 'read', NULL, '2026-01-28 13:54:32', '2026-01-28 16:36:11'),
(11, 'Teaching Ministry', 'mwiganivalence@gmail.com', '0755989743', 'INQUIRY', 'HiIIII', 'new', NULL, '2026-02-01 11:12:38', '2026-02-01 11:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_attempt_answers`
--

CREATE TABLE `discipleship_attempt_answers` (
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_attempt_answers`
--

INSERT INTO `discipleship_attempt_answers` (`attempt_id`, `question_id`, `option_id`, `is_correct`, `created_at`) VALUES
(1, 1, 1, 1, '2026-01-29 15:51:20'),
(2, 2, 4, 0, '2026-01-29 16:58:08'),
(3, 2, 3, 1, '2026-02-01 12:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_enrollments`
--

CREATE TABLE `discipleship_enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','withdrawn') DEFAULT 'active',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_enrollments`
--

INSERT INTO `discipleship_enrollments` (`id`, `student_id`, `program_id`, `enrolled_at`, `status`, `completed_at`) VALUES
(1, 1, 1, '2026-01-29 15:30:32', 'active', NULL),
(2, 1, 2, '2026-01-30 05:58:28', 'active', NULL),
(3, 2, 2, '2026-02-01 10:12:10', 'active', NULL),
(4, 1, 3, '2026-02-03 15:26:04', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_modules`
--

CREATE TABLE `discipleship_modules` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `pass_mark_pct` tinyint(3) UNSIGNED DEFAULT 70 COMMENT '0-100; required to unlock next module',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_modules`
--

INSERT INTO `discipleship_modules` (`id`, `program_id`, `title`, `description`, `display_order`, `pass_mark_pct`, `created_at`, `updated_at`) VALUES
(1, 1, 'To Know Christ', 'I love Christ', 1, 60, '2026-01-29 15:46:30', '2026-01-29 16:43:18'),
(2, 1, 'To Love Christ', '', 2, 50, '2026-01-29 16:00:04', '2026-01-29 16:00:04'),
(3, 1, 'To be like Christ', '', 3, 70, '2026-01-29 16:43:41', '2026-01-29 16:43:41'),
(4, 2, 'Fall of Man', '', 1, 70, '2026-01-30 05:55:49', '2026-01-30 05:55:49'),
(5, 3, 'The Whole Doctrine', '', 1, 50, '2026-02-03 15:24:58', '2026-02-03 15:24:58');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_module_attempts`
--

CREATE TABLE `discipleship_module_attempts` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `score_pct` decimal(5,2) NOT NULL COMMENT '0-100',
  `passed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_module_attempts`
--

INSERT INTO `discipleship_module_attempts` (`id`, `enrollment_id`, `module_id`, `attempted_at`, `score_pct`, `passed`) VALUES
(1, 1, 1, '2026-01-29 15:51:20', 100.00, 1),
(2, 1, 2, '2026-01-29 16:58:08', 0.00, 0),
(3, 1, 2, '2026-02-01 12:32:21', 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_module_progress`
--

CREATE TABLE `discipleship_module_progress` (
  `enrollment_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `passed_at` datetime DEFAULT NULL COMMENT 'Set when quiz passed; NULL if not yet passed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_module_progress`
--

INSERT INTO `discipleship_module_progress` (`enrollment_id`, `module_id`, `passed_at`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-01-29 18:51:20', '2026-01-29 15:51:20', '2026-01-29 15:51:20'),
(1, 2, '2026-02-01 15:32:21', '2026-02-01 12:32:21', '2026-02-01 12:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_module_resources`
--

CREATE TABLE `discipleship_module_resources` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `resource_type` enum('text','audio','video','pdf') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL COMMENT 'Formatted text/notes for type=text',
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Relative path for audio/video uploads',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_module_resources`
--

INSERT INTO `discipleship_module_resources` (`id`, `module_id`, `resource_type`, `title`, `content`, `file_path`, `display_order`, `created_at`) VALUES
(1, 1, 'text', 'Christ', 'Export & output\r\nDownload PNG — High-quality raster image\r\nDownload SVG — Scalable vector\r\nDownload PDF — Print-ready (via jsPDF)\r\nCopy to clipboard — Copy QR image (where Clipboard API is supported)\r\nPrint — Open print dialog with QR image\r\nSave design (optional) — Persist design + image on server via api/save-design.php\r\nTechnology stack\r\nFrontend: HTML5, CSS3, JavaScript (vanilla, no framework)\r\nLibraries:\r\nqr-code-styling — QR generation and styling (CDN)\r\njsPDF — PDF export (CDN)\r\nOptional backend: PHP 7.4+ (for Save design API)\r\nNo database required — Save design uses file-based storage in data/designs/\r\nSetup instructions\r\n1. Run locally (frontend only)\r\nClone or copy the project into a folder (e.g. qr_code).\r\nServe the folder with any HTTP server:\r\nXAMPP: Place under htdocs/qr_code and open http://localhost/qr_code/\r\nPHP built-in: From project root run php -S localhost:8080 and open http://localhost:8080\r\nNode: e.g. npx serve . and open the URL shown\r\nOpen index.html in a browser (or via the server URL).\r\nExport and preview work without a backend. Save design will fail until the PHP API is available.\r\n2. Optional: Enable “Save design” (PHP)\r\nEnsure PHP is available (e.g. XAMPP Apache + PHP).\r\nEnsure the project is served from a URL (e.g. http://localhost/qr_code/).\r\nCreate a writable directory for saved designs:\r\nmkdir -p data/designs\r\nchmod 755 data/designs   # or 775 if your server user is different', NULL, 0, '2026-01-29 15:51:03'),
(4, 4, 'pdf', 'Salvation of Man', '', 'assets/img/uploads/discipleship/pdf_1769753380_697c4b24b797f.pdf', 2, '2026-01-30 06:09:40'),
(5, 1, 'audio', 'SONG', '', 'assets/img/uploads/discipleship/audio_1770132074_6982126a74861.mp3', 1, '2026-02-03 15:21:14'),
(6, 5, 'text', 'BAPTISM', 'THE DOCTRINE OF BAPTISMS \r\nPastor Lenhard Kyamba. \r\nTherefore leaving the principles of the doctrine of Christ, let us go \r\non unto perfection; not laying again the foundation of repentance \r\nfrom dead works, and of faith toward God, \r\nOf the doctrine of baptismS, and of laying on of hands, and of \r\nresurrection of the dead, and of eternal judgment. \r\nHebrews 6:1-2 \r\nBaptisms is an important doctrine in Christianity. But water \r\nbaptism is not the only baptism in the Bible. This is the doctrine of \r\nbaptisms (it is plural, See Heb 6:1,2); there is more than one \r\nbaptism. The baptisms are: \r\n1. Water baptism, known as baptism in the name of the Father, \r\nthe Son, and the Holy Ghost (Matt 28:19) (Trinity baptism or \r\nbaptism in the name of Jesus Christ (Acts 8:16)),  \r\n2. Baptism of the Holy Ghost, and  \r\n3. Baptism of fire (Lk 3:16).  \r\n4. Baptism of John the Baptist. \r\n5. Baptism unto His body. \r\nThere is also the Baptism of John the Baptist, which is now \r\nobsolete. Since all of these are important, I will cover all of them. ', NULL, 1, '2026-02-03 15:25:27'),
(7, 5, 'audio', 'SONG', '', 'assets/img/uploads/discipleship/audio_1770132345_698213799af44.mp3', 2, '2026-02-03 15:25:45');

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

--
-- Dumping data for table `discipleship_programs`
--

INSERT INTO `discipleship_programs` (`id`, `program_name`, `description`, `features`, `image_url`, `duration`, `requirements`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'School of Christ', 'School of the Spirit', '', '', '', '', 'active', 0, '2026-01-29 15:30:22', '2026-01-29 15:30:22'),
(2, 'Crosslife Foundation Class (CFC)', 'School of Sonship', '', '', '', '', 'active', 2, '2026-01-30 05:55:00', '2026-01-30 05:55:00'),
(3, 'DOCTRINE OF BAPTISM', 'The teaching and essence behind baptism', '', '', '1 week', '', 'active', 3, '2026-02-03 15:24:29', '2026-02-03 15:24:29');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_questions`
--

CREATE TABLE `discipleship_questions` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_questions`
--

INSERT INTO `discipleship_questions` (`id`, `module_id`, `question_text`, `display_order`, `created_at`) VALUES
(1, 1, 'Do you Love Jesus', 1, '2026-01-29 15:47:11'),
(2, 2, 'Are you a lover of Christ', 0, '2026-01-29 16:01:50'),
(3, 3, 'Ready to carry your cross?', 0, '2026-01-29 16:44:10'),
(4, 4, 'The first man was ADAM?', 0, '2026-01-30 05:57:10'),
(5, 4, 'The plan was for man to have DOMINION', 1, '2026-01-30 05:57:32'),
(6, 4, 'How did man SIN', 0, '2026-01-30 05:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_question_options`
--

CREATE TABLE `discipleship_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(500) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `feedback_text` text DEFAULT NULL COMMENT 'Short explanation shown after answer',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_question_options`
--

INSERT INTO `discipleship_question_options` (`id`, `question_id`, `option_text`, `is_correct`, `feedback_text`, `display_order`, `created_at`) VALUES
(1, 1, 'Yes', 1, 'Good', 2, '2026-01-29 15:50:09'),
(2, 1, 'No', 0, 'Wrong', 1, '2026-01-29 15:50:30'),
(3, 2, 'Yes', 1, 'Good', 0, '2026-01-29 16:01:50'),
(4, 2, 'No', 0, '', 1, '2026-01-29 16:01:50'),
(5, 2, 'I dont know', 0, '', 2, '2026-01-29 16:01:50'),
(6, 3, 'Yes', 1, '', 0, '2026-01-29 16:44:10'),
(7, 3, 'No', 0, '', 1, '2026-01-29 16:44:10'),
(8, 3, 'I dont know', 0, '', 2, '2026-01-29 16:44:10'),
(9, 4, 'Yes', 1, '', 0, '2026-01-30 05:57:10'),
(10, 4, 'No', 0, '', 1, '2026-01-30 05:57:10'),
(11, 5, 'Yes', 1, '', 0, '2026-01-30 05:57:32'),
(12, 5, 'No', 0, '', 1, '2026-01-30 05:57:32'),
(13, 6, 'Disobeying GOD', 1, '', 0, '2026-01-30 05:58:03'),
(14, 6, 'Running away from God', 0, '', 1, '2026-01-30 05:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `discipleship_students`
--

CREATE TABLE `discipleship_students` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipleship_students`
--

INSERT INTO `discipleship_students` (`id`, `email`, `password_hash`, `full_name`, `phone`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'mwiganivalence@gmail.com', '$2y$10$3/LYJSWZOFgK414r48/en.C0sqX9eiWjxv1GOWQvIdReuO3vDWP6m', 'Valence Mwigani', NULL, 'active', '2026-02-03 18:19:46', '2026-01-29 15:29:01', '2026-02-03 15:19:46'),
(2, 'kenpius78@gmail.com', '$2y$10$MD1n73MDuOQiBEQoWxOzoeQDHLbCx9mt9mRlFkuAxwKHMO42e0UQS', 'keneth', '0759419270', 'active', '2026-02-01 13:11:50', '2026-02-01 10:11:21', '2026-02-01 10:11:50');

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
(1, 'Fasting', '21 DAYS', '2026-01-09', '22:00:00', '2026-01-31', '23:00:00', 'Google meet', 'Prayer', 'assets/img/uploads/events/event_697e63522aadb.jpg', 'ongoing', '2026-01-16 06:35:12', '2026-01-31 20:17:22'),
(2, 'Koinonia', 'School of the Spirit', '2026-02-03', '18:00:00', NULL, NULL, 'Sinza kwa Remmy', '', '', 'ongoing', '2026-02-01 10:59:52', '2026-02-01 10:59:52'),
(3, 'Koinonia', 'School of the Spirit', '2026-02-03', '18:00:00', NULL, NULL, 'Sinza kwa Remmy', '', '', 'ongoing', '2026-02-01 11:03:27', '2026-02-01 11:03:27'),
(4, 'Service', '', '2026-02-08', NULL, NULL, NULL, '', '', '', 'ongoing', '2026-02-01 11:05:35', '2026-02-01 11:05:35');

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
  `departments` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leadership`
--

INSERT INTO `leadership` (`id`, `name`, `role`, `departments`, `bio`, `image_url`, `email`, `phone`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Valence Mwigani', 'Head - Media/ICT', '', '', 'assets/img/uploads/leader_1769687125_697b485520f5b.jpg', 'mwiganivalence@gmail.com', '0753775184', 1, 'active', '2026-01-29 11:45:25', '2026-01-29 12:37:15'),
(2, 'Justine Alexander', 'Administrator', '', '', 'assets/img/uploads/leader_1769690004_697b5394d675c.jpg', '', '', 2, 'active', '2026-01-29 12:33:24', '2026-01-29 12:36:42'),
(3, 'Susan Kalonga', 'General Secretary', '', '', 'assets/img/uploads/leader_1769690048_697b53c0404bf.jpg', '', '', 3, 'active', '2026-01-29 12:34:08', '2026-01-29 12:36:42'),
(4, 'Veneranda Veneranda', 'Deaconness', '', '', 'assets/img/uploads/leader_1769690079_697b53df2e1c9.jpg', '', '', 4, 'active', '2026-01-29 12:34:39', '2026-01-29 12:36:42'),
(5, 'Kenneth Kenneth', 'Provost', '', '', 'assets/img/uploads/leader_1769690105_697b53f9d8b00.jpg', '', '', 5, 'active', '2026-01-29 12:35:05', '2026-01-29 12:36:42');

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
(3, 'Teaching Ministry', 'Dedicated to preaching the Gospel of the Cross, the Message of Sonship, the Gospel of the Kingdom of God, and the Gospel of Immortality through systematic teaching and exposition of God&amp;amp;amp;#039;s Word.', 'http://localhost/crosslife_2/assets/img/_MG_4880.jpg', 'Kenneth', '', 'active', 5, '2026-01-16 09:45:20', '2026-02-01 11:08:01'),
(4, 'Discipleship Ministry', 'Through the School of Christ Academy, we provide structured discipleship programs including Foundation Classes, Leadership Training, and Ministry Development to equip believers for the work of ministry.', 'http://localhost/crosslife_2/assets/img/_MG_4902.jpg', 'Amani', '', 'active', 3, '2026-01-16 09:45:20', '2026-02-01 11:07:17'),
(5, 'Prayer Ministry', 'A community of Life, Love, Sonship, and Prayer, committed to intercession for the church, the nation, and the global body of Christ.', 'http://localhost/crosslife_2/assets/img/_MG_5021.jpg', '', '', 'active', 1, '2026-01-16 09:45:20', '2026-01-29 11:23:52'),
(6, 'Outreach Ministry', 'Reaching the global community by showing the Way, revealing the Truth, and sharing Life through Christ, establishing a global network of manifested Sons of God.', 'assets/img/uploads/ministry_1769685350_697b4166ac037.jpg', 'Amani', '', 'active', 4, '2026-01-16 09:45:20', '2026-02-01 11:07:17'),
(7, 'Worship Ministry', 'Leading the church in worship, recognizing that worship is central to the life of CrossLife as we live in Zion, the realm of Christ.', 'http://localhost/crosslife_2/assets/img/_MG_5282.jpg', '', '', 'active', 6, '2026-01-16 09:45:20', '2026-01-29 11:23:52'),
(8, 'Fellowship Ministry', 'Creating an environment where believers experience the Life of God and grow in their identity in Christ, welcoming people from diverse backgrounds, ages, and walks of life.', 'assets/img/uploads/ministry_1769684699_697b3edbac003.jpg', '', '', 'active', 2, '2026-01-16 09:45:20', '2026-02-01 11:07:17');

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

--
-- Dumping data for table `sermons`
--

INSERT INTO `sermons` (`id`, `title`, `description`, `speaker`, `sermon_type`, `youtube_url`, `audio_url`, `thumbnail_url`, `sermon_date`, `category`, `views`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Jesus', '', '', 'video', 'https://youtu.be/2m1YU5H8ITc?si=KbRUKhzKnrIAPffe', '', '', '0000-00-00', '', 0, 'draft', '2026-01-30 09:08:14', '2026-01-30 09:08:14'),
(2, 'IT CAME TO PASS', '', 'Joshua', 'audio', '', '/uploads/audio/20260131-154614_Nathaniel_Bassey_-_Jesus_Iye_-_CeeNaija.com_.mp3', '', NULL, '', 0, 'published', '2026-01-31 12:40:11', '2026-01-31 12:46:14');

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
-- Indexes for table `discipleship_attempt_answers`
--
ALTER TABLE `discipleship_attempt_answers`
  ADD PRIMARY KEY (`attempt_id`,`question_id`),
  ADD KEY `fk_answers_question` (`question_id`),
  ADD KEY `fk_answers_option` (`option_id`);

--
-- Indexes for table `discipleship_enrollments`
--
ALTER TABLE `discipleship_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_enrollment_student_program` (`student_id`,`program_id`),
  ADD KEY `idx_enrollments_student` (`student_id`),
  ADD KEY `idx_enrollments_program` (`program_id`);

--
-- Indexes for table `discipleship_modules`
--
ALTER TABLE `discipleship_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modules_program` (`program_id`),
  ADD KEY `idx_modules_order` (`program_id`,`display_order`);

--
-- Indexes for table `discipleship_module_attempts`
--
ALTER TABLE `discipleship_module_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempts_enrollment` (`enrollment_id`),
  ADD KEY `idx_attempts_module` (`module_id`);

--
-- Indexes for table `discipleship_module_progress`
--
ALTER TABLE `discipleship_module_progress`
  ADD PRIMARY KEY (`enrollment_id`,`module_id`),
  ADD KEY `fk_progress_module` (`module_id`);

--
-- Indexes for table `discipleship_module_resources`
--
ALTER TABLE `discipleship_module_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resources_module` (`module_id`);

--
-- Indexes for table `discipleship_programs`
--
ALTER TABLE `discipleship_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `discipleship_questions`
--
ALTER TABLE `discipleship_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_questions_module` (`module_id`);

--
-- Indexes for table `discipleship_question_options`
--
ALTER TABLE `discipleship_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_options_question` (`question_id`);

--
-- Indexes for table `discipleship_students`
--
ALTER TABLE `discipleship_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_students_email` (`email`),
  ADD KEY `idx_students_status` (`status`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `discipleship_enrollments`
--
ALTER TABLE `discipleship_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `discipleship_modules`
--
ALTER TABLE `discipleship_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `discipleship_module_attempts`
--
ALTER TABLE `discipleship_module_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discipleship_module_resources`
--
ALTER TABLE `discipleship_module_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `discipleship_programs`
--
ALTER TABLE `discipleship_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discipleship_questions`
--
ALTER TABLE `discipleship_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `discipleship_question_options`
--
ALTER TABLE `discipleship_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `discipleship_students`
--
ALTER TABLE `discipleship_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leadership`
--
ALTER TABLE `leadership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discipleship_attempt_answers`
--
ALTER TABLE `discipleship_attempt_answers`
  ADD CONSTRAINT `fk_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `discipleship_module_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_answers_option` FOREIGN KEY (`option_id`) REFERENCES `discipleship_question_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `discipleship_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_enrollments`
--
ALTER TABLE `discipleship_enrollments`
  ADD CONSTRAINT `fk_enrollments_program` FOREIGN KEY (`program_id`) REFERENCES `discipleship_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollments_student` FOREIGN KEY (`student_id`) REFERENCES `discipleship_students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_modules`
--
ALTER TABLE `discipleship_modules`
  ADD CONSTRAINT `fk_modules_program` FOREIGN KEY (`program_id`) REFERENCES `discipleship_programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_module_attempts`
--
ALTER TABLE `discipleship_module_attempts`
  ADD CONSTRAINT `fk_attempts_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `discipleship_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attempts_module` FOREIGN KEY (`module_id`) REFERENCES `discipleship_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_module_progress`
--
ALTER TABLE `discipleship_module_progress`
  ADD CONSTRAINT `fk_progress_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `discipleship_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progress_module` FOREIGN KEY (`module_id`) REFERENCES `discipleship_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_module_resources`
--
ALTER TABLE `discipleship_module_resources`
  ADD CONSTRAINT `fk_resources_module` FOREIGN KEY (`module_id`) REFERENCES `discipleship_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_questions`
--
ALTER TABLE `discipleship_questions`
  ADD CONSTRAINT `fk_questions_module` FOREIGN KEY (`module_id`) REFERENCES `discipleship_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discipleship_question_options`
--
ALTER TABLE `discipleship_question_options`
  ADD CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `discipleship_questions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
