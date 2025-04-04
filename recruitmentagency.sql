-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 03:15 AM
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
-- Database: `recruitmentagency`
--
CREATE DATABASE IF NOT EXISTS `recruitmentagency` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `recruitmentagency`;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `status` enum('applied','shortlisted','interview_scheduled','offered','hired','rejected') DEFAULT 'applied',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `employer_decision` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `job_id`, `seeker_id`, `status`, `applied_at`, `employer_decision`) VALUES
(2, 4, 8, 'hired', '2025-03-28 01:16:02', 'approved'),
(3, 5, 10, 'interview_scheduled', '2025-03-28 03:15:34', 'pending'),
(4, 5, 12, 'interview_scheduled', '2025-03-28 03:34:37', 'pending'),
(5, 6, 12, 'interview_scheduled', '2025-03-28 03:50:08', 'pending'),
(6, 9, 8, 'interview_scheduled', '2025-03-28 04:01:54', 'pending'),
(7, 3, 10, 'interview_scheduled', '2025-04-04 00:42:33', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

DROP TABLE IF EXISTS `application_documents`;
CREATE TABLE `application_documents` (
  `document_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `document_type` enum('valid_id','certification','resume','other') NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_documents`
--

INSERT INTO `application_documents` (`document_id`, `application_id`, `document_type`, `document_path`, `uploaded_at`) VALUES
(1, 2, 'resume', 'uploads/application_documents/8/resume/67e5f8526ad6e_Business-Resume-Example.png', '2025-03-28 01:16:02'),
(2, 2, 'certification', 'uploads/application_documents/8/certification/67e5f8526be97_College_Replica_Diploma_01_Med.jpg', '2025-03-28 01:16:02'),
(3, 3, 'certification', 'uploads/application_documents/10/certification/67e61456b333b_1_1_in_photo_specifications_3371fc01cc.webp', '2025-03-28 03:15:34'),
(4, 3, 'resume', 'uploads/application_documents/10/resume/67e61456b43f0_Business-Resume-Example.png', '2025-03-28 03:15:34'),
(5, 3, 'valid_id', 'uploads/application_documents/10/valid_id/67e61456b5a5b_College_Replica_Diploma_01_Med.jpg', '2025-03-28 03:15:34'),
(6, 4, 'valid_id', 'uploads/application_documents/12/valid_id/67e618cd21e4d_1_1_in_photo_specifications_3371fc01cc.webp', '2025-03-28 03:34:37'),
(7, 4, 'valid_id', 'uploads/application_documents/12/valid_id/67e618cd22a42_Business-Resume-Example.png', '2025-03-28 03:34:37'),
(8, 5, 'valid_id', 'uploads/application_documents/12/valid_id/67e61c7098a6d_Business-Resume-Example.png', '2025-03-28 03:50:08'),
(9, 6, 'valid_id', 'uploads/application_documents/8/valid_id/67e61f327a450_Business-Resume-Example.png', '2025-03-28 04:01:54'),
(10, 6, 'certification', 'uploads/application_documents/8/certification/67e61f327ab92_College_Replica_Diploma_01_Med.jpg', '2025-03-28 04:01:54'),
(11, 7, 'valid_id', 'uploads/application_documents/10/valid_id/67ef2af98cd40_company-id-card-poster-design-template-6d520d06e9704cdc89b62afd61da2896_screen.jpg', '2025-04-04 00:42:33');

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

DROP TABLE IF EXISTS `employers`;
CREATE TABLE `employers` (
  `employer_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`employer_id`, `company_name`) VALUES
(7, 'Softwares inDeed'),
(9, 'YEZZAI'),
(11, 'Tech IT');

-- --------------------------------------------------------

--
-- Table structure for table `employer_documents`
--

DROP TABLE IF EXISTS `employer_documents`;
CREATE TABLE `employer_documents` (
  `document_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `document_type` enum('business_permit','barangay_clearance','sec_dti_registration','tin','bir_certificate','official_documents') NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_documents`
--

INSERT INTO `employer_documents` (`document_id`, `employer_id`, `document_type`, `document_path`, `uploaded_at`) VALUES
(1, 7, 'business_permit', 'uploads/employers/7/business_permit/couch.jpg', '2025-03-09 02:16:15'),
(2, 7, 'sec_dti_registration', 'uploads/employers/7/sec_dti_registration/bed.jpg', '2025-03-09 02:16:15'),
(3, 7, 'tin', 'uploads/employers/7/tin/din3.webp', '2025-03-09 02:16:15'),
(4, 7, 'bir_certificate', 'uploads/employers/7/bir_certificate/din2.jpg', '2025-03-09 02:16:15'),
(5, 7, 'official_documents', 'uploads/employers/7/official_documents/David End time.png', '2025-03-09 02:16:15'),
(6, 9, 'business_permit', 'uploads/employers/9/business_permit/businesspermits.jfif', '2025-03-28 00:58:29'),
(7, 9, 'sec_dti_registration', 'uploads/employers/9/sec_dti_registration/images.png', '2025-03-28 00:58:29'),
(8, 9, 'tin', 'uploads/employers/9/tin/tin-card1.jpg', '2025-03-28 00:58:29'),
(9, 9, 'bir_certificate', 'uploads/employers/9/bir_certificate/Sample-BIR-Certificate-of-Registration-2.png', '2025-03-28 00:58:29'),
(10, 9, 'official_documents', 'uploads/employers/9/official_documents/company-id-card-poster-design-template-6d520d06e9704cdc89b62afd61da2896_screen.jpg', '2025-03-28 00:58:29'),
(11, 11, 'business_permit', 'uploads/employers/11/business_permit/businesspermits.jfif', '2025-03-28 03:02:51'),
(12, 11, 'sec_dti_registration', 'uploads/employers/11/sec_dti_registration/images.png', '2025-03-28 03:02:51'),
(13, 11, 'tin', 'uploads/employers/11/tin/tin-card1.jpg', '2025-03-28 03:02:51'),
(14, 11, 'bir_certificate', 'uploads/employers/11/bir_certificate/Sample-BIR-Certificate-of-Registration-2.png', '2025-03-28 03:02:51'),
(15, 11, 'official_documents', 'uploads/employers/11/official_documents/company-id-card-poster-design-template-6d520d06e9704cdc89b62afd61da2896_screen.jpg', '2025-03-28 03:02:51');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `rating`, `comments`, `created_at`) VALUES
(2, 8, 5, 'Smooth hiring process', '2025-03-28 01:47:24'),
(3, 9, 5, 'Great system, really filters out unqualified candidates', '2025-03-28 01:48:04');

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

DROP TABLE IF EXISTS `interviews`;
CREATE TABLE `interviews` (
  `interview_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `recommendation` enum('recommended','not recommended') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`interview_id`, `application_id`, `scheduled_date`, `status`, `notes`, `recommendation`) VALUES
(2, 2, '2025-03-28 12:34:00', 'completed', 'good speaking skills, great skill in programming', 'recommended'),
(3, 3, '2025-03-28 12:00:00', 'completed', 'good communication skills', 'recommended'),
(4, 5, '2025-03-28 11:54:00', 'completed', 'good speaking skills', 'recommended'),
(5, 4, '2025-03-28 11:55:00', 'completed', 'great skills', 'recommended'),
(6, 6, '2025-03-28 12:02:00', 'completed', 'great skill', 'recommended'),
(7, 7, '2025-04-04 20:43:00', 'completed', 'Good', 'recommended');

-- --------------------------------------------------------

--
-- Table structure for table `job_offers`
--

DROP TABLE IF EXISTS `job_offers`;
CREATE TABLE `job_offers` (
  `offer_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `offer_details` text NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `employer_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `salary` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_offers`
--

INSERT INTO `job_offers` (`offer_id`, `application_id`, `offer_details`, `status`, `employer_id`, `seeker_id`, `job_id`, `created_at`, `salary`) VALUES
(2, 2, 'Position: Junior Web Dev\r\nStart Date: March 30, 2025', 'accepted', 9, 8, 4, '2025-03-28 01:46:42', 30000.00);

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

DROP TABLE IF EXISTS `job_postings`;
CREATE TABLE `job_postings` (
  `job_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text NOT NULL,
  `skills` text NOT NULL,
  `status` enum('pending','approved','rejected','unavailable') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `location` varchar(255) DEFAULT NULL,
  `quota` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_postings`
--

INSERT INTO `job_postings` (`job_id`, `employer_id`, `title`, `description`, `requirements`, `skills`, `status`, `created_at`, `location`, `quota`) VALUES
(2, 7, 'Software Engineer', 'Magaling sa javascript at node.js', 'Certificate of candidacy', 'JavaScript, PostGreSQL, Node.js', 'unavailable', '2025-03-09 04:01:35', 'Taguig', -1),
(3, 7, 'Senior Web Developer', 'DSADA', 'dsaDSDAD', 'DSada, JAJDISUAD', 'approved', '2025-03-15 02:24:31', 'Taguig, Philippines', 4),
(4, 9, 'Software Engineer', 'Must be skilled in: \r\nWeb Development\r\nAndroid Development ', '2+ years experience, must be a college graduate, computer literate', 'PHP, C++, Python, Laravel, C#', 'unavailable', '2025-03-28 01:13:22', 'Cupang, Muntinlupa City', 0),
(5, 11, 'Junior Web Developer for Startup', 'This position performs moderately difficult research, design, and software development assignments within a specific software functional area or product line. The position should have the ability to work on individual pieces of work and solve problems including the design of the program flow of individual pieces of code, effective coding, and unit testing.\r\n\r\nRESPONSIBILITIES:\r\n\r\n    Provides and develops technical solutions as well as interface capabilities for web-based products and offerings.\r\n    Translate business requirements and specifications into a functional design and coding logic to enable business functions while adhering to global project design considerations and templates.\r\n    Collaborate with other team members and most stakeholders to define customer requirements and system interfaces, assess available technologies, and develop and present solutions for simple to moderately complex systems.\r\n    Work with quality assurance teams to fix defects in a timely manner.\r\n    Adhere to the defined delivery process.\r\n    Provides accurate status reports to supervisors and management.\r\n    Provide sizing estimates for specific modules or sub-systems.\r\n    Participate in Code Reviews to ensure applications fully meet business requirements\r\n    Successfully implement development processes, coding best practices, and code reviews\r\n    Operate in agile development methodology while collaborating with key stakeholders.\r\n    Code proficiently in the key programming languages.\r\n    Resolve technical issues as necessary.\r\n    Keep abreast of new technology developments.', '    1 3 years of solid, diverse work experience in Web Application development\r\n    Bachelor\'s Degree holder\r\n    Proficiency in C# and ASP.NET.\r\n    Strong knowledge of .NET Core framework.\r\n    Familiarity with RESTful APIs and web services.\r\n    Experience with front-end technologies such as Angular 6 and above.\r\n    Strong knowledge of HTML5, CSS, JavaScript, JQuery and Bootstrap.\r\n    Experience with relational databases such as MS SQL or RDBMS\r\n    Experience with version control systems like Git.\r\n    Experience in agile development process\r\n    Knowledgeable in development tools such as Visual Studio, Azure DevOps is a plus\r\n    Experience with cloud services like Azure or AWS is a plus.\r\n    Knowledge of Microsoft Power Platform (Power Apps, Power Automate) is a plus\r\n    Experience with WordPress is a plus.\r\n    Must have good oral and written skills in English\r\n    Must be willing to work on mid shift schedule', 'MySQL, PHP, C++', 'approved', '2025-03-28 03:10:27', 'Upper Bicutan, Taguig City', 4),
(6, 11, 'Aircon Technician', 'Experienced aircon technician', '2+ years experience', 'Technician, Troubleshooting, Fixing', 'approved', '2025-03-28 03:19:36', 'Lower Bicutan, Taguig City', 1),
(7, 11, 'Tech Support', 'Computer literate that deals with computer troubleshooting', '1 year experience and above, training documents', 'Debugging, Troubleshooting, Communication, Fixing', 'approved', '2025-03-28 03:21:43', 'Sucat, Muntinlupa City', 2),
(8, 11, 'Software Engineer', 'Individual who excels in software creation', '3+ years experience in the field, bachelor\'s degree', 'Node.js, Swift, Assembly, Python', 'approved', '2025-03-28 03:24:46', 'San Pedro, Laguna City', 3),
(9, 9, 'Farming ', 'Will farm rice, wheat', 'Skilled labour', 'Farming, Harvesting', 'approved', '2025-03-28 03:59:20', 'Bulacan City', 3);

-- --------------------------------------------------------

--
-- Table structure for table `job_seekers`
--

DROP TABLE IF EXISTS `job_seekers`;
CREATE TABLE `job_seekers` (
  `seeker_id` int(11) NOT NULL,
  `skills` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_seekers`
--

INSERT INTO `job_seekers` (`seeker_id`, `skills`, `location`) VALUES
(8, 'PHP, Laravel, CSS, HTML, C++', 'Sucat, Muntinlupa City'),
(10, 'PHP, HTML, MySQL, JavaScript', 'Western Bicutan, Taguig City'),
(12, 'Technician, Debugging, Fixing, Troubleshooting', 'Lower Bicutan, Taguig City');

-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_documents`
--

DROP TABLE IF EXISTS `job_seeker_documents`;
CREATE TABLE `job_seeker_documents` (
  `document_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `document_type` enum('valid_id','tin','resume','photo','qualification') NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_seeker_documents`
--

INSERT INTO `job_seeker_documents` (`document_id`, `seeker_id`, `document_type`, `document_path`, `uploaded_at`) VALUES
(6, 8, 'valid_id', 'uploads/job_seekers/8/valid_id/national-ID.png', '2025-03-28 00:53:03'),
(7, 8, 'tin', 'uploads/job_seekers/8/tin/images.jfif', '2025-03-28 00:53:03'),
(8, 8, 'resume', 'uploads/job_seekers/8/resume/Business-Resume-Example.png', '2025-03-28 00:53:03'),
(9, 8, 'photo', 'uploads/job_seekers/8/photo/face-14.jpg', '2025-03-28 00:53:03'),
(10, 8, 'qualification', 'uploads/job_seekers/8/qualification/College_Replica_Diploma_01_Med.jpg', '2025-03-28 00:53:03'),
(11, 10, 'valid_id', 'uploads/job_seekers/10/valid_id/national-ID.png', '2025-03-28 03:00:14'),
(12, 10, 'tin', 'uploads/job_seekers/10/tin/images.jfif', '2025-03-28 03:00:14'),
(13, 10, 'resume', 'uploads/job_seekers/10/resume/Business-Resume-Example.png', '2025-03-28 03:00:14'),
(14, 10, 'photo', 'uploads/job_seekers/10/photo/face-14.jpg', '2025-03-28 03:00:14'),
(15, 10, 'qualification', 'uploads/job_seekers/10/qualification/College_Replica_Diploma_01_Med.jpg', '2025-03-28 03:00:14'),
(16, 12, 'valid_id', 'uploads/job_seekers/12/valid_id/national-ID.png', '2025-03-28 03:33:54'),
(17, 12, 'tin', 'uploads/job_seekers/12/tin/images.jfif', '2025-03-28 03:33:54'),
(18, 12, 'resume', 'uploads/job_seekers/12/resume/Business-Resume-Example.png', '2025-03-28 03:33:54'),
(19, 12, 'photo', 'uploads/job_seekers/12/photo/face-14.jpg', '2025-03-28 03:33:54'),
(20, 12, 'qualification', 'uploads/job_seekers/12/qualification/College_Replica_Diploma_01_Med.jpg', '2025-03-28 03:33:54');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `interview_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `status`, `created_at`, `interview_id`) VALUES
(3, 7, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'read', '2025-03-14 23:53:57', NULL),
(5, 8, 'Your interview has been scheduled on 2025-03-28T12:34 at Cupang, Muntinlupa City.', 'read', '2025-03-28 01:34:26', NULL),
(6, 9, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'read', '2025-03-28 01:36:29', 2),
(7, 8, 'You have received a job offer. Please check your offers.', 'read', '2025-03-28 01:46:42', NULL),
(8, 10, 'Your interview has been scheduled on 2025-03-28T12:00 at Upper Bicutan, Taguig City.', 'read', '2025-03-28 03:41:05', NULL),
(9, 12, 'Your interview has been scheduled on 2025-03-28T11:54 at Lower Bicutan, Taguig City.', 'unread', '2025-03-28 03:54:10', NULL),
(10, 11, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'unread', '2025-03-28 03:54:27', 4),
(11, 12, 'Your interview has been scheduled on 2025-03-28T11:55 at Upper Bicutan, Taguig City.', 'unread', '2025-03-28 03:55:19', NULL),
(12, 11, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'unread', '2025-03-28 03:55:33', 5),
(13, 11, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'unread', '2025-03-28 03:55:44', 3),
(14, 8, 'Your interview has been scheduled on 2025-03-28T12:02 at Bulacan City.', 'unread', '2025-03-28 04:02:11', NULL),
(15, 9, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'unread', '2025-03-28 04:02:21', 6),
(16, 10, 'Your interview has been scheduled on 2025-04-04T20:43 at Taguig, Philippines.', 'unread', '2025-04-04 00:43:19', NULL),
(17, 7, 'A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.', 'unread', '2025-04-04 00:43:33', 7),
(18, 10, 'You have been scheduled for a final interview. Please check your dashboard for details.', 'unread', '2025-04-04 00:49:04', NULL),
(19, 10, 'Your final interview for the job has been marked as completed.', 'unread', '2025-04-04 00:51:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('job_seeker','employer','admin') NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `user_type`, `status`, `created_at`) VALUES
(3, 'Vincent Borja', 'vinceerolborja@gmail.com', '$2y$10$AfXVsZyaG/8kjiQtkH9laes4ggZvq./IiR99jOHBNOxATXcNtKUiy', 'employer', 'verified', '2025-03-08 05:05:35'),
(4, 'Admin User', '123@admin.com', '$2y$10$feW8LwcOaliJbQTAIJ0N9ekjbe76ORi9xs8IGZoDS.FjKchiKF9sC', 'admin', 'verified', '2025-03-08 05:08:36'),
(7, 'Vincent Borja', 'user123@gmail.com', '$2y$10$xjQOzffbCcrhnEigR7MBy./2V/P3Zd/cst1PdIEhFgMFe7k3Gotvu', 'employer', 'verified', '2025-03-09 02:16:15'),
(8, 'Jay Tabigue', 'jaytabigue@gmail.com', '$2y$10$4Vr9NNdnDI.m8anGm1m/b.Yj4nncoFMcW4.uFbt/.6/DXUSoe8T3G', 'job_seeker', 'verified', '2025-03-28 00:53:03'),
(9, 'Jason Laceda', 'jason.laceda@gmail.com', '$2y$10$noTvtFsCP.dKmZ0N.jVPqOVmt7zPVprPc2zg0NJ0xGEglkP8ngb1m', 'employer', 'verified', '2025-03-28 00:58:29'),
(10, 'Yhanskie Cipriano', 'skie0721@gmail.com', '$2y$10$Xe09JjM1SkjH63CHmPEsWOGRWqklM.1G.QZSIR1Cu8ToO36TVuwYe', 'job_seeker', 'verified', '2025-03-28 03:00:14'),
(11, 'Jay Tabigue', 'jhayranoco4@gmail.com', '$2y$10$OcgpyX64//LmanfjgKSkM.OB24lnu7sRHO9j6yLkv9PLa3sSW8TK2', 'employer', 'verified', '2025-03-28 03:02:51'),
(12, 'Cedric Vila', 'cedricvila@gmail.com', '$2y$10$wD81tiU1w0Rydjc6fTuNAOQ5LG13rqvGdLsqO4jnsrY7NgZk648Gu', 'job_seeker', 'verified', '2025-03-28 03:33:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`employer_id`);

--
-- Indexes for table `employer_documents`
--
ALTER TABLE `employer_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `job_offers`
--
ALTER TABLE `job_offers`
  ADD PRIMARY KEY (`offer_id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `seeker_id` (`seeker_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Indexes for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD PRIMARY KEY (`seeker_id`);

--
-- Indexes for table `job_seeker_documents`
--
ALTER TABLE `job_seeker_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `interview_id` (`interview_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `employer_documents`
--
ALTER TABLE `employer_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `job_offers`
--
ALTER TABLE `job_offers`
  MODIFY `offer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `job_seeker_documents`
--
ALTER TABLE `job_seeker_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_documents`
--
ALTER TABLE `employer_documents`
  ADD CONSTRAINT `employer_documents_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_offers`
--
ALTER TABLE `job_offers`
  ADD CONSTRAINT `job_offers_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_offers_ibfk_2` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_offers_ibfk_3` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_offers_ibfk_4` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`job_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `job_postings_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD CONSTRAINT `job_seekers_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_seeker_documents`
--
ALTER TABLE `job_seeker_documents`
  ADD CONSTRAINT `job_seeker_documents_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_4` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_5` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_6` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_7` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`interview_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
