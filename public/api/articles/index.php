<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$articles = [
    [
        'id' => 1,
        'title' => 'Оптимизация реляционных запросов и индексов в информационных системах учёта',
        'author' => 'Рахимов Фарход Джамшедович',
        'section' => 'Информационные системы и базы данных',
        'submitted_at' => '2026-11-12',
        'status' => 'accepted',
    ],
    [
        'id' => 2,
        'title' => 'Применение нейросетевых моделей для распознавания таджикской рукописной речи',
        'author' => 'Кодирова Мадина Саидовна',
        'section' => 'Искусственный интеллект и машинное обучение',
        'submitted_at' => '2026-11-14',
        'status' => 'under_review',
    ],
    [
        'id' => 3,
        'title' => 'Анализ уязвимостей веб-приложений и защита от межсайтового скриптинга',
        'author' => 'Хусейнов Умед Бахромович',
        'section' => 'Кибербезопасность и защита информации',
        'submitted_at' => '2026-11-15',
        'status' => 'accepted',
    ],
    [
        'id' => 4,
        'title' => 'Моделирование распределённых микросервисных систем в облачной среде',
        'author' => 'Ганиева Нигина Рустамовна',
        'section' => 'Программная инженерия',
        'submitted_at' => '2026-11-16',
        'status' => 'revision',
    ],
    [
        'id' => 5,
        'title' => 'Алгоритмы балансировки нагрузки в корпоративных сетях передачи данных',
        'author' => 'Солиев Бахтиёр Усманович',
        'section' => 'Компьютерные сети и телекоммуникации',
        'submitted_at' => '2026-11-18',
        'status' => 'rejected',
    ],
    [
        'id' => 6,
        'title' => '<img src=x onerror="alert(\'XSS\')"> Тестовый доклад по безопасности',
        'author' => 'Тестов <b>Алишер</b> Каримович',
        'section' => 'Тестовая секция',
        'submitted_at' => '2026-11-20',
        'status' => 'under_review',
    ],
];

$q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));
if ($q !== '') {
    $articles = array_values(array_filter(
        $articles,
        fn (array $a): bool => str_contains(mb_strtolower($a['title']), $q)
            || str_contains(mb_strtolower($a['author']), $q)
            || str_contains(mb_strtolower($a['section']), $q)
    ));
}

echo json_encode($articles, JSON_UNESCAPED_UNICODE);