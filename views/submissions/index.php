<?php
/** @var \App\Core\View $this */
/** @var array<int, array<string, mixed>> $submissions */
/** @var string $query */
/** @var int $currentPage */
/** @var int $totalPages */
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="h3 mb-0">Заявки на доклады</h1>
    <form class="d-flex gap-2" method="get" action="<?= $this->url('/submissions') ?>">
        <input class="form-control form-control-sm" type="search" name="q"
               value="<?= $this->e($query) ?>" placeholder="Поиск по теме или автору">
        <button class="btn btn-sm btn-outline-primary" type="submit">Найти</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Тема доклада</th>
                <th>Автор</th>
                <th>Секция</th>
                <th>Дата</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td>
                        <a href="<?= $this->url('/submissions/' . (int) $item['id']) ?>">
                            <?= $this->e($item['title']) ?>
                        </a>
                    </td>
                    <td><?= $this->e($item['author_name']) ?></td>
                    <td><?= $this->e($item['section_title']) ?></td>
                    <td><?= $this->e($this->date((string) $item['submitted_at'])) ?></td>
                    <td>
                        <span class="badge bg-secondary">
                            <?= $this->e($this->label('submission_status', (string) $item['status'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($submissions === []): ?>
                <tr>
                    <td colspan="6" class="text-center text-body-secondary py-4">Заявки не найдены.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->partial('pagination', ['currentPage' => $currentPage, 'totalPages' => $totalPages]) ?>
