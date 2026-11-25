<?php
/** @var \App\Core\View $this */
/** @var int $code */
/** @var string $message */
?>
<div class="py-5 text-center">
    <div class="display-5 fw-bold text-body-secondary"><?= (int) $code ?></div>
    <h1 class="h4 mt-2"><?= $this->e($message) ?></h1>
    <a class="btn btn-primary mt-3" href="<?= $this->url('/') ?>">На главную</a>
</div>
