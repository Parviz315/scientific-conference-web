-- database/seed.sql: Додаҳои санҷишӣ барои «Ташкили Конфронсии илмӣ»

USE scientific_conference;
SET NAMES utf8mb4;

-- 1. Корбарони санҷишӣ (Парол барои ҳама: Secret123!)
INSERT INTO users (id, username, email, password_hash, role, is_active) VALUES
(1, 'admin_parviz', 'admin@conference.tj', '$2y$10$fvScIwtN3zcpXPoVnGkI/euhKZRFZ7uIaJngo.G4iwxoj0PoVhD70', 'admin', 1),
(2, 'organizer_sitora', 'organizer@conference.tj', '$2y$10$fvScIwtN3zcpXPoVnGkI/euhKZRFZ7uIaJngo.G4iwxoj0PoVhD70', 'organizer', 1),
(3, 'reviewer_rustam', 'reviewer@conference.tj', '$2y$10$fvScIwtN3zcpXPoVnGkI/euhKZRFZ7uIaJngo.G4iwxoj0PoVhD70', 'reviewer', 1),
(4, 'author_farhod', 'farhod@example.tj', '$2y$10$fvScIwtN3zcpXPoVnGkI/euhKZRFZ7uIaJngo.G4iwxoj0PoVhD70', 'participant', 1),
(5, 'author_madina', 'madina@example.tj', '$2y$10$fvScIwtN3zcpXPoVnGkI/euhKZRFZ7uIaJngo.G4iwxoj0PoVhD70', 'participant', 1);

-- 2. Секцияҳо
INSERT INTO sections (id, title, room, schedule_time) VALUES
(1, 'Барномасозӣ ва технологияҳои веб', 'Толори 101', '2026-10-15 09:00:00'),
(2, 'Информатикаи назариявӣ ва сунъӣ', 'Тоҳирӣ 202', '2026-10-15 11:30:00'),
(3, 'Системаҳои иттилоотӣ дар идоракунӣ', 'Лекторияи асосӣ', '2026-10-16 10:00:00');

-- 3. Мақолаҳо / Аризаҳо
INSERT INTO submissions (id, user_id, section_id, title, abstract, file_path, status) VALUES
(1, 4, 1, 'Истифодаи фреймворкҳои муосир дар توسعه веб-барномаҳо', 'Дар ин мақола муқоисаи фреймворкҳои PHP ва JS оварда шудааст...', 'uploads/sub1.pdf', 'approved'),
(2, 5, 2, 'Алгоритмҳои зеҳни сунъӣ барои таҳлили додаҳо', 'Баррасии шабакаҳои нейронӣ ва усулҳои коркарди онҳо...', 'uploads/sub2.pdf', 'under_review'),
(3, 4, 3, 'Таҳлили амнияти системаҳои پایгоҳи додаҳо', 'Масъалаҳои ҳифзи иттилоот дар системаҳои корпоративӣ...', 'uploads/sub3.pdf', 'pending');

-- 4. Тақризҳо
INSERT INTO reviews (id, submission_id, reviewer_id, score, comments) VALUES
(1, 1, 3, 95, 'Мақола хеле хуб навишта шудааст, тавсия ба нашр.'),
(2, 2, 3, 78, 'Матни анҷомёфта ба талабот ҷавобгӯ аст, вале ислоҳи қисми амалӣ лозим аст.');

-- 5. Огоҳиномаҳо
INSERT INTO notifications (user_id, message, is_read) VALUES
(4, 'Мақолаи شما тасдиқ карда шуд ва ба нашр тавсия гардид.', 1),
(5, 'Мақолаи شما ба марҳилаи тақриз гузаронида шуд.', 0);