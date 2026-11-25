<?php
/** @var \App\Core\View $this */
/** @var array<int, array<string, mixed>> $sections */
?>
<h1 class="h3 mb-3">Секции конференции</h1>

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>Код</th>
                <th>Название секции</th>
                <th>Руководитель</th>
                <th>Аудитория</th>
                <th>Докладов</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sections as $section): ?>
                <tr>
                    <td><?= $this->e($section['code']) ?></td>
                    <td><?= $this->e($section['title']) ?></td>
                    <td><?= $this->e($section['chair']) ?></td>
                    <td><?= $this->e($section['room']) ?></td>
                    <td><?= (int) $section['submissions_count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
