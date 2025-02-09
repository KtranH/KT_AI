CREATE TABLE `users`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `avatar_url` VARCHAR(255) NOT NULL,
    `cover_image_url` VARCHAR(255) NOT NULL,
    `sum_like` INT NOT NULL,
    `sum_img` INT NOT NULL,
    `remaining_creadits` INT NOT NULL DEFAULT '20',
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL,
    `status_user` ENUM('active', 'banned') NOT NULL DEFAULT 'active'
);
CREATE TABLE `ai_features`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `prompt_template` VARCHAR(255) NOT NULL,
    `creadit_cost` INT NOT NULL DEFAULT '0',
    `count_img` INT NOT NULL DEFAULT '0',
    `thumbnail_url` VARCHAR(255) NOT NULL,
    `input_requirements` JSON NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `sum_img` INT NOT NULL,
    `average_processing_time` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL,
    `status_feature` ENUM('active', 'inactive', 'beta') NOT NULL DEFAULT 'active'
);
CREATE TABLE `images`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `features_id` INT NOT NULL,
    `prompt` TEXT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `sum_like` INT NOT NULL,
    `sum_comment` INT NOT NULL,
    `privacy_status` ENUM('public', 'private') NOT NULL DEFAULT 'public',
    `metadata` JSON NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL,
    `status_image` ENUM('processing', 'completed', 'failed') NOT NULL DEFAULT 'processing'
);
CREATE TABLE `notifications`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `data` JSON NOT NULL,
    `read_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NOT NULL
);
CREATE TABLE `admin_users`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'admin', 'moderator') NOT NULL,
    `last_login_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL,
    `status_admin` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
);
CREATE TABLE `admin_permissions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL
);
CREATE TABLE `admin_role_permissions`(
    `role` ENUM('super_admin', 'admin', 'moderator') NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    PRIMARY KEY(`role`)
);
CREATE TABLE `reports`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `image_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL,
    `status_report` ENUM('waiting', 'ignored', 'accept') NOT NULL DEFAULT 'waiting'
);
CREATE TABLE `comments`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `image_id` INT NOT NULL,
    `parent_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL
);
CREATE TABLE `interactions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `image_id` BIGINT NOT NULL,
    `type_interaction` ENUM('like', 'save', 'report') NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL,
    `status_interaction` ENUM('active', 'hidden', 'resolved') NOT NULL DEFAULT 'active'
);
CREATE TABLE `user_sessions`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(255) NOT NULL,
    `user_agent` TEXT NOT NULL,
    `last_activity` TIMESTAMP NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `is_valid` BOOLEAN NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NOT NULL
);
ALTER TABLE
    `interactions` ADD CONSTRAINT `interactions_image_id_foreign` FOREIGN KEY(`image_id`) REFERENCES `images`(`id`);
ALTER TABLE
    `admin_role_permissions` ADD CONSTRAINT `admin_role_permissions_permission_id_foreign` FOREIGN KEY(`permission_id`) REFERENCES `admin_permissions`(`id`);
ALTER TABLE
    `comments` ADD CONSTRAINT `comments_image_id_foreign` FOREIGN KEY(`image_id`) REFERENCES `images`(`id`);
ALTER TABLE
    `images` ADD CONSTRAINT `images_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `comments` ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `reports` ADD CONSTRAINT `reports_admin_id_foreign` FOREIGN KEY(`admin_id`) REFERENCES `admin_users`(`id`);
ALTER TABLE
    `reports` ADD CONSTRAINT `reports_image_id_foreign` FOREIGN KEY(`image_id`) REFERENCES `images`(`id`);
ALTER TABLE
    `images` ADD CONSTRAINT `images_features_id_foreign` FOREIGN KEY(`features_id`) REFERENCES `ai_features`(`id`);
ALTER TABLE
    `user_sessions` ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `notifications` ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `admin_users` ADD CONSTRAINT `admin_users_role_foreign` FOREIGN KEY(`role`) REFERENCES `admin_role_permissions`(`role`);
ALTER TABLE
    `interactions` ADD CONSTRAINT `interactions_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);