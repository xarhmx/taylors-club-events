-- ============================================================
-- Taylor's Club Events - database
-- ------------------------------------------------------------
-- Import in phpMyAdmin: Import tab -> choose this file -> Go.
-- It creates the database, all tables and sample data.
-- Re-importing resets everything back to this sample data.
--
-- Test accounts (password in brackets):
--   student@test.com   (Student123)   - Student
--   organiser@test.com (Organiser123) - Club Organiser of Computer Science Society
--   all other sample users use       (Password123)
--
-- Passwords are stored as bcrypt hashes (password_hash in PHP),
-- never as plain text.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `taylors_club_events`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taylors_club_events`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `remember_tokens`, `feedback`, `rsvps`, `club_followers`,
                     `club_members`, `events`, `clubs`, `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- users
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `user_id`        INT NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(100) NOT NULL,
  `email`          VARCHAR(191) NOT NULL,
  `password`       VARCHAR(255) NOT NULL COMMENT 'bcrypt hash from password_hash()',
  `role`           ENUM('student','organiser') NOT NULL DEFAULT 'student',
  `student_id`     VARCHAR(20) NOT NULL,
  `phone`          VARCHAR(20) NOT NULL DEFAULT '',
  `profile_photo`  VARCHAR(255) DEFAULT NULL COMMENT 'file name in images/profiles/',
  `failed_logins`  INT NOT NULL DEFAULT 0 COMMENT 'wrong passwords in a row',
  `locked_until`   DATETIME DEFAULT NULL COMMENT 'login blocked until this time',
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- remember_tokens  ("Remember me" cookie logins)
-- ------------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `token_id`    INT NOT NULL AUTO_INCREMENT,
  `user_id`     INT NOT NULL,
  `selector`    CHAR(24) NOT NULL COMMENT 'public part of the cookie',
  `token_hash`  CHAR(64) NOT NULL COMMENT 'SHA-256 of the secret part',
  `expires_at`  DATETIME NOT NULL,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `uq_tokens_selector` (`selector`),
  KEY `idx_tokens_user` (`user_id`),
  CONSTRAINT `fk_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- clubs
-- ------------------------------------------------------------
CREATE TABLE `clubs` (
  `club_id`       INT NOT NULL AUTO_INCREMENT,
  `club_name`     VARCHAR(150) NOT NULL,
  `category`      VARCHAR(50) NOT NULL,
  `description`   TEXT,
  `image`         VARCHAR(255) DEFAULT NULL,
  `organiser_id`  INT DEFAULT NULL COMMENT 'user who can edit this club',
  PRIMARY KEY (`club_id`),
  KEY `idx_clubs_organiser` (`organiser_id`),
  CONSTRAINT `fk_clubs_organiser` FOREIGN KEY (`organiser_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- club_members  (committee positions, e.g. Club Head)
