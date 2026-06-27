
-- --------------------------------------------------------

--
-- Table structure for table `bot_sessions`
--

CREATE TABLE `bot_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `bale_id` bigint NOT NULL,
  `step` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'start',
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bot_sessions`
--

INSERT INTO `bot_sessions` (`id`, `bale_id`, `step`, `data`, `created_at`, `updated_at`) VALUES
(1, 1894591257, 'viewing_requests', '{\"request_ids\": [8, 7, 6, 5, 4, 2, 1], \"request_page\": 0, \"last_message_id\": 1822}', '2026-06-08 06:54:41', '2026-06-21 09:45:25'),
(2, 1699828075, 'main_menu', '[]', '2026-06-08 09:00:33', '2026-06-08 09:02:40'),
(3, 0, 'main_menu', '[]', '2026-06-08 09:15:22', '2026-06-08 09:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'آموزش', 'education', 1, '2026-06-08 08:31:50', '2026-06-08 08:31:50'),
(2, 'مالی', 'finance', 1, '2026-06-08 08:31:50', '2026-06-08 08:31:50'),
(3, 'خوابگاه', 'dormitory', 1, '2026-06-08 08:31:50', '2026-06-08 08:31:50'),
(4, 'کتابخانه', 'library', 1, '2026-06-08 08:31:50', '2026-06-08 08:31:50'),
(5, 'فنی', 'technical', 1, '2026-06-08 08:31:50', '2026-06-08 08:31:50'),
(6, 'سایر', 'other', 1, '2026-06-08 08:31:50', '2026-06-08 08:31:50');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_25_093102_create_departments_table', 1),
(5, '2026_05_25_093246_add_fields_to_users_table', 1),
(6, '2026_05_25_093608_create_requests_table', 1),
(7, '2026_06_01_075936_create_request_files_table', 1),
(8, '2026_06_01_075944_create_request_replies_table', 1),
(9, '2026_06_01_080046_create_bot_sessions_table', 1),
(10, '2026_06_15_000000_add_referred_at_to_requests_table', 7);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` bigint UNSIGNED NOT NULL,
  `tracking_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `requester_id` bigint UNSIGNED NOT NULL,
  `applicant_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applicant_student_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `assigned_expert_id` bigint UNSIGNED DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','seen','answered','referred') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_for_other` tinyint(1) NOT NULL DEFAULT '0',
  `seen_at` timestamp NULL DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `referred_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `tracking_code`, `requester_id`, `applicant_phone`, `applicant_student_number`, `department_id`, `assigned_expert_id`, `body`, `status`, `is_for_other`, `seen_at`, `answered_at`, `referred_at`, `created_at`, `updated_at`) VALUES
(1, '0996A36E', 1, '0156644838', '4665', 4, NULL, 'd65f4s6df46s', 'pending', 0, '2026-06-13 09:27:23', NULL, NULL, '2026-06-08 08:32:51', '2026-06-13 09:27:23'),
(2, '5847F7CE', 1, '09151251252', '855552', 3, 7, 'لطفا خوابگاه رو باز کنید', 'referred', 1, '2026-06-20 08:43:51', NULL, '2026-06-23 09:40:52', '2026-06-08 08:39:39', '2026-06-23 09:40:52'),
(3, 'B73A1966', 2, '0155334496', '95926', 4, NULL, 'سلام...تست', 'pending', 0, NULL, NULL, NULL, '2026-06-08 09:02:40', '2026-06-08 09:02:40'),
(4, '04625847', 1, '0156644838', '4646', 4, NULL, '465465', 'pending', 0, NULL, NULL, NULL, '2026-06-09 08:54:21', '2026-06-09 08:54:21'),
(5, '25833255', 1, '0156644838', '5465', 4, NULL, '46546', 'pending', 0, NULL, NULL, NULL, '2026-06-10 06:50:37', '2026-06-10 06:50:37'),
(6, '2E9524D0', 1, '0156644838', NULL, 2, 9, '4654', 'referred', 0, '2026-06-17 09:00:04', NULL, '2026-06-23 08:59:57', '2026-06-10 06:51:50', '2026-06-23 08:59:57'),
(7, '78265A4E', 1, '09156644838', '4646', 3, 6, '65464', 'answered', 1, '2026-06-13 08:22:36', '2026-06-20 09:17:07', '2026-06-20 09:09:08', '2026-06-10 07:19:25', '2026-06-20 09:17:07'),
(8, '5E229498', 1, '0156644838', '4654654', 1, NULL, '45646', 'pending', 0, NULL, NULL, NULL, '2026-06-21 09:45:22', '2026-06-21 09:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `request_files`
--

CREATE TABLE `request_files` (
  `id` bigint UNSIGNED NOT NULL,
  `request_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_files`
--

INSERT INTO `request_files` (`id`, `request_id`, `file_path`, `file_name`, `file_type`, `file_size`, `created_at`, `updated_at`) VALUES
(1, 2, '1894591257:1894710409203425027:0:d254cf5f65a233bf79a0636bef78883374d2952fe991bccf4211f134a0136bcda38d42630b4dd87548d1e46b939b3a07', '1894591257:1894710409203425027:0:d254cf5f65a233bf79a0636bef78883374d2952fe991bccf4211f134a0136bcda38d42630b4dd87548d1e46b939b3a07', 'bale_file', 0, '2026-06-08 08:39:39', '2026-06-08 08:39:39'),
(2, 4, '1894591257:2014588107234418435:0:a44622550f33e84e48d1e46b939b3a07', '1894591257:2014588107234418435:0:a44622550f33e84e48d1e46b939b3a07', 'bale_file', 0, '2026-06-09 08:54:21', '2026-06-09 08:54:21'),
(3, 8, '1894591257:1876445823843901185:0:deceef14d6107589c77f9cb588c8042c6dabe1168d3079ddc2f35d99f2500e061a9ec6f7595b78a8', '1894591257:1876445823843901185:0:deceef14d6107589c77f9cb588c8042c6dabe1168d3079ddc2f35d99f2500e061a9ec6f7595b78a8', 'bale_file', 0, '2026-06-21 09:45:22', '2026-06-21 09:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `request_replies`
--

CREATE TABLE `request_replies` (
  `id` bigint UNSIGNED NOT NULL,
  `request_id` bigint UNSIGNED NOT NULL,
  `expert_id` bigint UNSIGNED NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_referral` tinyint(1) NOT NULL DEFAULT '0',
  `referred_to` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_replies`
--

INSERT INTO `request_replies` (`id`, `request_id`, `expert_id`, `body`, `file_path`, `file_name`, `is_referral`, `referred_to`, `created_at`, `updated_at`) VALUES
(1, 7, 3, 'سلام، تست شد', 'replies/h95QMKPLGnJhX52iwRNEsTci3ASHIjiQnztvWARu.jpg', '11111.jpg', 0, NULL, '2026-06-14 07:29:13', '2026-06-14 07:29:13'),
(2, 6, 3, 'لطفا بررسی بفرمائید', NULL, NULL, 1, 4, '2026-06-17 05:51:12', '2026-06-17 05:51:12'),
(3, 7, 3, 'تست ارجاع مجدد', NULL, NULL, 1, 4, '2026-06-20 08:45:14', '2026-06-20 08:45:14'),
(4, 2, 3, 'تست جدید', NULL, NULL, 1, 4, '2026-06-20 08:45:50', '2026-06-20 08:45:50'),
(5, 6, 4, 'تست اجراع یییی', NULL, NULL, 1, 3, '2026-06-20 08:46:14', '2026-06-20 08:46:14'),
(6, 7, 3, 'سیبسیب', NULL, NULL, 1, 6, '2026-06-20 09:09:08', '2026-06-20 09:09:08'),
(7, 2, 4, 'درخواست به ارجاع‌دهنده عودت داده شد.', NULL, NULL, 1, 3, '2026-06-20 09:15:41', '2026-06-20 09:15:41'),
(8, 7, 3, 'نمیشه موراد رو بررسی کرد', 'replies/8OJAoFBzlM6rPUdCI91C1rAfoZ2ewfGFfwi4OhIs.jpg', '6dde1cea-6aff-41cc-a9e0-618db3c770ca.jpg', 0, NULL, '2026-06-20 09:17:07', '2026-06-20 09:17:07'),
(9, 6, 4, 'ارجاع داده شد.', NULL, NULL, 1, 9, '2026-06-23 08:59:57', '2026-06-23 08:59:57'),
(10, 2, 3, 'بررسی نمایید', NULL, NULL, 1, 7, '2026-06-23 09:40:52', '2026-06-23 09:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('fr3FowZvWQFIIGCeqrT7N7ztvM4s9X9VTpIgAT4e', 3, '94.101.182.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJmMlpXYmZjbmlMR2NlQ0dhZ3BrRkk1V1JXcjNCU1U2R0JwMUNUbjNEIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cHM6XC9cL3N1cHBvcnQuZ29uYWJhZC5hYy5pclwvcGFuZWxcL3JlcXVlc3RzXC82In0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc3VwcG9ydC5nb25hYmFkLmFjLmlyXC9wYW5lbFwvcmVxdWVzdHM/ZXhwZXJ0X2lkPTMmZnJvbV9kYXRlPTIwMjYtMDUtMjcmdG9fZGF0ZT0yMDI2LTA2LTI3Iiwicm91dGUiOiJwYW5lbC5yZXF1ZXN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjozfQ==', 1782535273),
('n3EwOr8kAVfoHPw8YGdiSAyt0VtRIEt9N4HIDtw3', 4, '94.101.183.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'eyJfdG9rZW4iOiJFVkdFY29Qd2FBb2daZ3pteFlEaFJxNjA0b1RQZGd2S0c3WVRKQTdyIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cHM6XC9cL3N1cHBvcnQuZ29uYWJhZC5hYy5pclwvcGFuZWxcL3JlcG9ydHMifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zdXBwb3J0LmdvbmFiYWQuYWMuaXJcL3BhbmVsXC9yZXF1ZXN0c1wvNiIsInJvdXRlIjoicGFuZWwucmVxdWVzdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo0fQ==', 1782535146);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bale_id` bigint DEFAULT NULL,
  `bale_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('student','expert','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `is_channel_member` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `phone`, `student_number`, `bale_id`, `bale_username`, `role`, `department_id`, `is_channel_member`, `is_active`) VALUES
(1, 'محسن یوسفی', '1894591257@bale.temp', NULL, '$2y$12$QFueS3dIgEKlc3QA.upNE.NN.Z22fk1JKyQvW/9omz9rtBQ8qUnaK', NULL, '2026-06-08 08:14:54', '2026-06-08 08:15:02', '0156644838', NULL, 1894591257, 'm_youssefi', 'student', NULL, 0, 1),
(2, 'Reza Ajam', '1699828075@bale.temp', NULL, '$2y$12$OQpZ7W3.7v0X20/k6vk42eeVFnuA2g5xmnfLbFkPD4WMRtzagv/T.', NULL, '2026-06-08 09:00:33', '2026-06-08 09:00:46', '0155334496', NULL, 1699828075, 'r_ajam', 'student', NULL, 0, 1),
(3, 'کارشناس تست', 'expert@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-13 06:35:00', '2026-06-13 06:35:00', NULL, NULL, NULL, NULL, 'expert', 3, 0, 1),
(4, 'کارشناس تست 2', 'expert2@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-13 06:35:00', '2026-06-13 06:35:00', NULL, NULL, NULL, NULL, 'expert', 2, 0, 1),
(5, 'کارشناس آموزش', 'education@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-17 06:49:42', '2026-06-17 06:49:42', NULL, NULL, NULL, NULL, 'expert', 1, 0, 1),
(6, 'کارشناس مالی', 'finance@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-17 06:49:42', '2026-06-17 06:49:42', NULL, NULL, NULL, NULL, 'expert', 2, 0, 1),
(7, 'کارشناس خوابگاه', 'dormitory@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-17 06:49:42', '2026-06-17 06:49:42', NULL, NULL, NULL, NULL, 'expert', 3, 0, 1),
(8, 'کارشناس کتابخانه', 'library@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-17 06:49:42', '2026-06-17 06:49:42', NULL, NULL, NULL, NULL, 'expert', 4, 0, 1),
(9, 'کارشناس فنی', 'technical@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-17 06:49:42', '2026-06-17 06:49:42', NULL, NULL, NULL, NULL, 'expert', 5, 0, 1),
(10, 'کارشناس سایر', 'other@gonabad.ac.ir', NULL, '$2y$12$nRa641AqHb4zWiEmybFpEeHG5kZww1CFcj6OkOmzho96WuJBjDgA2', NULL, '2026-06-17 06:49:42', '2026-06-17 06:49:42', NULL, NULL, NULL, NULL, 'expert', 6, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bot_sessions`
--
ALTER TABLE `bot_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bot_sessions_bale_id_unique` (`bale_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requests_tracking_code_unique` (`tracking_code`),
  ADD KEY `requests_requester_id_foreign` (`requester_id`),
  ADD KEY `requests_department_id_foreign` (`department_id`),
  ADD KEY `requests_assigned_expert_id_foreign` (`assigned_expert_id`);

--
-- Indexes for table `request_files`
--
ALTER TABLE `request_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_files_request_id_foreign` (`request_id`);

--
-- Indexes for table `request_replies`
--
ALTER TABLE `request_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_replies_request_id_foreign` (`request_id`),
  ADD KEY `request_replies_expert_id_foreign` (`expert_id`),
  ADD KEY `request_replies_referred_to_foreign` (`referred_to`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD UNIQUE KEY `users_bale_id_unique` (`bale_id`),
  ADD KEY `users_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bot_sessions`
--
ALTER TABLE `bot_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `request_files`
--
ALTER TABLE `request_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `request_replies`
--
ALTER TABLE `request_replies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_assigned_expert_id_foreign` FOREIGN KEY (`assigned_expert_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `requests_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `request_files`
--
ALTER TABLE `request_files`
  ADD CONSTRAINT `request_files_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_replies`
--
ALTER TABLE `request_replies`
  ADD CONSTRAINT `request_replies_expert_id_foreign` FOREIGN KEY (`expert_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `request_replies_referred_to_foreign` FOREIGN KEY (`referred_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `request_replies_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);
COMMIT;
