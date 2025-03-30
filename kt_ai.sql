-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 30, 2025 lúc 05:25 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `kt_ai`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_role_permissions`
--

CREATE TABLE `admin_role_permissions` (
  `role` enum('super_admin','admin','moderator') NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator') NOT NULL,
  `last_login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_admin` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ai_features`
--

CREATE TABLE `ai_features` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `prompt_template` text DEFAULT NULL,
  `creadit_cost` int(11) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `input_requirements` int(11) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `sum_img` int(11) DEFAULT NULL,
  `average_processing_time` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_feature` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ai_features`
--

INSERT INTO `ai_features` (`id`, `title`, `slug`, `description`, `prompt_template`, `creadit_cost`, `thumbnail_url`, `input_requirements`, `category`, `sum_img`, `average_processing_time`, `created_at`, `updated_at`, `status_feature`) VALUES
(1, 'Tạo ảnh bằng mô tả', 'tao-anh-bang-mo-ta', 'Tự do sáng tạo các hình ảnh bằng trí tưởng tượng của bạn thông qua đoạn mô tả', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_1.png', NULL, 'Text to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(2, 'Tạo ảnh theo phong cách ảnh khác', 'tao-anh-bang-mo-ta', 'Tạo ra các bức ảnh lấy phong cách từ các bức ảnh bạn cung cấp', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_2.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(3, 'Kết hợp phong cách 2 hình ảnh', 'ket-hop-phong-cach-2-hinh-anh', 'Tạo ra các bức ảnh mà kết hợp phong cách từ 2 hình ảnh khác nhau do bạn cung cấp', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_3.png', 2, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(4, 'Chuyển đổi nét vẽ thành hình ảnh', 'chuyen-doi-net-ve-thanh-hinh-anh', 'Chuyển đổi các nét vẽ thủ công thành hình ảnh màu sắc', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_4.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(5, 'Chuyển đổi phong cách sang Anime', 'chuyen-doi-phong-cach-sang-anime', 'Chuyển đổi bất kì hình ảnh nào của bạn sang phong cách Anime', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_5.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(6, 'Tạo hình ảnh dễ thương', 'tao-hinh-anh-de-thuong', 'Tạo hình ảnh dễ thương 3D từ dòng mô tả của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_6.png', NULL, 'Text to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(7, 'Tạo ảnh siêu thực siêu nhanh', 'tao-hinh-anh-de-thuong', 'Tạo ảnh siêu thực và siêu nhanh trong 10s', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_7.png', NULL, 'Text to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(8, 'Tạo hình ảnh theo Pose', 'tao-hinh-anh-theo-pose', 'Bạn muốn tạo ảnh theo Pose của riêng bạn? Thử ngay.', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_8.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(9, 'Tạo hình ảnh Ghibli từ khuôn mặt của bạn', 'tao-hinh-anh-ghibli-tu-khuon-mat', 'Sáng tạo các hình ảnh Ghibli hoặc hình ảnh dịu dàng từ khuôn mặt của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_9.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(10, 'Tạo hình ảnh sticker từ khuôn mặt của bạn', 'tao-hinh-sticker-tu-khuon-mat', 'Tạo ra các hình ảnh sticker dễ thương từ khuôn mặt của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_10.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(11, 'Làm nét hình ảnh', 'lam-net-anh', 'Tăng độ phân giải, làm nét hình ảnh của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_11.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(12, 'Đổi màu sắc quần áo', 'doi-mau-sac-quan-ao', 'Thay đổi màu sắc hình ảnh quần áo trong bất kì hình ảnh nào của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_12.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(13, 'Pha trộn hình ảnh nâng cao', 'pha-tron-hinh-anh', 'Tải hình ảnh của bạn để pha trộn phong cách với nhau với phiên bản nâng cao', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_13.png', 2, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(14, 'Tạo sticker dễ thương', 'pha-tron-hinh-anh', 'Tạo các sticker dễ thương từ mô tả của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_14.png', NULL, 'Text to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(15, 'Mở rộng hình ảnh', 'mo-rong-anh', 'Tự động nội suy để tăng mở rộng hình ảnh của bạn', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_15.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(16, 'Tạo avatar 3D', 'mo-rong-anh', 'Tạo ra các hình ảnh 3D từ khuôn mặt của bạn để làm ảnh đại diện', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_16.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(17, 'Thử quần áo phiên bản standard', 'thu-quan-ao-phien-ban-standard', 'Tạo ra người mẫu ảo để thử các quần áo của bạn (Lưu ý ở phiên bản standard thì không thể lấy các họa tiết trên áo)', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_17.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(18, 'Tạo ảnh nội thất', 'tao-anh-noi-that', 'Tự do sáng tạo hình ảnh nội thất, phòng ngủ, phòng khách và nhiều kiến trúc khác', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_18.png', NULL, 'Text to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(19, 'Tạo nền background sản phẩm', 'tao-nen-background-san-pham', 'Bạn muốn thêm hình nền cho sản phẩm của bạn thêm phần chuyền nghiệp? Tải lên và thử ngay', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_19.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(20, 'Thử quần áo phiên bản Premium', 'thu-quan-ao-phien-ban-premium', 'Sáng tạo hơn, chuyên nghiệp hơn phiên bản Standard. Có thể lấy họa tiết hình ảnh', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_20.png', 1, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active'),
(21, 'Tạo người mẫu thử quần áo theo khuôn mặt của bạn', 'tao-nguoi-mau-thu-ao', 'Tạo ra một người mẫu thử quần ảo với khuôn mặt do bạn chọn.', '1girl, cute, smile...', 0, 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_21.png', 2, 'Image to Image', 0, 300, '2025-02-16 04:37:54', '2025-02-26 04:33:17', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `image_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sum_like` int(11) DEFAULT NULL,
  `list_like` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`list_like`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `image_id`, `parent_id`, `content`, `created_at`, `updated_at`, `sum_like`, `list_like`) VALUES
(1, 2, 1, NULL, 'hihi 😂', '2025-03-24 07:53:50', '2025-03-24 08:02:09', 0, '[]'),
(2, 2, 1, NULL, 'aaa', '2025-03-25 08:47:21', '2025-03-25 08:56:46', 1, '[2]');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `images`
--

CREATE TABLE `images` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `features_id` int(10) UNSIGNED NOT NULL,
  `prompt` text NOT NULL,
  `image_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_url`)),
  `sum_like` int(11) NOT NULL,
  `sum_comment` int(11) NOT NULL,
  `privacy_status` enum('public','private') NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_image` enum('processing','completed','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `images`
--

INSERT INTO `images` (`id`, `user_id`, `features_id`, `prompt`, `image_url`, `sum_like`, `sum_comment`, `privacy_status`, `metadata`, `created_at`, `updated_at`, `status_image`) VALUES
(1, 2, 1, '1girl, 2girl, 3girl', '[\"https:\\/\\/cdn-media.sforum.vn\\/storage\\/app\\/media\\/anh-dep-102.jpg\",\"https:\\/\\/cdn-media.sforum.vn\\/storage\\/app\\/media\\/anh-dep-103.jpg\",\"https:\\/\\/cdn-media.sforum.vn\\/storage\\/app\\/media\\/anh-dep-104.jpg\"]', 2, 2, 'public', '\"{\\\"tags\\\":[\\\"girl\\\",\\\"beauty\\\",\\\"portrait\\\"]}\"', '2025-03-15 22:23:49', '2025-03-25 08:50:22', 'completed'),
(2, 2, 1, 'Thử nghiệm lần 2', '[\"https:\\/\\/cdn-media.sforum.vn\\/storage\\/app\\/media\\/anh-dep-104.jpg\"]', 0, 0, 'public', '\"{\\\"tags\\\":[\\\"girl\\\",\\\"beauty\\\",\\\"portrait\\\"]}\"', '2025-03-15 22:23:49', '2025-03-15 22:23:49', 'completed');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `interactions`
--

CREATE TABLE `interactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `image_id` int(10) UNSIGNED NOT NULL,
  `type_interaction` enum('like','save','report') NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_interaction` enum('active','hidden','resolved') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `interactions`
--

INSERT INTO `interactions` (`id`, `user_id`, `image_id`, `type_interaction`, `content`, `created_at`, `updated_at`, `status_interaction`) VALUES
(1, 1, 1, 'like', NULL, '2025-03-22 20:23:04', '2025-03-22 20:23:04', 'active'),
(3, 5, 1, 'like', NULL, '2025-03-22 20:23:04', '2025-03-22 20:23:04', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_02_09_093438_create_users_table', 1),
(2, '2025_02_09_093439_create_ai_features_table', 1),
(3, '2025_02_09_093440_create_images_table', 1),
(4, '2025_02_09_093441_create_notifications_table', 1),
(5, '2025_02_09_093442_create_admin_users_table', 1),
(6, '2025_02_09_093443_create_admin_permissions_table', 1),
(7, '2025_02_09_093444_create_admin_role_permissions_table', 1),
(8, '2025_02_09_093445_create_reports_table', 1),
(9, '2025_02_09_093446_create_comments_table', 1),
(10, '2025_02_09_093447_create_interactions_table', 1),
(11, '2025_02_09_093448_create_user_sessions_table', 1),
(12, '2025_02_16_042609_update_ai_features_table', 1),
(13, '2025_02_18_041007_add_remember_token_to_users_table', 2),
(14, '2025_02_19_052037_create_personal_access_tokens_table', 3),
(15, '2025_02_19_053732_create_cache_table', 4),
(16, '2025_02_20_040356_email_verification_code', 5),
(17, '2025_02_21_020147_create_activities_users', 6),
(18, '2025_02_22_062255_create_personal_access_tokens_table', 7),
(19, '2025_02_26_024317_update_image_url_in_images', 8),
(20, '2025_02_26_043125_update_input_requirement_ai_features', 9),
(21, '2025_02_26_053219_update_privacy_status_images', 10),
(24, '2025_03_23_032058_update_interaction', 11),
(25, '2025_03_23_035630_update_comment', 12),
(26, '2025_03_24_145213_update_parent_i_d_in_comments', 13),
(27, '2025_03_25_100836_update_sum_like_sum_img_remaining_creadits_is_verified_status_user_user', 14);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '884918b45bd0934b5cb366ea147a5063bae2a446ec04a823e6c8befa6dbb1e2d', '[\"*\"]', NULL, NULL, '2025-02-22 00:41:52', '2025-02-22 00:41:52'),
(46, 'App\\Models\\User', 2, 'auth_token', 'a13e9d0add89e023fe5911c56a95f4b97a1cf6e8399137cfd12ee17ab86532df', '[\"*\"]', NULL, NULL, '2025-03-25 03:11:59', '2025-03-25 03:11:59'),
(47, 'App\\Models\\User', 2, 'auth_token', 'dc945b9c27dbb0281e3e037a589caf2abe807f9ea078b704139d8c1101a7419e', '[\"*\"]', NULL, NULL, '2025-03-25 08:28:43', '2025-03-25 08:28:43'),
(48, 'App\\Models\\User', 2, 'auth_token', '13c6189b959e468e57c7855cf093ad4c75d78a552967f68aebd7f26bb343c5a8', '[\"*\"]', NULL, NULL, '2025-03-26 03:07:09', '2025-03-26 03:07:09'),
(49, 'App\\Models\\User', 2, 'auth_token', '5f3a5e3c2c63ace98818619232f033f33020475c6299b02497d6b408efa37beb', '[\"*\"]', NULL, NULL, '2025-03-29 19:05:44', '2025-03-29 19:05:44'),
(50, 'App\\Models\\User', 2, 'auth_token', '636f1b0ad3c2307d89c293416dc664b722638c8ce93e7b922f9d9b9597d8949c', '[\"*\"]', NULL, NULL, '2025-03-29 19:47:47', '2025-03-29 19:47:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reports`
--

CREATE TABLE `reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `image_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_report` enum('waiting','ignored','accept') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `activities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activities`)),
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_url` varchar(255) NOT NULL,
  `cover_image_url` varchar(255) NOT NULL,
  `sum_like` int(11) NOT NULL DEFAULT 0,
  `sum_img` int(11) NOT NULL DEFAULT 0,
  `remaining_creadits` int(11) NOT NULL DEFAULT 20,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_user` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `email_verification_code` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `activities`, `email`, `password`, `avatar_url`, `cover_image_url`, `sum_like`, `sum_img`, `remaining_creadits`, `created_at`, `updated_at`, `status_user`, `remember_token`, `email_verification_code`, `is_verified`) VALUES
(1, 'John Doe', NULL, 'john.doe@example.com', '$2y$12$5iQh1KbeP06bS0xTtKUVc.GgJmfC2dhCEdV4UtOVFKNyxTui4IcKy', 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png', 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png', 3, 0, 0, '2025-02-18 04:14:42', '2025-03-22 20:59:29', 'active', NULL, NULL, 1),
(2, '24.Trần Hoàng Anh Khôi', '[{\"action\":\"test th\\u1eed\",\"timestamp\":\"2025-02-21 02:10:02\"},{\"action\":\"th\\u00eam m\\u1edbi\",\"timestamp\":\"2025-02-21 02:05:02\"},{\"action\":\"s\\u1eeda\",\"timestamp\":\"2025-02-21 02:00:02\"},{\"action\":\"x\\u00f3a\",\"timestamp\":\"2025-02-21 01:55:02\"}]', 'hoangkhoi230@gmail.com', '$2y$12$TYmuXJHuM0z7KAdNEoPe0uEjSTTQjnfUDAgzr73tTR.j5LeM7Fpsy', 'https://lh3.googleusercontent.com/a/ACg8ocLev1qQPI8GSu3HuQYV5frfYBAmMQX_Fej2vyRveWGMPofrZdar=s96-c', 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png', 2, 0, 20, '2025-02-18 22:13:30', '2025-03-29 19:47:47', 'active', NULL, NULL, 0),
(5, 'Trần Hoàng Khôi', NULL, 'liz659923@gmail.com', '$2y$12$5RCLqAnO2Ng.6qEaRGlOvOUYVV2UISh6YYymUhikGbLVY88LqPzTy', 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png', 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png', 0, 0, 20, '2025-02-19 22:37:32', '2025-02-19 22:39:26', 'active', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` text NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_valid` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `admin_role_permissions`
--
ALTER TABLE `admin_role_permissions`
  ADD KEY `admin_role_permissions_permission_id_foreign` (`permission_id`);

--
-- Chỉ mục cho bảng `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `ai_features`
--
ALTER TABLE `ai_features`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_user_id_foreign` (`user_id`),
  ADD KEY `comments_image_id_foreign` (`image_id`);

--
-- Chỉ mục cho bảng `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `images_user_id_foreign` (`user_id`),
  ADD KEY `images_features_id_foreign` (`features_id`);

--
-- Chỉ mục cho bảng `interactions`
--
ALTER TABLE `interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `interactions_user_id_foreign` (`user_id`),
  ADD KEY `interactions_image_id_foreign` (`image_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Chỉ mục cho bảng `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reports_admin_id_foreign` (`admin_id`),
  ADD KEY `reports_image_id_foreign` (`image_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_sessions_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ai_features`
--
ALTER TABLE `ai_features`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `images`
--
ALTER TABLE `images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `interactions`
--
ALTER TABLE `interactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `admin_role_permissions`
--
ALTER TABLE `admin_role_permissions`
  ADD CONSTRAINT `admin_role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_features_id_foreign` FOREIGN KEY (`features_id`) REFERENCES `ai_features` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `images_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `interactions`
--
ALTER TABLE `interactions`
  ADD CONSTRAINT `interactions_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
