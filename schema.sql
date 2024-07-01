CREATE TABLE `user` (
  `id` INT PRIMARY KEY,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` ENUM ('ROLE_ADMIN', 'ROLE_USER') NOT NULL,
  `picture_id` INT DEFAULT null,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime DEFAULT null
);

CREATE TABLE `picture` (
  `id` INT PRIMARY KEY,
  `file_name` varchar(255) NOT NULL,
  `path_name` varchar(255) NOT NULL,
  `mimeType` varchar(255),
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime DEFAULT null
);

CREATE TABLE `post` (
  `id` INT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `lede` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `user_id` INT NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime DEFAULT null
);

CREATE TABLE `commentary` (
  `id` INT PRIMARY KEY,
  `content` text NOT NULL,
  `post_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `updated_at` datetime DEFAULT null
);

ALTER TABLE `user` ADD FOREIGN KEY (`picture_id`) REFERENCES `picture` (`id`);
ALTER TABLE `commentary` ADD FOREIGN KEY (`post_id`) REFERENCES `post` (`id`);
ALTER TABLE `commentary` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
ALTER TABLE `post` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
