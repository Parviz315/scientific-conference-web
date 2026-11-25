<?php
/** @var \App\Core\View $this */
/** @var int $currentPage */
/** @var int $totalPages */
if ($totalPages <= 1) {
    return;
}
?>
<nav aria-label="Навигация по страницам" class="mt-3">
    <ul class="pagination pagination-sm mb-0">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item<?= $p === $currentPage ? ' active' : '' ?>">
                <a class="page-link" href="<?= $this->url('/submissions?page=' . $p) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
