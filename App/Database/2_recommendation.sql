CREATE TABLE `recommendation`
(
    `id`         int                             NOT NULL,
    `text`       text COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp                       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `recommendation`
    ADD PRIMARY KEY (`id`),
    ADD PRIMARY KEY (`id`),
    MODIFY `id` int NOT NULL AUTO_INCREMENT;