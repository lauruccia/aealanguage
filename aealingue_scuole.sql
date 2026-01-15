-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Gen 15, 2026 alle 10:43
-- Versione del server: 8.0.44
-- Versione PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aealingue_scuole`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `closure_days`
--

CREATE TABLE `closure_days` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `closure_days`
--

INSERT INTO `closure_days` (`id`, `date`, `reason`, `created_at`, `updated_at`) VALUES
(1, '2026-01-01', 'Capodanno', '2026-01-14 13:36:09', '2026-01-14 13:36:09'),
(2, '2026-01-06', 'Epifania', '2026-01-14 13:36:19', '2026-01-14 13:36:19'),
(3, '2026-04-05', 'Pasqua', '2026-01-15 06:50:09', '2026-01-15 06:50:09'),
(4, '2026-04-06', 'Pasquetta', '2026-01-15 06:50:21', '2026-01-15 06:50:21'),
(5, '2026-08-15', 'Ferragosto', '2026-01-15 06:50:31', '2026-01-15 06:50:31'),
(6, '2026-06-25', 'Festa della Liberazione', '2026-01-15 06:51:43', '2026-01-15 06:51:43'),
(7, '2026-05-01', 'Festa dei Lavoratori', '2026-01-15 06:51:55', '2026-01-15 06:51:55'),
(8, '2026-06-02', 'Festa della Repubblica', '2026-01-15 06:52:05', '2026-01-15 06:52:05'),
(9, '2026-11-01', 'Tutti i Santi', '2026-01-15 06:52:16', '2026-01-15 06:52:16'),
(10, '2026-12-08', 'Immacolata', '2026-01-15 06:52:25', '2026-01-15 06:52:25'),
(11, '2026-12-25', 'Natale', '2026-01-15 06:52:36', '2026-01-15 06:52:36'),
(12, '2026-12-26', 'Santo Stefano', '2026-01-15 06:52:46', '2026-01-15 06:52:46');

-- --------------------------------------------------------

--
-- Struttura della tabella `courses`
--

CREATE TABLE `courses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tassa_iscrizione` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `lessons_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `courses`
--

INSERT INTO `courses` (`id`, `name`, `subject_id`, `prezzo`, `tassa_iscrizione`, `description`, `lessons_count`, `created_at`, `updated_at`) VALUES
(1, 'CDCC Corso MIX 12+3 ', 3, 560.00, 70.00, NULL, 15, '2026-01-14 08:45:59', '2026-01-14 08:45:59'),
(2, 'S23 Corso MIX 15+5', 3, 820.00, 70.00, NULL, 20, '2026-01-14 13:35:26', '2026-01-14 13:35:26');

-- --------------------------------------------------------

--
-- Struttura della tabella `enrollments`
--

CREATE TABLE `enrollments` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `installments_count` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `status` enum('attivo','concluso','annullato','sospeso') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attivo',
  `course_price_eur` decimal(10,2) DEFAULT NULL,
  `deposit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `registration_fee_eur` decimal(10,2) DEFAULT NULL,
  `payment_plan` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrolled_at` date DEFAULT NULL,
  `starts_at` date DEFAULT NULL,
  `weekly_day` tinyint UNSIGNED DEFAULT NULL,
  `weekly_time` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lesson_duration_minutes` smallint UNSIGNED NOT NULL DEFAULT '60',
  `purchased_minutes` int UNSIGNED NOT NULL DEFAULT '0',
  `default_teacher_id` bigint UNSIGNED DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `first_installment_due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `installments_count`, `status`, `course_price_eur`, `deposit`, `registration_fee_eur`, `payment_plan`, `enrolled_at`, `starts_at`, `weekly_day`, `weekly_time`, `lesson_duration_minutes`, `purchased_minutes`, `default_teacher_id`, `ends_at`, `first_installment_due_date`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 5, 'attivo', 560.00, 60.00, 70.00, 'monthly', '2026-01-14', '2026-01-15', 4, '13:00', 60, 0, 1, '2026-04-23', '2026-01-14', '2026-01-14 08:49:47', '2026-01-14 09:37:34');

-- --------------------------------------------------------

