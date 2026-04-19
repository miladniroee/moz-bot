CREATE TABLE `game_state`
(
    `user_id`    varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `message_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_mark`  varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
    `bot_mark`   varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
    `game`       json                                   NOT NULL,
    `winner`     varchar(4) COLLATE utf8mb4_unicode_ci           DEFAULT NULL,
    `created_at` timestamp                              NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `game_state`
    ADD PRIMARY KEY (`user_id`,`message_id`) USING BTREE,
    ADD CONSTRAINT `user_id_id_users_game_state_foreign_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;