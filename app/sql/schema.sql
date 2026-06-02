SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS session_ratings, user_favorite_games, comments,
    participations, sessions, tournaments, games, locations, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
                       id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                       username      VARCHAR(50)  NOT NULL,
                       role ENUM('user','admin') NOT NULL DEFAULT 'user',
                       email         VARCHAR(255) NOT NULL,
                       password_hash VARCHAR(255) NOT NULL,
                       city VARCHAR(100) NULL,
                       created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                       UNIQUE KEY uq_users_username (username),
                       UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locations (
                           id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                           name       VARCHAR(120) NOT NULL,
                           address    VARCHAR(255) NULL,
                           city       VARCHAR(100) NOT NULL,
                           created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE games (
                       id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                       bgg_id INT UNSIGNED NOT NULL UNIQUE,
                       name VARCHAR(255) NOT NULL,
                       year_published SMALLINT NULL,
                       min_players TINYINT UNSIGNED NULL,
                       max_players TINYINT UNSIGNED NULL,
                       playing_time SMALLINT UNSIGNED NULL,
                       thumbnail_url VARCHAR(500) NULL,
                       description TEXT NULL,
                       cached_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tournaments (
                             id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                             name        VARCHAR(150) NOT NULL,
                             creator_id  INT UNSIGNED NOT NULL,
                             description TEXT NULL,
                             created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
                          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                          creator_id INT UNSIGNED NOT NULL,
                          game_id INT UNSIGNED NULL,
                          status ENUM('open','cancelled','finished') DEFAULT 'open',
                          tournament_id INT UNSIGNED NULL,
                          location_id INT UNSIGNED NOT NULL,
                          title VARCHAR(150) NOT NULL,
                          scheduled_at DATETIME NOT NULL,
                          max_players TINYINT UNSIGNED NOT NULL,
                          is_private TINYINT(1) NOT NULL DEFAULT 0,
                          description TEXT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                          FOREIGN KEY (creator_id)  REFERENCES users(id)     ON DELETE CASCADE,
                          FOREIGN KEY (game_id)     REFERENCES games(id)     ON DELETE SET NULL,
                          FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE RESTRICT,
                          FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE SET NULL,
                          KEY idx_sessions_scheduled (scheduled_at),
                          KEY idx_sessions_game (game_id),
                          KEY idx_sessions_location (location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE participations (
                                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                user_id INT UNSIGNED NOT NULL,
                                session_id INT UNSIGNED NOT NULL,
                                status ENUM('pending','approved') NOT NULL DEFAULT 'approved',
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                                FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
                                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
                                UNIQUE KEY uq_participation (user_id, session_id),
                                KEY idx_participations_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
                          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                          session_id INT UNSIGNED NOT NULL,
                          user_id INT UNSIGNED NOT NULL,
                          body TEXT NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                          FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
                          FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
                          KEY idx_comments_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_favorite_games (
                                     user_id    INT UNSIGNED NOT NULL,
                                     game_id    INT UNSIGNED NOT NULL,
                                     created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                     PRIMARY KEY (user_id, game_id),
                                     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                                     FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE session_ratings (
                                 id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                 session_id INT UNSIGNED NOT NULL,
                                 user_id    INT UNSIGNED NOT NULL,
                                 rating     TINYINT UNSIGNED NOT NULL,
                                 comment    TEXT NULL,
                                 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
                                 FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
                                 UNIQUE KEY uq_rating (session_id, user_id),
                                 CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;