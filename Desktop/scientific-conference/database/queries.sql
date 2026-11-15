-- database/queries.sql: Дархостҳои назоратӣ барои лоиҳа

USE scientific_conference;

-- 1. Рӯйхати мақолаҳо бо номи муаллиф ва секция (Барои саҳифаи умумии мақолаҳо)
SELECT s.id, s.title, u.username AS author, sec.title AS section_name, s.status
FROM submissions s
JOIN users u ON u.id = s.user_id
JOIN sections sec ON sec.id = s.section_id
ORDER BY s.submitted_at DESC;

-- 2. Ҳисобот: Шумораи мақолаҳо аз рӯи секцияҳо (Бо истифодаи GROUP BY ва LEFT JOIN)
SELECT sec.title AS section_name, COUNT(s.id) AS total_submissions
FROM sections sec
LEFT JOIN submissions s ON s.section_id = sec.id
GROUP BY sec.id, sec.title;

-- 3. Филтри мақолаҳо аз рӯи статус ('under_review' ё 'approved')
SELECT title, abstract, status 
FROM submissions 
WHERE status = 'under_review';

-- 4. Миёнаи баҳои тақризҳо барои ҳар як мақола
data: 
SELECT s.title, AVG(r.score) AS average_score, COUNT(r.id) AS total_reviews
FROM submissions s
LEFT JOIN reviews r ON r.submission_id = s.id
GROUP BY s.id, s.title;

-- 5. Санҷиши рамзгузорӣ ва дарозии сатрҳо дар UTF8MB4
SELECT title, CHAR_LENGTH(title) AS chars_count, LENGTH(title) AS bytes_count
FROM sections WHERE id = 1;