--
-- Struttura della tabella `enrollment_hour_movements`
--

CREATE TABLE `enrollment_hour_movements` (
  `id` bigint UNSIGNED NOT NULL,
  `enrollment_id` bigint UNSIGNED NOT NULL,
  `minutes` int NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_id` bigint UNSIGNED DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `occurred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `enrollment_hour_movements`
--

INSERT INTO `enrollment_hour_movements` (`id`, `enrollment_id`, `minutes`, `type`, `lesson_id`, `note`, `occurred_at`, `created_at`, `updated_at`) VALUES
(1, 1, 900, 'purchase', NULL, 'Acquisto iniziale (da corso)', '2026-01-14 08:49:47', '2026-01-14 08:49:47', '2026-01-14 08:49:47'),
(2, 1, -60, 'lesson', 1, 'Consumo ore per lezione (completed o cancelled_counted)', '2026-01-15 07:45:29', '2026-01-15 07:45:29', '2026-01-15 07:45:29');

-- --------------------------------------------------------

--
-- Struttura della tabella `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `installments`
--

CREATE TABLE `installments` (
  `id` bigint UNSIGNED NOT NULL,
  `enrollment_id` bigint UNSIGNED NOT NULL,
  `number` tinyint UNSIGNED NOT NULL,
  `due_date` date NOT NULL,
  `amount_cents` int UNSIGNED NOT NULL,
  `paid_cents` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `installments`
--

INSERT INTO `installments` (`id`, `enrollment_id`, `number`, `due_date`, `amount_cents`, `paid_cents`, `status`, `created_at`, `updated_at`) VALUES
(8, 1, 0, '2026-01-14', 7000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(9, 1, 1, '2026-01-14', 6000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(10, 1, 2, '2026-01-14', 10000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(11, 1, 3, '2026-02-14', 10000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(12, 1, 4, '2026-03-14', 10000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(13, 1, 5, '2026-04-14', 10000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(14, 1, 6, '2026-05-14', 10000, 0, 'da_pagare', '2026-01-14 09:37:34', '2026-01-14 09:37:34');

-- --------------------------------------------------------

--
-- Struttura della tabella `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `enrollment_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `lesson_number` int UNSIGNED NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `duration_minutes` int UNSIGNED NOT NULL DEFAULT '60',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `cancelled_at` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_start_at` timestamp NULL DEFAULT NULL,
  `previous_end_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `lessons`
--

INSERT INTO `lessons` (`id`, `enrollment_id`, `student_id`, `course_id`, `teacher_id`, `lesson_number`, `starts_at`, `ends_at`, `duration_minutes`, `status`, `cancelled_at`, `cancel_reason`, `previous_start_at`, `previous_end_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 1, 1, '2026-01-15 13:00:00', '2026-01-15 14:00:00', 60, 'cancelled_counted', '2026-01-15 08:45:23', 'studente', NULL, NULL, 'test', '2026-01-14 09:37:34', '2026-01-15 07:45:29'),
(2, 1, 2, 1, 1, 2, '2026-01-16 13:00:00', '2026-01-22 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-15 07:45:12'),
(3, 1, 2, 1, 1, 3, '2026-01-29 13:00:00', '2026-01-29 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(4, 1, 2, 1, 1, 4, '2026-02-05 13:00:00', '2026-02-05 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(5, 1, 2, 1, 1, 5, '2026-02-12 13:00:00', '2026-02-12 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(6, 1, 2, 1, 1, 6, '2026-02-19 13:00:00', '2026-02-19 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(7, 1, 2, 1, 1, 7, '2026-02-26 13:00:00', '2026-02-26 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(8, 1, 2, 1, 1, 8, '2026-03-05 13:00:00', '2026-03-05 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(9, 1, 2, 1, 1, 9, '2026-03-12 13:00:00', '2026-03-12 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(10, 1, 2, 1, 1, 10, '2026-03-19 13:00:00', '2026-03-19 14:00:00', 60, 'cancelled_recover', '2026-01-15 07:49:00', 'richiesta studente', NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-15 06:49:09'),
(11, 1, 2, 1, 1, 11, '2026-03-26 13:00:00', '2026-03-26 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(12, 1, 2, 1, 1, 12, '2026-04-02 13:00:00', '2026-04-02 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(13, 1, 2, 1, 1, 13, '2026-04-09 13:00:00', '2026-04-09 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(14, 1, 2, 1, 1, 14, '2026-04-16 13:00:00', '2026-04-16 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34'),
(15, 1, 2, 1, 1, 15, '2026-04-23 13:00:00', '2026-04-23 14:00:00', 60, 'scheduled', NULL, NULL, NULL, NULL, NULL, '2026-01-14 09:37:34', '2026-01-14 09:37:34');

-- --------------------------------------------------------

--
-- Struttura della tabella `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_09_151404_create_students_table', 1),
(5, '2026_01_09_151434_create_teachers_table', 1),
(6, '2026_01_09_151454_create_courses_table', 1),
(7, '2026_01_09_161945_add_pricing_fields_to_courses_table', 1),
(8, '2026_01_09_162000_create_enrollments_table', 1),
(9, '2026_01_09_162010_create_installments_table', 1),
(10, '2026_01_09_162020_create_payments_table', 1),
(11, '2026_01_10_090242_add_installment_plan_fields_to_enrollments_table', 2),
(12, '2026_01_10_092803_add_profile_fields_to_students_table', 2),
(13, '2026_01_10_093206_add_guardian_fields_to_students_table', 2),
(14, '2026_01_10_100850_add_is_minor_to_students_table', 2),
(15, '2026_01_10_101415_fix_installments_status_enum', 2),
(16, '2026_01_10_104221_add_guardian_columns_to_students_table', 2),
(17, '2026_01_10_152216_alter_installments_status_to_string', 2),
(18, '2026_01_10_160541_add_price_fields_to_enrollments_table', 2),
(19, '2026_01_10_161033_add_payment_plan_fields_to_enrollments_table', 2),
(20, '2026_01_12_071633_add_deposit_to_enrollments_table', 2),
(21, '2026_01_12_085216_create_lessons_table', 2),
(22, '2026_01_12_085242_create_closure_days_table', 2),
(23, '2026_01_12_091938_add_schedule_fields_to_enrollments_table', 2),
(24, '2026_01_12_122054_add_fields_to_teachers_table', 2),
(25, '2026_01_12_122140_create_subjects_table', 2),
(26, '2026_01_12_122204_create_subject_teacher_table', 2),
(27, '2026_01_12_122931_add_personal_and_fiscal_fields_to_teachers_table', 2),
(28, '2026_01_12_133813_create_permission_tables', 2),
(29, '2026_01_12_144416_add_user_id_to_teachers_table', 2),
(30, '2026_01_13_080447_create_enrollment_hour_movements_table', 2),
(31, '2026_01_13_080535_add_cancellation_fields_to_lessons_table', 2),
(32, '2026_01_13_081021_add_enrollment_id_to_lessons_table', 2),
(33, '2026_01_13_092201_add_purchased_minutes_to_enrollments_table', 2),
(34, '2026_01_13_122536_add_subject_id_to_courses_table', 2),
(35, '2026_01_13_153533_alter_enrollments_status_enum_to_masculine', 2),
(36, '2026_01_13_163702_add_user_id_to_students_table', 3),
(37, '2026_01_13_163841_add_must_change_password_to_users_table', 4),
(38, '2026_01_14_093736_drop_price_from_courses_table', 5);

-- --------------------------------------------------------

--
-- Struttura della tabella `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(4, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4),
(4, 'App\\Models\\User', 5),
(5, 'App\\Models\\User', 6),
(4, 'App\\Models\\User', 7),
(4, 'App\\Models\\User', 8),
(3, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(3, 'App\\Models\\User', 11),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13);

-- --------------------------------------------------------

--
-- Struttura della tabella `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `enrollment_id` bigint UNSIGNED NOT NULL,
  `installment_id` bigint UNSIGNED DEFAULT NULL,
  `paid_at` date NOT NULL,
  `amount_cents` int UNSIGNED NOT NULL,
  `kind` enum('tassa_iscrizione','acconto','rata','saldo','altro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rata',
  `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'web', '2026-01-13 16:09:07', '2026-01-13 16:09:07'),
(2, 'amministrazione', 'web', '2026-01-13 16:09:07', '2026-01-13 16:09:07'),
(3, 'docente', 'web', '2026-01-13 16:09:07', '2026-01-13 16:09:07'),
(4, 'studente', 'web', '2026-01-13 16:09:07', '2026-01-13 16:09:07'),
(5, 'segreteria', 'web', '2026-01-13 16:09:07', '2026-01-13 16:09:07');

-- --------------------------------------------------------

--
-- Struttura della tabella `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0gWkmaO3Nb8rQsVmX8IyysoKGvSwvgyb54zU7zZx', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidkdhcW1HZGwweG13Z2FkeDZvRjk4T0I0VktuZW1NcUNZVWtvS0hsTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9teS1sZXNzb25zIjtzOjU6InJvdXRlIjtzOjMxOiJmaWxhbWVudC5hZG1pbi5wYWdlcy5teS1sZXNzb25zIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNDE0NTc3ODExZGQ5N2VhZjc4NDQ5MDM0MzRhNWM2YjA4NWJiOTZiMWQ1NmQxYTAxMDNhMzM1ZjcyMGUzY2UzOSI7fQ==', 1768391050),
('ATd9WwKdo6M5eDJhQqcQfCSz6uvcxGtA24keKRxK', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiczkxZXUzREhlTmZOWE0wM0tkRnhHVWFsMmYzWmRTTWRyYnhwb3htaCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4iO3M6NToicm91dGUiO3M6MzY6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmFkbWluLWRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImFkOWVkZmNiNjcwMTczYjEzNGZlZTJhM2U4MDAxYzYxNDdkNTI2NjY2ZTQzNmMyZmQ2ZTY3NTMzZDQyNDE4YTEiO3M6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1768391042),
('gjB4ggH2JL3ThaQXfDJ7Xa9B2197bGubfJCE6fZ3', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVVNwTXlRZlZUeGt0ZTB3aXFTTVFJbDBSeURDZDUzVTFLdVN4dG9ybyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbiI7czo1OiJyb3V0ZSI7czozNjoiZmlsYW1lbnQuYWRtaW4ucGFnZXMuYWRtaW4tZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNDE0NTc3ODExZGQ5N2VhZjc4NDQ5MDM0MzRhNWM2YjA4NWJiOTZiMWQ1NmQxYTAxMDNhMzM1ZjcyMGUzY2UzOSI7fQ==', 1768387910),
('YFJ278incNLowifxPs1Z7bNGbEo3rX1or61mQE5y', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiUXdKZnFaM2RORlY5Mlhpa0FwbE1qcFpDY2x0ZVRVcThoSzgzT3BPMiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4iO3M6NToicm91dGUiO3M6MzY6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmFkbWluLWRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjcxNWU3NDNlNDAyYjhmNjU3ZjA5YjMwYTFhZmVhMDYxNDViNDY0ZGM1ZjhhNzBjMWYxYTIwZDllYjcxMzk3NDAiO30=', 1768386840),
('ZJkB8NGFHYvTztnH5SN4m4dazff5yyCyppEtE3Rx', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZEd3RFRQbzkzcklvN0xlMTZ0R0lTMVpGNGJyc1pGM2N3RkZoYVhYUCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vbXktbGVzc29ucyI7czo1OiJyb3V0ZSI7czozMToiZmlsYW1lbnQuYWRtaW4ucGFnZXMubXktbGVzc29ucyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjcxNWU3NDNlNDAyYjhmNjU3ZjA5YjMwYTFhZmVhMDYxNDViNDY0ZGM1ZjhhNzBjMWYxYTIwZDllYjcxMzk3NDAiO30=', 1768389166);

-- --------------------------------------------------------

--
-- Struttura della tabella `students`
--

CREATE TABLE `students` (
  `id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `is_minor` tinyint(1) NOT NULL DEFAULT '0',
  `guardian_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `students`
--

INSERT INTO `students` (`id`, `first_name`, `last_name`, `email`, `phone`, `birth_date`, `is_minor`, `guardian_role`, `guardian_name`, `guardian_email`, `guardian_phone`, `birth_place`, `birth_country`, `tax_code`, `vat_number`, `address_line`, `postal_code`, `city`, `province`, `country`, `created_at`, `updated_at`, `user_id`) VALUES
(1, 'VILMA', 'ACCETTA', 'vilmaaccetta@virgilio.it', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'CCTVLM73S60H703Y', NULL, 'VIA DEI BERSAGLIERI Palazzina Logistica Guardia Costiera 10/B', '00143', 'Roma', 'RM', 'Italia', '2026-01-14 08:48:31', '2026-01-14 08:48:31', 7),
(2, 'Laura', 'Gullì', 'sitireggiocal@gmail.com', '3351502213', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Italia', NULL, NULL, 'Largo dei Manganelli 2', '89065', 'Motta San Giovanni', 'RC', 'Italia', '2026-01-14 08:48:59', '2026-01-14 08:48:59', 8);

-- --------------------------------------------------------

--
-- Struttura della tabella `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Arabo', '2026-01-14 09:36:26', '2026-01-14 09:36:26'),
(2, 'Francese', '2026-01-14 09:36:26', '2026-01-14 09:36:26'),
(3, 'Inglese', '2026-01-14 09:36:26', '2026-01-14 09:36:26'),
(4, 'Italiano per stranieri', '2026-01-14 09:36:26', '2026-01-14 09:36:26'),
(5, 'Spagnolo', '2026-01-14 09:36:26', '2026-01-14 09:36:26'),
(6, 'Tedesco', '2026-01-14 09:36:26', '2026-01-14 09:36:26');

-- --------------------------------------------------------

--
-- Struttura della tabella `subject_teacher`
--

CREATE TABLE `subject_teacher` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `subject_teacher`
--

INSERT INTO `subject_teacher` (`id`, `teacher_id`, `subject_id`, `created_at`, `updated_at`) VALUES
(1, 1, 3, '2026-01-14 09:00:56', '2026-01-14 09:00:56'),
(2, 2, 3, '2026-01-14 09:01:52', '2026-01-14 09:01:52'),
(3, 3, 3, '2026-01-15 07:42:19', '2026-01-15 07:42:19'),
(4, 4, 3, '2026-01-15 07:42:48', '2026-01-15 07:42:48'),
(5, 5, 3, '2026-01-15 07:43:22', '2026-01-15 07:43:22'),
(6, 6, 3, '2026-01-15 07:43:57', '2026-01-15 07:43:57');

-- --------------------------------------------------------

--
-- Struttura della tabella `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `residence_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gross_hourly_rate` decimal(10,2) DEFAULT NULL,
  `pec` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_percentage` decimal(5,2) DEFAULT NULL,
  `cv_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_document_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `birth_date`, `birth_place`, `birth_country`, `vat_number`, `tax_code`, `address`, `postal_code`, `city`, `province`, `residence_country`, `contract_type`, `gross_hourly_rate`, `pec`, `iban`, `billing_mode`, `vat_percentage`, `cv_path`, `id_document_path`, `created_at`, `updated_at`) VALUES
(1, 9, 'Barbara', 'Verza', 'barbaraverza@aeacenter.it', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NONE', NULL, NULL, NULL, '2026-01-14 09:00:56', '2026-01-14 09:00:56'),
(2, 10, 'Elvira', 'Seyidova', 'elvira@aeacenter.it', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NONE', NULL, NULL, NULL, '2026-01-14 09:01:52', '2026-01-14 09:01:52'),
(3, 11, 'Fabiola', 'Albornoz', 'fabiola@aeacenter.it', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NONE', NULL, NULL, NULL, '2026-01-15 07:42:19', '2026-01-15 07:42:19'),
(4, NULL, 'Chiara', 'Forcina', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NONE', NULL, NULL, NULL, '2026-01-15 07:42:48', '2026-01-15 07:42:48'),
(5, 12, 'Natalie', 'Nosek', 'natalie@aeacenter.it', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NONE', NULL, NULL, NULL, '2026-01-15 07:43:22', '2026-01-15 07:43:23'),
(6, 13, 'Chiara', 'Palazzi', 'chiarapalazzi@aeacenter.it', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NONE', NULL, NULL, NULL, '2026-01-15 07:43:57', '2026-01-15 07:43:57');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `must_change_password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '2026-01-13 15:36:45', '$2y$12$iC2QFsTVaBqLXE4scLUSmOyX6w2NQbLnoeWwWj3e3SvB1hliY2mDS', 0, '9qLEi8QyHF', '2026-01-13 15:36:46', '2026-01-13 15:36:46'),
(2, 'Superadmin', 'knm@aealingue.it', NULL, '$2y$12$h8qbdN2Jok7QZkty6Y/jiOUOX3JPsz0H6tW3Fo9ONcTKDrRQ83t9i', 0, '52dqOA4InkzQjvsuiRw2MyrS0RJg39fTkPXleMi4OL1wFscSe7AsD0PGuOGN', '2026-01-13 16:06:02', '2026-01-13 16:10:07'),
(3, 'Admin', 'admin@aealingue.it', NULL, '$2y$12$jEUZj8563q1dFaO9Cz2BhO5oglV4r/iy6z9Obn/reBH61YsbVyOFK', 0, 'Xu9TiG4UCpth8JUahyyAR5lABosYyjye89HC3XXnU1Xq4T1rC8g6PnaWJdNd', '2026-01-13 16:10:07', '2026-01-13 16:10:07'),
(4, 'Docente', 'docente@aealingue.it', NULL, '$2y$12$F4Xz.VtShiMxNK/I1xfn5OQnf1s0GCUu..iPl6V.Ltlgp1Er6n/Ci', 0, 'AVTZBEmIMBNtIMV9hBiQ1YmdesfPgk93TZDHaug2gr8VE9739Ox1vEtLmCgp', '2026-01-13 16:10:07', '2026-01-13 16:10:07'),
(5, 'Studente', 'studente@aealingue.it', NULL, '$2y$12$i.nEcC7zidcuYt8mdjh2Ou/wz.8DCkD1ivZTh4qUQkObYXbiKM/uC', 0, 'xo9XjKg0iv746NnwFIj8dfkQWGY1L8k1D0llsxN5M8SjTCNf9nlpNdX9kSh3', '2026-01-13 16:10:08', '2026-01-13 16:10:08'),
(6, 'Segreteria', 'segreteria@aealingue.it', NULL, '$2y$12$go8nIZNcpMvkqkgS6NzUEuUBguPR.MArGiJX/TBxPA10scloEDxxy', 0, NULL, '2026-01-13 16:10:08', '2026-01-13 16:10:08'),
(7, 'VILMA ACCETTA', 'vilmaaccetta@virgilio.it', NULL, '$2y$12$tO9UBunDN1l1vlEyxFYfU.1qMSWCmSUBCrXt2x2kbwM/BuEHpBa.u', 0, NULL, '2026-01-14 08:48:31', '2026-01-14 08:48:31'),
(8, 'Laura Gullì', 'sitireggiocal@gmail.com', NULL, '$2y$12$.TPJ/PWsAJESsIf7HInHtuhMhvhfiMRQhSEA4T9XhnzVfIcAbspre', 0, 'rE3oF8Ur5VfyMPn6SlYN0wJIlxNPqh1jTYkxrZ34nk9yobvMkINsOsN80ziG', '2026-01-14 08:48:59', '2026-01-14 08:48:59'),
(9, 'Barbara Verza', 'barbaraverza@aeacenter.it', NULL, '$2y$12$MPj9dXp0IPqBkl6/ZpqORuzEpag47DjgjecWxS4/Lo/lEW2oS718m', 0, 't09Vs7n1YZ5PdXCV80TLlh4dO2NFPYE3cNIWwT9hTyIdrzs5ThRnTnSgcog2', '2026-01-14 09:00:56', '2026-01-14 13:07:52'),
(10, 'Elvira Seyidova', 'elvira@aeacenter.it', NULL, '$2y$12$bw6zYRb3WHAXB1Sm38FiP.qFg1wg/CPhE7UvDmUWwKsXwN0VnxOtm', 0, NULL, '2026-01-14 09:01:52', '2026-01-14 09:01:52'),
(11, 'Fabiola Albornoz', 'fabiola@aeacenter.it', NULL, '$2y$12$bd3EBwItMTI1nX4ZlyXSCuJLFBVrSeOO439GAHV.ZWhYv4jOG7FWG', 0, NULL, '2026-01-15 07:42:19', '2026-01-15 07:42:19'),
(12, 'Natalie Nosek', 'natalie@aeacenter.it', NULL, '$2y$12$IyUYoEfiU36phYQZnlUrS.qXPHYPEegUOwTuyf3oqepSpFXMqiI3i', 0, NULL, '2026-01-15 07:43:23', '2026-01-15 07:43:23'),
(13, 'Chiara Palazzi', 'chiarapalazzi@aeacenter.it', NULL, '$2y$12$fpYvVmbK6mef9EBQRWvAK.0ueRPQIyvvRh93YzYTiA0HlztBzyCja', 0, NULL, '2026-01-15 07:43:57', '2026-01-15 07:43:57');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indici per le tabelle `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indici per le tabelle `closure_days`
--
ALTER TABLE `closure_days`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `closure_days_date_unique` (`date`);

--
-- Indici per le tabelle `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_subject_id_foreign` (`subject_id`);

--
-- Indici per le tabelle `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollments_student_id_course_id_unique` (`student_id`,`course_id`),
  ADD KEY `enrollments_course_id_foreign` (`course_id`),
  ADD KEY `enrollments_default_teacher_id_foreign` (`default_teacher_id`);

--
-- Indici per le tabelle `enrollment_hour_movements`
--
ALTER TABLE `enrollment_hour_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_hour_movements_enrollment_id_type_index` (`enrollment_id`,`type`),
  ADD KEY `enrollment_hour_movements_lesson_id_index` (`lesson_id`),
  ADD KEY `enrollment_hour_movements_occurred_at_index` (`occurred_at`);

--
-- Indici per le tabelle `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indici per le tabelle `installments`
--
ALTER TABLE `installments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `installments_enrollment_id_number_unique` (`enrollment_id`,`number`);

--
-- Indici per le tabelle `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indici per le tabelle `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lessons_enrollment_id_lesson_number_unique` (`enrollment_id`,`lesson_number`),
  ADD KEY `lessons_student_id_foreign` (`student_id`),
  ADD KEY `lessons_course_id_foreign` (`course_id`),
  ADD KEY `lessons_teacher_id_starts_at_index` (`teacher_id`,`starts_at`);

--
-- Indici per le tabelle `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indici per le tabelle `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indici per le tabelle `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indici per le tabelle `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `payments_installment_id_foreign` (`installment_id`);

--
-- Indici per le tabelle `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indici per le tabelle `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indici per le tabelle `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indici per le tabelle `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indici per le tabelle `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_user_id_unique` (`user_id`);

--
-- Indici per le tabelle `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_name_unique` (`name`);

--
-- Indici per le tabelle `subject_teacher`
--
ALTER TABLE `subject_teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_teacher_teacher_id_subject_id_unique` (`teacher_id`,`subject_id`),
  ADD KEY `subject_teacher_subject_id_foreign` (`subject_id`);

--
-- Indici per le tabelle `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teachers_user_id_foreign` (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `closure_days`
--
ALTER TABLE `closure_days`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `enrollment_hour_movements`
--
ALTER TABLE `enrollment_hour_movements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `installments`
--
ALTER TABLE `installments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT per la tabella `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `subject_teacher`
--
ALTER TABLE `subject_teacher`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_default_teacher_id_foreign` FOREIGN KEY (`default_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `enrollment_hour_movements`
--
ALTER TABLE `enrollment_hour_movements`
  ADD CONSTRAINT `enrollment_hour_movements_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_hour_movements_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `installments`
--
ALTER TABLE `installments`
  ADD CONSTRAINT `installments_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_installment_id_foreign` FOREIGN KEY (`installment_id`) REFERENCES `installments` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `subject_teacher`
--
ALTER TABLE `subject_teacher`
  ADD CONSTRAINT `subject_teacher_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_teacher_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
