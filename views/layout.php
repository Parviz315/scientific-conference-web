<?php
/** @var \App\Core\View $this */
/** @var string $content */
/** @var string|null $title */
/** @var string|null $active */
$activeSection = $active ?? '';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title ?? 'Научная конференция') ?> — Конференция</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?= $this->partial('nav', ['active' => $activeSection]) ?>

    <main class="container py-4">
        <?= $content ?>
    </main>

    <footer class="border-top py-3 mt-5 text-body-secondary small">
        <div class="container d-flex justify-content-between flex-wrap gap-2">
            <span>Организация научной конференции (Backend 1 — PHP 8)</span>
            <span>Тег: day-12</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
