<?php

declare(strict_types=1);

namespace App\Controllers;

final class SubmissionController extends Controller
{
    /** @var array<int, array<string, mixed>> */
    private const DEMO_SUBMISSIONS = [
        1 => [
            'id' => 1,
            'title' => 'Архитектура лёгкого маршрутизатора на PHP 8',
            'author_name' => 'Рахимов Фарход',
            'affiliation' => 'ТГУ / Факультет ИТ',
            'section_title' => 'Программная инженерия и распределённые системы',
            'submitted_at' => '2026-11-12',
            'status' => 'accepted',
            'abstract' => 'Рассматривается реализация Front Controller и маршрутизации с параметрами пути без сторонних фреймворков.',
        ],
        2 => [
            'id' => 2,
            'title' => 'Безопасность подготовленных запросов и транзакций в PDO',
            'author_name' => 'Саидова Мадина',
            'affiliation' => 'ХГУ им. Б. Гафурова',
            'section_title' => 'Операционные системы и информационная безопасность',
            'submitted_at' => '2026-11-15',
            'status' => 'pending',
            'abstract' => 'Анализируются типовые ошибки при работе с реляционными БД и экранировании поисковых шаблонов.',
        ],
        3 => [
            'id' => 3,
            'title' => '<b>Тест экранирования HTML</b> и моделирование расписания',
            'author_name' => 'Олимов Рустам',
            'affiliation' => 'Политехнический институт',
            'section_title' => 'Искусственный интеллект и анализ данных',
            'submitted_at' => '2026-11-18',
            'status' => 'revision',
            'abstract' => 'Пример записи с HTML-тегами в заголовке для проверки защиты от XSS через метод View::e().',
        ],
    ];

    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $items = self::DEMO_SUBMISSIONS;

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $items = array_filter($items, static function (array $row) use ($needle): bool {
                $haystack = mb_strtolower((string) $row['title'] . ' ' . (string) $row['author_name']);
                return mb_strpos($haystack, $needle) !== false;
            });
        }

        $this->render('submissions/index', [
            'title' => 'Заявки на доклады',
            'active' => 'submissions',
            'submissions' => array_values($items),
            'query' => $query,
            'currentPage' => 1,
            'totalPages' => 1,
        ]);
    }

    public function show(int $id): void
    {
        $submission = self::DEMO_SUBMISSIONS[$id] ?? null;
        if ($submission === null) {
            $this->abort(404, 'Заявка на доклад не найдена.');
        }

        $this->render('submissions/show', [
            'title' => 'Заявка #' . $id,
            'active' => 'submissions',
            'submission' => $submission,
        ]);
    }
}
