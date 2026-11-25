<?php
/** @var \App\Core\View $this */
/** @var string $active */
$navItems = [
    ['key' => 'home', 'label' => 'Главная', 'path' => '/'],
    ['key' => 'sections', 'label' => 'Секции', 'path' => '/sections'],
    ['key' => 'submissions', 'label' => 'Заявки', 'path' => '/submissions'],
];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $this->url('/') ?>">Научная конференция</a>
        <div class="navbar-nav">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = ($active === $item['key']); ?>
                <a class="nav-link<?= $isActive ? ' active' : '' ?>"
                   <?= $isActive ? 'aria-current="page"' : '' ?>
                   href="<?= $this->url($item['path']) ?>">
                    <?= $this->e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
