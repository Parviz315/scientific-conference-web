CREATE DATABASE IF NOT EXISTS scientific_conference
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scientific_conference;

-- Справочник 1: Научные секции
CREATE TABLE sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    hall VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Справочник 2: Докладчики / авторы
CREATE TABLE speakers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    organization VARCHAR(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Главная сущность: Научные доклады / статьи
CREATE TABLE articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    abstract TEXT NULL,
    section_id INT UNSIGNED NOT NULL,
    speaker_id INT UNSIGNED NOT NULL,
    presentation_date DATE NOT NULL,
    duration_minutes TINYINT UNSIGNED NOT NULL DEFAULT 15,
    status ENUM('submitted', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'submitted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE RESTRICT,
    CONSTRAINT fk_articles_speaker FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Вторая таблица для транзакции: Программа выступлений конференции
CREATE TABLE schedule_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL UNIQUE,
    section_id INT UNSIGNED NOT NULL,
    slot_date DATE NOT NULL,
    order_number INT UNSIGNED NOT NULL,
    CONSTRAINT fk_schedule_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;