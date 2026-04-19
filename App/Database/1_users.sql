CREATE TABLE `users`
(
    `id`           varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name`         varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `username`     varchar(99) COLLATE utf8mb4_unicode_ci  DEFAULT NULL,
    `display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `type`         varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `is_active`    tinyint(1) NOT NULL DEFAULT '1',
    `created_at`   timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);