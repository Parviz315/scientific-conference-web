## Лекция 13 (`day-13`) — Бэкенд на чистом PHP с PDO

- Настроено подключение к базе данных `scientific_conference` (`utf8mb4`) через PDO (`App\Core\Database`) с параметрами в `config/config.php`.
- Реализована серверная валидация данных и бизнес-правил (`App\Core\Validator` и `SubmissionController`).
- Реализованы репозитории (`SectionRepository`, `SubmissionRepository`) с подготовленными запросами, экранированием спецсимволов `LIKE`, поиском, фильтрацией, пагинацией и CRUD.
- Реализовано утверждение заявки с записью оценки в `reviews` в рамках транзакции (`beginTransaction` / `commit` / `rollBack`), паттерн PRG и обработка ошибок `404`, `422` и `500`.