-- ------------------------------------------------------------
CREATE TABLE `club_members` (
  `member_id`  INT NOT NULL AUTO_INCREMENT,
  `club_id`    INT NOT NULL,
  `user_id`    INT NOT NULL,
  `position`   VARCHAR(50) NOT NULL DEFAULT 'Member',
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `uq_members_club_user` (`club_id`, `user_id`),
  KEY `idx_members_user` (`user_id`),
  CONSTRAINT `fk_members_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- events
-- ------------------------------------------------------------
CREATE TABLE `events` (
  `event_id`     INT NOT NULL AUTO_INCREMENT,
  `club_id`      INT NOT NULL,
  `event_name`   VARCHAR(150) NOT NULL,
  `description`  TEXT,
  `event_date`   DATE NOT NULL,
  `event_time`   TIME NOT NULL,
  `location`     VARCHAR(150) NOT NULL,
  `image`        VARCHAR(255) DEFAULT NULL,
  `created_by`   INT DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `idx_events_club` (`club_id`),
  KEY `idx_events_date` (`event_date`),
  CONSTRAINT `fk_events_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_events_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- club_followers
-- ------------------------------------------------------------
CREATE TABLE `club_followers` (
  `follow_id`  INT NOT NULL AUTO_INCREMENT,
  `user_id`    INT NOT NULL,
  `club_id`    INT NOT NULL,
  PRIMARY KEY (`follow_id`),
  UNIQUE KEY `uq_followers_user_club` (`user_id`, `club_id`),
  KEY `idx_followers_club` (`club_id`),
  CONSTRAINT `fk_followers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_followers_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- rsvps  (who is going to which event)
-- ------------------------------------------------------------
CREATE TABLE `rsvps` (
  `rsvp_id`     INT NOT NULL AUTO_INCREMENT,
  `user_id`     INT NOT NULL,
  `event_id`    INT NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rsvp_id`),
  UNIQUE KEY `uq_rsvps_user_event` (`user_id`, `event_id`),
  KEY `idx_rsvps_event` (`event_id`),
  CONSTRAINT `fk_rsvps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rsvps_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- feedback  (ratings for past events)
-- ------------------------------------------------------------
CREATE TABLE `feedback` (
  `feedback_id`  INT NOT NULL AUTO_INCREMENT,
  `user_id`      INT NOT NULL,
  `event_id`     INT NOT NULL,
  `rating`       TINYINT NOT NULL COMMENT '1 to 5 stars',
  `comment`      TEXT,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`),
  UNIQUE KEY `uq_feedback_user_event` (`user_id`, `event_id`),
  KEY `idx_feedback_event` (`event_id`),
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Sample data
-- ============================================================

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `student_id`, `phone`) VALUES
(1,  'John Doe',     'student@test.com',   '$2y$10$d908mTP8JRuJ7/n1pZj.OOl6wV41aeauysPTlqd8QxWKX.3CRF3z2', 'student',   '0362514', '012-345 6789'),
(2,  'Aisha Rahman', 'organiser@test.com', '$2y$10$9nIEIHXtIQtQY/UKPztUZeQVTFy82WJSXOxXU44t7eJU0Z5wpR0Ja', 'organiser', '0358821', ''),
(3,  'Daniel Lim',   'daniel@test.com',    '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'student',   '0361102', ''),
(4,  'Priya Nair',   'priya@test.com',     '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'student',   '0360457', ''),
(5,  'Kavin Raj',    'kavin@test.com',     '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'organiser', '0357713', ''),
(6,  'Meera Devi',   'meera@test.com',     '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'student',   '0362290', ''),
(7,  'Marcus Tan',   'marcus@test.com',    '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'organiser', '0356648', ''),
(8,  'Sofia Chen',   'sofia@test.com',     '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'student',   '0363381', ''),
(9,  'Hana Yusof',   'hana@test.com',      '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'organiser', '0359904', ''),
(10, 'Ryan Ong',     'ryan@test.com',      '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'organiser', '0358127', ''),
(11, 'Nur Iman',     'nur@test.com',       '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'student',   '0364015', ''),
(12, 'Ethan Wong',   'ethan@test.com',     '$2y$10$d0V48dOyNdyAheKtNuRcX.iiOW2N9Vf59UCqUppp8QeFrqld2KGAq', 'organiser', '0357560', '');

INSERT INTO `clubs` (`club_id`, `club_name`, `category`, `description`, `organiser_id`) VALUES
(1, 'Computer Science Society', 'Academic',   'Coding nights, hackathons and tech talks for anyone curious about computing.', 2),
(2, 'Tamil Society',            'Cultural',   'Celebrating Tamil culture through festivals, music, food and friendship.', 5),
(3, 'Game Development Club',    'Technology', 'Design, build and playtest games together, from weekend game jams to workshops.', 7),
(4, 'Photography Club',         'Arts',       'Photo walks, editing sessions and exhibitions around campus and the city.', 9),
(5, 'Badminton Club',           'Sports',     'Weekly training sessions and friendly matches for every skill level.', 10),
(6, 'Debate Society',           'Academic',   'Sharpen your public speaking and compete in inter-university tournaments.', 12);

INSERT INTO `club_members` (`club_id`, `user_id`, `position`) VALUES
(1, 2,  'Club Head'),
(1, 3,  'Assistant Head'),
(1, 4,  'Treasurer'),
(1, 1,  'Event Crew'),
(2, 5,  'Club Head'),
(2, 6,  'Assistant Head'),
(3, 7,  'Club Head'),
(3, 8,  'Assistant Head'),
(3, 1,  'Secretary'),
(4, 9,  'Club Head'),
(5, 10, 'Club Head'),
(5, 11, 'Assistant Head'),
(6, 12, 'Club Head');

INSERT INTO `events` (`event_id`, `club_id`, `event_name`, `description`, `event_date`, `event_time`, `location`, `created_by`) VALUES
(1, 2, 'Tamil Society Welcoming Party', 'Meet the committee and new members over food, music and games. Everyone is welcome!', '2026-11-28', '18:00:00', 'Grand Hall',        5),
(2, 3, 'Game Dev Workshop',             'A hands-on intro to making your first 2D game in Godot. Bring a laptop.',            '2026-11-28', '14:00:00', 'Lab C3.12',         7),
(3, 1, 'Intro to Web Hackathon',        'Team up and build a website in one day. Mentors, snacks and prizes provided.',        '2026-12-02', '09:00:00', 'Library Level 2',   2),
(4, 4, 'Night Photo Walk',              'Learn long-exposure and low-light photography around the lake. Phones welcome.',      '2026-12-05', '19:30:00', 'Lakeside',          9),
(5, 5, 'Inter-Club Badminton Friendly', 'Friendly doubles matches against other clubs. Rackets available to borrow.',          '2026-12-10', '16:00:00', 'Sports Complex',    10),
(6, 6, 'Public Speaking Bootcamp',      'Practical drills to speak with confidence, from structure to body language.',        '2026-12-17', '10:00:00', 'Lecture Theatre 4', 12),
(7, 1, 'Python for Beginners',          'A beginner-friendly session covering Python basics through small exercises.',        '2026-11-12', '15:00:00', 'Lab C3.10',         2),
(8, 3, 'Mini Game Jam',                 'Make a tiny game in six hours around a surprise theme.',                              '2026-11-06', '10:00:00', 'Block D',           7);

INSERT INTO `club_followers` (`user_id`, `club_id`) VALUES
(1, 1), (1, 3), (1, 5),
(2, 1), (2, 4),
(3, 1), (3, 2),
(4, 1), (4, 4);

INSERT INTO `rsvps` (`user_id`, `event_id`) VALUES
(1, 2), (1, 3), (1, 5), (1, 7), (1, 8),
(2, 3), (2, 7),
(3, 1), (3, 3), (3, 7),
(4, 3), (4, 4), (4, 7),
(6, 1),
(8, 2), (8, 8),
(11, 5), (11, 6);

INSERT INTO `feedback` (`user_id`, `event_id`, `rating`, `comment`) VALUES
(4, 7, 5, 'Super clear explanations, perfect for beginners.'),
(3, 7, 4, 'Great pace. Would love a follow-up session!'),
(1, 8, 5, 'So much fun, made my first game!');
