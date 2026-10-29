-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 29, 2026 at 01:12 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simbita_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_works`
--

CREATE TABLE `academic_works` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `work_type_id` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abstract` text COLLATE utf8mb4_unicode_ci,
  `keywords` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','proposal','feasibility_test','writing','progress_exam','revision','closed_exam','ready_for_defense','defense','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `started_at` date DEFAULT NULL,
  `completed_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_works`
--

INSERT INTO `academic_works` (`id`, `student_id`, `work_type_id`, `title`, `abstract`, `keywords`, `status`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(5, 5, 1, 'PERAN MAHASISWA DALAM MEMAJUKAN LITERASI DIGITAL', 'Mahasiswa dapat turut serta mengembangkan dan memajukan literasi secara digital melalu program baca keliling.', NULL, 'proposal', '2026-10-27', NULL, '2026-10-27 07:31:59', '2026-10-27 07:31:59');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_table` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `related_table`, `related_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 02:40:02'),
(2, 1, 'logout', 'users', 1, 'User logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 02:40:11'),
(3, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 02:40:29'),
(4, 1, 'logout', 'users', 1, 'User logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 03:12:35'),
(5, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 04:01:48'),
(6, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:37:46'),
(7, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:37:53'),
(8, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:37:55'),
(9, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:37:56'),
(10, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:37:56'),
(11, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:45:09'),
(12, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 06:45:11'),
(13, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:13:23'),
(14, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:15:43'),
(15, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:15:46'),
(16, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:18:41'),
(17, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:24:59'),
(18, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:25:46'),
(19, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:25:48'),
(20, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:25:58'),
(21, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:26:00'),
(22, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:26:02'),
(23, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:26:03'),
(24, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:26:04'),
(25, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:34:55'),
(26, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:34:57'),
(27, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:34:58'),
(28, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:34:59'),
(29, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:35:00'),
(30, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:35:56'),
(31, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:36:12'),
(32, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:36:13'),
(33, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:36:14'),
(34, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:30'),
(35, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:32'),
(36, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:33'),
(37, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:34'),
(38, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:34'),
(39, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:35'),
(40, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:35'),
(41, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:36'),
(42, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:37'),
(43, 1, 'login', 'users', 1, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-25 07:39:46'),
(44, 17, 'login', 'users', 17, 'Mahasiswa login ke sistem', '192.168.1.10', 'Mozilla/5.0 Chrome', '2024-03-25 07:20:00'),
(45, 17, 'upload_document', 'document_versions', 2, 'Mengunggah Proposal V2', '192.168.1.10', 'Mozilla/5.0 Chrome', '2024-03-25 07:30:00'),
(46, NULL, 'review_document', 'reviews', NULL, 'Memberikan keputusan needs_revision', '192.168.1.20', 'Mozilla/5.0 Firefox', '2024-03-26 03:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `bimbingan_logs`
--

CREATE TABLE `bimbingan_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `academic_work_id` int(10) UNSIGNED NOT NULL,
  `meeting_date` date NOT NULL,
  `topic` varchar(255) NOT NULL,
  `discussion_points` text,
  `action_items` text,
  `student_notes` text,
  `lecturer_notes` text,
  `is_attended` tinyint(1) NOT NULL DEFAULT '1',
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_slot_id` int(10) UNSIGNED NOT NULL,
  `academic_work_id` int(10) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `agenda` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','done') DEFAULT 'pending',
  `lecturer_note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_items`
--

CREATE TABLE `checklist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `work_type_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `document_type_id` smallint(5) UNSIGNED DEFAULT NULL,
  `item_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_order` smallint(5) UNSIGNED NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checklist_items`
--

INSERT INTO `checklist_items` (`id`, `work_type_id`, `document_type_id`, `item_text`, `item_order`, `is_active`, `created_at`) VALUES
(1, NULL, 1, 'Judul jelas, spesifik, dan sesuai bidang ilmu', 1, 1, '2026-10-25 02:08:34'),
(2, NULL, 1, 'Latar belakang masalah tersusun logis', 2, 1, '2026-10-25 02:08:34'),
(3, NULL, 1, 'Rumusan masalah jelas dan terukur', 3, 1, '2026-10-25 02:08:34'),
(4, NULL, 1, 'Tujuan penelitian sesuai rumusan masalah', 4, 1, '2026-10-25 02:08:34'),
(5, NULL, 1, 'Metode penelitian dijelaskan secara memadai', 5, 1, '2026-10-25 02:08:34'),
(6, NULL, 1, 'Daftar pustaka memadai dan sitasi konsisten', 6, 1, '2026-10-25 02:08:34'),
(7, NULL, 2, 'Latar belakang sesuai topik penelitian', 1, 1, '2026-10-25 02:08:34'),
(8, NULL, 2, 'Rumusan masalah konsisten dengan judul', 2, 1, '2026-10-25 02:08:34'),
(9, NULL, 2, 'Tujuan dan manfaat penelitian jelas', 3, 1, '2026-10-25 02:08:34'),
(10, NULL, 2, 'Batasan masalah dijelaskan', 4, 1, '2026-10-25 02:08:34'),
(11, NULL, 3, 'Teori relevan dan mutakhir', 1, 1, '2026-10-25 02:08:34'),
(12, NULL, 3, 'Kerangka pemikiran jelas', 2, 1, '2026-10-25 02:08:34'),
(13, NULL, 3, 'Hipotesis atau proposisi ditulis sesuai kebutuhan', 3, 1, '2026-10-25 02:08:34'),
(14, NULL, 4, 'Desain dan metode penelitian sesuai tujuan', 1, 1, '2026-10-25 02:08:34'),
(15, NULL, 4, 'Populasi, sampel, atau sumber data jelas', 2, 1, '2026-10-25 02:08:34'),
(16, NULL, 4, 'Teknik pengumpulan dan analisis data dijelaskan', 3, 1, '2026-10-25 02:08:34'),
(17, NULL, 5, 'Hasil disajikan sistematis', 1, 1, '2026-10-25 02:08:34'),
(18, NULL, 5, 'Pembahasan menjawab rumusan masalah', 2, 1, '2026-10-25 02:08:34'),
(19, NULL, 5, 'Kesimpulan sesuai tujuan penelitian', 3, 1, '2026-10-25 02:08:34'),
(20, NULL, 6, 'Kesimpulan merangkum hasil utama', 1, 1, '2026-10-25 02:08:34'),
(21, NULL, 6, 'Saran relevan dan dapat ditindaklanjuti', 2, 1, '2026-10-25 02:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_work_id` bigint(20) UNSIGNED NOT NULL,
  `document_type_id` smallint(5) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `academic_work_id`, `document_type_id`, `title`, `created_at`, `updated_at`) VALUES
(10, 5, 7, 'ADITYA NUGROHO (09503244013).pdf', '2026-10-27 07:34:35', '2026-10-27 07:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `work_type_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `work_type_id`, `code`, `name`, `sort_order`, `is_active`, `created_at`) VALUES
(1, NULL, 'proposal', 'Proposal Penelitian', 1, 1, '2026-10-25 02:08:33'),
(2, NULL, 'chapter_1', 'Bab I Pendahuluan', 2, 1, '2026-10-25 02:08:33'),
(3, NULL, 'chapter_2', 'Bab II Tinjauan Pustaka', 3, 1, '2026-10-25 02:08:33'),
(4, NULL, 'chapter_3', 'Bab III Metode Penelitian', 4, 1, '2026-10-25 02:08:33'),
(5, NULL, 'chapter_4', 'Bab IV Hasil dan Pembahasan', 5, 1, '2026-10-25 02:08:33'),
(6, NULL, 'chapter_5', 'Bab V Penutup', 6, 1, '2026-10-25 02:08:33'),
(7, NULL, 'full_document', 'Naskah Lengkap', 7, 1, '2026-10-25 02:08:33'),
(8, NULL, 'revision', 'Dokumen Revisi', 8, 1, '2026-10-25 02:08:33'),
(9, NULL, 'other', 'Dokumen Lainnya', 9, 1, '2026-10-25 02:08:33'),
(10, 3, 'publication_manuscript', 'Naskah Publikasi', 10, 1, '2026-10-25 02:08:33'),
(11, 3, 'progress_report', 'Laporan Kemajuan Penelitian', 11, 1, '2026-10-25 02:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `version_number` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_mime` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size_bytes` bigint(20) UNSIGNED NOT NULL,
  `page_count` int(10) UNSIGNED DEFAULT NULL,
  `student_note` text COLLATE utf8mb4_unicode_ci,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_current` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_versions`
--

INSERT INTO `document_versions` (`id`, `document_id`, `version_number`, `original_filename`, `stored_filename`, `file_path`, `file_mime`, `file_size_bytes`, `page_count`, `student_note`, `uploaded_by`, `uploaded_at`, `is_current`) VALUES
(1, 10, 1, 'ADITYA NUGROHO (09503244013).pdf', '06e62c4961b863df3567b989da4eba0b.pdf', 'C:\\laragon\\www\\simbita-app/storage/documents/06e62c4961b863df3567b989da4eba0b.pdf', 'application/pdf', 12090454, 98, '', 20, '2026-10-27 14:34:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_expertise`
--

CREATE TABLE `lecturer_expertise` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `keyword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_profiles`
--

CREATE TABLE `lecturer_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `lecturer_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_degree` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expertise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lecturer_profiles`
--

INSERT INTO `lecturer_profiles` (`id`, `user_id`, `lecturer_number`, `academic_degree`, `department`, `expertise`, `phone`, `created_at`, `updated_at`) VALUES
(6, 21, '001001005', 'S.I.Pust', 'Ilmu Komunikasi', 'Coding, Development', '082147201903', '2026-10-27 03:55:34', '2026-10-27 03:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('system','info','success','warning','danger','review','upload','status_change','deadline','milestone','comment','approval','title_proposals','bimbingan_logs','bookings','academic_works') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `related_table` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `related_table`, `related_id`, `link`, `is_read`, `read_at`, `created_at`) VALUES
(1, 21, 'Dokumen Baru Diupload', 'Yolis Libman mengunggah ADITYA NUGROHO (09503244013).pdf (Versi 1)', 'system', 'document_versions', 1, '/lecturer/review?id=5', 1, '2026-10-27 14:35:32', '2026-10-27 07:34:35'),
(2, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 17:33:37', '2026-10-28 10:28:26'),
(3, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 17:32:50', '2026-10-28 10:28:32'),
(4, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 18:26:04', '2026-10-28 11:25:26'),
(5, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 18:26:04', '2026-10-28 11:25:30'),
(6, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 18:36:25', '2026-10-28 11:33:56'),
(7, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 18:34:14', '2026-10-28 11:33:59'),
(8, 20, 'Dokumen Anda Telah Perlu Revisi', 'Antonius Yulianus M.Peta. S.I.Pust telah menyelesaikan review dengan keputusan: Perlu Revisi', 'review', 'document_versions', 1, '/student/history', 1, '2026-10-28 18:57:51', '2026-10-28 11:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_version_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `review_status` enum('pending','in_review','needs_revision','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `general_note` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `document_version_id`, `reviewer_id`, `review_status`, `general_note`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(10, 1, 21, 'needs_revision', 'Beri ABSTRAK versi Inggris dan Ganti Judulnya.', '2026-10-28 18:57:36', '2026-10-27 07:34:35', '2026-10-28 11:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `review_checklist_results`
--

CREATE TABLE `review_checklist_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `checklist_item_id` bigint(20) UNSIGNED NOT NULL,
  `result` enum('pending','complete','incomplete','not_applicable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_comments`
--

CREATE TABLE `review_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `document_version_id` bigint(20) UNSIGNED NOT NULL,
  `page_number` int(10) UNSIGNED NOT NULL,
  `position_x` decimal(5,2) NOT NULL,
  `position_y` decimal(5,2) NOT NULL,
  `comment_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('minor','important','mandatory') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'important',
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `review_comments`
--

INSERT INTO `review_comments` (`id`, `review_id`, `document_version_id`, `page_number`, `position_x`, `position_y`, `comment_text`, `priority`, `is_resolved`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 1, 23.02, 13.16, 'GANTI JUDUL', 'important', 1, '2026-10-28 18:54:59', '2026-10-28 10:03:16', '2026-10-28 11:54:59'),
(2, 10, 1, 1, 23.02, 13.16, 'GANTI JUDUL', 'important', 1, '2026-10-28 18:55:03', '2026-10-28 10:08:25', '2026-10-28 11:55:03'),
(3, 10, 1, 1, 23.02, 13.16, 'GANTI JUDUL', 'important', 1, '2026-10-28 18:55:06', '2026-10-28 10:08:27', '2026-10-28 11:55:06'),
(4, 10, 1, 1, 23.02, 13.16, 'GANTI JUDUL', 'important', 1, '2026-10-28 18:55:10', '2026-10-28 10:08:29', '2026-10-28 11:55:10'),
(5, 10, 1, 1, 23.02, 13.16, 'GANTI JUDUL', 'important', 1, '2026-10-28 18:55:14', '2026-10-28 10:09:28', '2026-10-28 11:55:14'),
(6, 10, 1, 1, 47.05, 11.85, 'test', 'important', 1, '2026-10-28 18:55:18', '2026-10-28 10:09:41', '2026-10-28 11:55:18'),
(7, 10, 1, 1, 64.70, 24.10, 'tws', 'important', 1, '2026-10-28 18:55:26', '2026-10-28 10:12:09', '2026-10-28 11:55:26'),
(8, 10, 1, 1, 35.62, 17.67, 'tes', 'important', 1, '2026-10-28 18:55:22', '2026-10-28 10:16:14', '2026-10-28 11:55:22'),
(9, 10, 1, 2, 50.07, 47.31, 'GANTI JUDUL', 'important', 1, '2026-10-28 18:55:47', '2026-10-28 10:16:43', '2026-10-28 11:55:47'),
(10, 10, 1, 5, 47.89, 68.49, 'Buatkan juga versi Bahasa Inggris untuk ABSTRAK', 'mandatory', 1, '2026-10-28 18:56:00', '2026-10-28 10:17:49', '2026-10-28 11:56:00'),
(11, 10, 1, 1, 50.47, 14.08, 'ganti judul', 'mandatory', 1, '2026-10-28 18:55:30', '2026-10-28 10:25:23', '2026-10-28 11:55:30'),
(12, 10, 1, 5, 47.28, 40.96, 'Mana ABSTRAK versi Inggrisnya?', 'mandatory', 1, '2026-10-28 18:55:55', '2026-10-28 10:25:52', '2026-10-28 11:55:55'),
(13, 10, 1, 1, 46.27, 17.66, 'ganti judul', 'mandatory', 1, '2026-10-28 18:55:51', '2026-10-28 11:23:57', '2026-10-28 11:55:51'),
(14, 10, 1, 5, 48.12, 41.20, 'Beri versi Bahasa Inggris untuk ABSTRAK', 'mandatory', 1, '2026-10-28 18:55:38', '2026-10-28 11:24:24', '2026-10-28 11:55:38'),
(15, 10, 1, 16, 54.17, 15.28, 'Pendahuluan jangan terlalu panjangan', 'mandatory', 1, '2026-10-28 18:54:47', '2026-10-28 11:24:57', '2026-10-28 11:54:47'),
(16, 10, 1, 1, 52.82, 15.64, 'TESTING', 'mandatory', 1, '2026-10-28 18:55:43', '2026-10-28 11:33:27', '2026-10-28 11:55:43'),
(17, 10, 1, 5, 52.82, 20.38, 'WAJIB', 'mandatory', 1, '2026-10-28 18:54:53', '2026-10-28 11:33:42', '2026-10-28 11:54:53'),
(18, 10, 1, 1, 46.43, 16.60, 'tes', 'important', 1, '2026-10-28 18:58:18', '2026-10-28 11:56:50', '2026-10-28 11:58:18'),
(19, 10, 1, 5, 53.66, 20.51, 'ABSTRAK INGGRIS MANA?', 'mandatory', 1, '2026-10-28 18:58:25', '2026-10-28 11:57:08', '2026-10-28 11:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_slots`
--

CREATE TABLE `schedule_slots` (
  `id` int(10) UNSIGNED NOT NULL,
  `lecturer_id` int(10) UNSIGNED NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_students` int(11) NOT NULL DEFAULT '3',
  `mode` enum('offline','online') DEFAULT 'offline',
  `location` varchar(100) DEFAULT NULL,
  `link_meeting` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_level` enum('s1','s2','s3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `study_program` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faculty` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollment_year` smallint(5) UNSIGNED NOT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `student_number`, `degree_level`, `study_program`, `faculty`, `enrollment_year`, `phone`, `created_at`, `updated_at`) VALUES
(5, 20, '202601001', 's1', 'Ilmu Komunikasi', 'Fakultas Teknik & Informasi', 2024, '082147201903', '2026-10-27 01:39:10', '2026-10-27 01:39:10');

-- --------------------------------------------------------

--
-- Table structure for table `supervision_assignments`
--

CREATE TABLE `supervision_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_work_id` bigint(20) UNSIGNED NOT NULL,
  `lecturer_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_role` enum('supervisor_1','supervisor_2','promotor','co_promotor','examiner','advisor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supervision_assignments`
--

INSERT INTO `supervision_assignments` (`id`, `academic_work_id`, `lecturer_id`, `assignment_role`, `assigned_by`, `assigned_at`, `is_active`) VALUES
(7, 5, 6, 'supervisor_1', 21, '2026-10-27 14:31:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ta_repository`
--

CREATE TABLE `ta_repository` (
  `id` int(10) UNSIGNED NOT NULL,
  `academic_work_id` int(10) UNSIGNED NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `download_count` int(11) DEFAULT '0',
  `is_public` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ta_repository`
--

INSERT INTO `ta_repository` (`id`, `academic_work_id`, `published_at`, `download_count`, `is_public`, `created_at`) VALUES
(1, 5, '2026-10-27 14:32:40', 0, 1, '2026-10-27 07:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `title_proposals`
--

CREATE TABLE `title_proposals` (
  `id` int(10) UNSIGNED NOT NULL,
  `proposer_role` enum('student','lecturer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `proposer_user_id` bigint(20) UNSIGNED NOT NULL,
  `target_user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_type_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','accepted','rejected','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `response_note` text COLLATE utf8mb4_unicode_ci,
  `responded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `title_proposals`
--

INSERT INTO `title_proposals` (`id`, `proposer_role`, `proposer_user_id`, `target_user_id`, `title`, `description`, `keywords`, `work_type_id`, `status`, `response_note`, `responded_by`, `responded_at`, `created_at`, `updated_at`) VALUES
(1, 'student', 20, 21, 'PERAN MAHASISWA DALAM MEMAJUKAN LITERASI DIGITAL', 'Mahasiswa dapat turut serta mengembangkan dan memajukan literasi secara digital melalu program baca keliling.', NULL, 1, 'accepted', NULL, 21, '2026-10-27 14:31:59', '2026-10-27 07:31:34', '2026-10-27 07:31:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','lecturer','student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `is_active`, `remember_token`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Administrator SIMBITA', 'admin@unimof.ac.id', '$2y$12$RaM6rfV.mbzDQ/PoYBPB/usoNkItv5POsyLw67S6eRvtMwT33CdXG', 'admin', 1, NULL, '2026-10-29 06:48:42', '2026-10-25 02:08:34', '2026-10-28 23:48:42'),
(17, 'Andi Pratama', 'mhs1@unimof.ac.id', '$2y$12$RaM6rfV.mbzDQ/PoYBPB/usoNkItv5POsyLw67S6eRvtMwT33CdXG', 'student', 1, NULL, NULL, '2026-10-26 01:18:08', '2026-10-26 02:15:01'),
(18, 'Dewi Lestari', 'mhs2@unimof.ac.id', '$2y$12$RaM6rfV.mbzDQ/PoYBPB/usoNkItv5POsyLw67S6eRvtMwT33CdXG', 'student', 1, NULL, NULL, '2026-10-26 01:18:08', '2026-10-26 02:15:01'),
(19, 'Rizky Fauzan', 'mhs3@unimof.ac.id', '$2y$12$RaM6rfV.mbzDQ/PoYBPB/usoNkItv5POsyLw67S6eRvtMwT33CdXG', 'student', 1, NULL, NULL, '2026-10-26 01:18:08', '2026-10-26 02:15:01'),
(20, 'Yolis Libman', 'wewurtraditionalmusic35@gmail.com', '$2y$12$cyX39Eg4gCiy7YwDzvxmY.RazOeaGCSk/4J0GuJib8zsBWGb83QEW', 'student', 1, NULL, '2026-10-28 18:57:47', '2026-10-27 01:39:10', '2026-10-28 11:57:47'),
(21, 'Antonius Yulianus M.Peta. S.I.Pust', 'julianusmitankesik57@gmail.com', '$2y$10$UpGFWQFhTIc2dfmZx5A.4e8vb44r3py1z6DivDv98rFiwVPy.XFvC', 'lecturer', 1, NULL, '2026-10-28 19:46:11', '2026-10-27 03:55:34', '2026-10-28 12:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `work_milestones`
--

CREATE TABLE `work_milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_work_id` bigint(20) UNSIGNED NOT NULL,
  `milestone_type` enum('proposal','feasibility_test','writing','progress_exam','revision','closed_exam','defense_preparation','defense','final_revision','publication') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `due_date` date NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_types`
--

CREATE TABLE `work_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_level` enum('s1','s2','s3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_types`
--

INSERT INTO `work_types` (`id`, `code`, `name`, `degree_level`, `is_active`, `created_at`) VALUES
(1, 'skripsi', 'Skripsi', 's1', 1, '2026-10-25 02:08:33'),
(2, 'tesis', 'Tesis', 's2', 1, '2026-10-25 02:08:33'),
(3, 'disertasi', 'Disertasi', 's3', 1, '2026-10-25 02:08:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_works`
--
ALTER TABLE `academic_works`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_work_student_type` (`student_id`,`work_type_id`),
  ADD KEY `fk_work_type` (`work_type_id`),
  ADD KEY `idx_work_status` (`status`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_user_time` (`user_id`,`created_at`),
  ADD KEY `idx_log_action` (`action`);

--
-- Indexes for table `bimbingan_logs`
--
ALTER TABLE `bimbingan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bl_work` (`academic_work_id`),
  ADD KEY `idx_bl_date` (`meeting_date`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bk_slot` (`schedule_slot_id`),
  ADD KEY `idx_bk_work` (`academic_work_id`);

--
-- Indexes for table `checklist_items`
--
ALTER TABLE `checklist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_checklist_scope` (`work_type_id`,`document_type_id`,`item_order`),
  ADD KEY `fk_checklist_document_type` (`document_type_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_document_work_type` (`academic_work_id`,`document_type_id`),
  ADD KEY `fk_document_type` (`document_type_id`),
  ADD KEY `idx_document_work` (`academic_work_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_document_type_code` (`work_type_id`,`code`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_document_version` (`document_id`,`version_number`),
  ADD KEY `fk_version_uploader` (`uploaded_by`),
  ADD KEY `idx_version_current` (`document_id`,`is_current`);

--
-- Indexes for table `lecturer_expertise`
--
ALTER TABLE `lecturer_expertise`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_le_lec` (`lecturer_id`);

--
-- Indexes for table `lecturer_profiles`
--
ALTER TABLE `lecturer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notification_unread` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_review_version` (`document_version_id`),
  ADD KEY `idx_review_status` (`review_status`),
  ADD KEY `idx_review_reviewer` (`reviewer_id`);

--
-- Indexes for table `review_checklist_results`
--
ALTER TABLE `review_checklist_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_review_checklist` (`review_id`,`checklist_item_id`),
  ADD KEY `fk_result_item` (`checklist_item_id`);

--
-- Indexes for table `review_comments`
--
ALTER TABLE `review_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comment_page` (`review_id`,`page_number`),
  ADD KEY `idx_comment_priority` (`priority`),
  ADD KEY `idx_comment_unresolved` (`review_id`,`is_resolved`),
  ADD KEY `idx_comment_version` (`document_version_id`,`page_number`);

--
-- Indexes for table `schedule_slots`
--
ALTER TABLE `schedule_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`lecturer_id`,`slot_date`,`start_time`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `supervision_assignments`
--
ALTER TABLE `supervision_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assignment` (`academic_work_id`,`lecturer_id`,`assignment_role`,`is_active`),
  ADD KEY `fk_assignment_lecturer` (`lecturer_id`),
  ADD KEY `fk_assignment_admin` (`assigned_by`),
  ADD KEY `idx_assignment_work` (`academic_work_id`,`is_active`);

--
-- Indexes for table `ta_repository`
--
ALTER TABLE `ta_repository`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academic_work_id` (`academic_work_id`);

--
-- Indexes for table `title_proposals`
--
ALTER TABLE `title_proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tp_proposer` (`proposer_user_id`),
  ADD KEY `idx_tp_target` (`target_user_id`),
  ADD KEY `idx_tp_status` (`status`),
  ADD KEY `idx_tp_worktype` (`work_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_active` (`role`,`is_active`);

--
-- Indexes for table `work_milestones`
--
ALTER TABLE `work_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_milestone_admin` (`created_by`),
  ADD KEY `idx_milestone_due` (`academic_work_id`,`due_date`);

--
-- Indexes for table `work_types`
--
ALTER TABLE `work_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_works`
--
ALTER TABLE `academic_works`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `bimbingan_logs`
--
ALTER TABLE `bimbingan_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklist_items`
--
ALTER TABLE `checklist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lecturer_expertise`
--
ALTER TABLE `lecturer_expertise`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lecturer_profiles`
--
ALTER TABLE `lecturer_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `review_checklist_results`
--
ALTER TABLE `review_checklist_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_comments`
--
ALTER TABLE `review_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `schedule_slots`
--
ALTER TABLE `schedule_slots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supervision_assignments`
--
ALTER TABLE `supervision_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ta_repository`
--
ALTER TABLE `ta_repository`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `title_proposals`
--
ALTER TABLE `title_proposals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `work_milestones`
--
ALTER TABLE `work_milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work_types`
--
ALTER TABLE `work_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_works`
--
ALTER TABLE `academic_works`
  ADD CONSTRAINT `fk_work_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_work_type` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`);

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `checklist_items`
--
ALTER TABLE `checklist_items`
  ADD CONSTRAINT `fk_checklist_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_checklist_work_type` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  ADD CONSTRAINT `fk_document_work` FOREIGN KEY (`academic_work_id`) REFERENCES `academic_works` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_types`
--
ALTER TABLE `document_types`
  ADD CONSTRAINT `fk_document_type_work` FOREIGN KEY (`work_type_id`) REFERENCES `work_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `fk_version_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_version_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `lecturer_profiles`
--
ALTER TABLE `lecturer_profiles`
  ADD CONSTRAINT `fk_lecturer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_review_version` FOREIGN KEY (`document_version_id`) REFERENCES `document_versions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_checklist_results`
--
ALTER TABLE `review_checklist_results`
  ADD CONSTRAINT `fk_result_item` FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_items` (`id`),
  ADD CONSTRAINT `fk_result_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_comments`
--
ALTER TABLE `review_comments`
  ADD CONSTRAINT `fk_comment_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_version` FOREIGN KEY (`document_version_id`) REFERENCES `document_versions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supervision_assignments`
--
ALTER TABLE `supervision_assignments`
  ADD CONSTRAINT `fk_assignment_admin` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_assignment_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_work` FOREIGN KEY (`academic_work_id`) REFERENCES `academic_works` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_milestones`
--
ALTER TABLE `work_milestones`
  ADD CONSTRAINT `fk_milestone_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_milestone_work` FOREIGN KEY (`academic_work_id`) REFERENCES `academic_works` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
