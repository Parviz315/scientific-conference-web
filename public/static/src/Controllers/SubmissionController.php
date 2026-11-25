<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Repositories\SectionRepository;
use App\Repositories\SubmissionRepository;
use PDOException;

final class SubmissionController
{
    private const PER_PAGE = 10;

    private const ALLOWED_STATUSES = ['pending', 'under_review', 'approved', 'rejected'];

    private const MESSAGES = [
        'created'      => ['success', 'Заявка на доклад успешно создана.'],
        'updated'      => ['success', 'Изменения в заявке сохранены.'],
        'deleted'      => ['success', 'Заявка удалена.'],
        'approved'     => ['success', 'Заявка утверждена, рецензия зафиксирована в транзакции.'],
        'already_done' => ['warning', 'Заявка уже была обработана ранее.'],
        'has_links'    => ['danger',  'Нельзя удалить заявку, по которой есть связанные записи.'],
    ];

    public function __construct(
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly SectionRepository $sections = new SectionRepository(),
    ) {
    }

    /** GET /submissions */
    public function index(): void
    {
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $status = (string) ($_GET['status'] ?? '');

        $filters = [
            'q'          => trim((string) ($_GET['q'] ?? '')),
            'section_id' => $sectionId > 0 ? (string) $sectionId : '',
            'status'     => in_array($status, self::ALLOWED_STATUSES, true) ? $status : '',
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->submissions->paginate($filters, $page, self::PER_PAGE);

        $this->render('articles/index', [
            'items'    => $result['items'],
            'total'    => $result['total'],
            'page'     => $result['page'],
            'pages'    => $result['pages'],
            'filters'  => $filters,
            'sections' => $this->sections->all(),
            'notice'   => self::MESSAGES[$_GET['msg'] ?? ''] ?? null,
        ]);
    }

    /** GET /submissions/create */
    public function create(): void
    {
        $this->renderForm([], [], null, 200);
    }

    /** POST /submissions/create */
    public function store(): void
    {
        [$data, $errors] = $this->validate($_POST, null);
        if ($errors !== []) {
            $this->renderForm($_POST, $errors, null, 422);
            return;
        }

        $this->submissions->create($data);
        $this->redirect('/submissions?msg=created');
    }

    /** GET /submissions/{id}/edit */
    public function edit(int $id): void
    {
        $item = $this->findOrFail($id);
        $this->renderForm($item, [], $id, 200);
    }

    /** POST /submissions/{id}/edit */
    public function update(int $id): void
    {
        $this->findOrFail($id);
        [$data, $errors] = $this->validate($_POST, $id);
        if ($errors !== []) {
            $this->renderForm($_POST, $errors, $id, 422);
            return;
        }

        $this->submissions->update($id, $data);
        $this->redirect('/submissions?msg=updated');
    }

    /** POST /submissions/{id}/approve (транзакция: submissions + reviews) */
    public function approve(int $id): void
    {
        $this->findOrFail($id);
        $reviewerId = max(1, (int) ($_POST['reviewer_id'] ?? 1));
        $score = max(1, min(100, (int) ($_POST['score'] ?? 90)));

        $ok = $this->submissions->approveWithReview($id, $reviewerId, $score);
        $this->redirect('/submissions?msg=' . ($ok ? 'approved' : 'already_done'));
    }

    /** POST /submissions/{id}/delete */
    public function delete(int $id): void
    {
        $this->findOrFail($id);
        try {
            $this->submissions->delete($id);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1451) {
                $this->redirect('/submissions?msg=has_links');
            }
            throw $e;
        }

        $this->redirect('/submissions?msg=deleted');
    }

    /** Валидация на сервере: формат + допустимые id секции + 2 бизнес-правила */
    private function validate(array $input, ?int $exceptId): array
    {
        $v = new Validator($input);
        $v->required('title', 'Название доклада')
          ->minLength('title', 5, 'Название доклада')
          ->maxLength('title', 255, 'Название доклада')
          ->required('section_id', 'Научная секция')
          ->in('section_id', $this->sections->ids(), 'Научная секция')
          ->maxLength('abstract', 3000, 'Аннотация')
          ->maxLength('file_path', 255, 'Путь к файлу')
          ->required('status', 'Статус')
          ->in('status', self::ALLOWED_STATUSES, 'Статус');

        $errors = $v->errors();
        $title = trim((string) ($input['title'] ?? ''));
        $abstract = trim((string) ($input['abstract'] ?? ''));
        $sectionId = (int) ($input['section_id'] ?? 0);

        // Бизнес-правило 1: аннотация обязательна, если заявка переводится на рецензию или утверждение
        $status = (string) ($input['status'] ?? 'pending');
        if (!isset($errors['status']) && in_array($status, ['under_review', 'approved'], true) && $abstract === '') {
            $v->addError('abstract', 'Для статуса «На рецензии» или «Утверждена» необходимо указать аннотацию.');
        }

        // Бизнес-правило 2: не допускать пустых слов-заглушек в названии
        if (!isset($errors['title']) && strtolower($title) === 'тест') {
            $v->addError('title', 'Название доклада не может быть просто словом «тест».');
        }

        $data = [
            'section_id' => $sectionId,
            'title'      => $title,
            'abstract'   => $abstract !== '' ? $abstract : null,
            'file_path'  => trim((string) ($input['file_path'] ?? '')) ?: null,
            'status'     => $status,
        ];

        return [$data, $v->errors()];
    }

    private function findOrFail(int $id): array
    {
        $item = $this->submissions->find($id);
        if ($item === null) {
            http_response_code(404);
            exit('Заявка не найдена');
        }
        return $item;
    }

    private function renderForm(array $values, array $errors, ?int $id, int $statusCode): void
    {
        http_response_code($statusCode);
        $this->render('articles/form', [
            'values'   => $values,
            'errors'   => $errors,
            'id'       => $id,
            'sections' => $this->sections->all(),
        ]);
    }

    private function render(string $view, array $params): void
    {
        extract($params, EXTR_SKIP);
        $basePath = defined('BASE_PATH') ? \BASE_PATH : dirname(__DIR__, 2);
        require "{$basePath}/views/{$view}.php";
    }

    private function redirect(string $url): never
    {
        header("Location: {$url}", true, 302);
        exit;
    }
}