<?php
/** @var \App\Core\View $this */
/** @var string $conferenceTitle */
/** @var string $conferenceDate */
/** @var int $sectionsCount */
/** @var int $submissionsCount */
?>
<div class="p-4 bg-light rounded-3 border mb-4">
    <h1 class="h3 mb-2"><?= $this->e($conferenceTitle) ?></h1>
    <p class="text-body-secondary mb-3">Дата проведения: <?= $this->e($this->date($conferenceDate)) ?></p>
    <a class="btn btn-primary" href="<?= $this->url('/submissions') ?>">Перейти к заявкам</a>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="text-body-secondary small">Секций в программе</div>
                <div class="fs-4 fw-semibold"><?= (int) $sectionsCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="text-body-secondary small">Зарегистрировано докладов</div>
                <div class="fs-4 fw-semibold"><?= (int) $submissionsCount ?></div>
            </div>
        </div>
    </div>
</div>
