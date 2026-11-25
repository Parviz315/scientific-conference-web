<?php
/** @var \App\Core\View $this */
/** @var array<string, mixed> $submission */
?>
<div class="mb-3">
    <a href="<?= $this->url('/submissions') ?>">&larr; Назад к списку заявок</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-body-secondary small mb-1">
            Заявка #<?= (int) $submission['id'] ?> &middot; <?= $this->e($this->date((string) $submission['submitted_at'])) ?>
        </div>
        <h1 class="h4 mb-3"><?= $this->e($submission['title']) ?></h1>
        <p class="mb-1"><strong>Автор:</strong> <?= $this->e($submission['author_name']) ?></p>
        <p class="mb-1"><strong>Организация:</strong> <?= $this->e($submission['affiliation']) ?></p>
        <p class="mb-3"><strong>Секция:</strong> <?= $this->e($submission['section_title']) ?></p>
        <hr>
        <p class="mb-0"><?= $this->e($submission['abstract']) ?></p>
    </div>
</div>
