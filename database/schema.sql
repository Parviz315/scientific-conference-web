-- database/schema.sql: Модели физикии базаи додаҳо барои «Ташкили Конфронсии илмӣ»

DROP DATABASE IF EXISTS scientific_conference;
CREATE DATABASE scientific_conference
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE scientific_conference;

-- 1. Ҷадвали корбарон (Users)
CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'organizer', 'reviewer', 'participant') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ҷадвали секцияҳо / равияҳои илмӣ (Sections)
CREATE TABLE sections (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    room VARCHAR(50) NULL,
    schedule_time DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Ҷадвали мақолаҳо ва аризаҳо (Submissions)
CREATE TABLE submissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    abstract TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('pending', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_submissions_status (status),
    CONSTRAINT fk_submissions_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_submissions_section FOREIGN KEY (section_id)
        REFERENCES sections (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Ҷадвали тақризҳо ва баҳогузорӣ (Reviews)
CREATE TABLE reviews (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    submission_id INT UNSIGNED NOT NULL,
    reviewer_id INT UNSIGNED NOT NULL,
    score INT NOT NULL,
    comments TEXT NULL,
    reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT chk_review_score CHECK (score >= 1 AND score <= 100),
    CONSTRAINT fk_reviews_submission FOREIGN KEY (submission_id)
        REFERENCES submissions (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Ҷадвали огоҳиномаҳо (Notifications)
CREATE TABLE notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;