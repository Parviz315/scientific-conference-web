<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use Throwable;

final class SubmissionRepository
{
    /** Белый список полей заявки, разрешённых для создания и обновления */
    public const FIELDS = [
        'section_id',
        'title',
        'abstract',
        'file_path',
        'status',
    ];

    private const SELECT = '
        SELECT sub.*,
               sec.title AS section_title,
               sec.room  AS section_room
        FROM submissions sub
        JOIN sections sec ON sec.id = sub.section_id';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $conditions[] = "(sub.title LIKE :q OR sub.abstract LIKE :q)";
            $params[':q'] = '%' . addcslashes($q, '%_\\') . '%';
        }

        if (!empty($filters['section_id'])) {
            $conditions[] = 'sub.section_id = :section_id';
            $params[':section_id'] = (int) $filters['section_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'sub.status = :status';
            $params[':status'] = (string) $filters['status'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }

    public function paginate(array $filters, int $page, int $perPage = 10): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $countSql = "SELECT COUNT(*) FROM submissions sub {$where}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        $sql = self::SELECT . " {$where} ORDER BY sub.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(self::SELECT . ' WHERE sub.id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $columns = implode(', ', self::FIELDS);
        $placeholders = ':' . implode(', :', self::FIELDS);
        $stmt = $this->db->prepare("INSERT INTO submissions ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($this->onlyFields($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(fn (string $f): string => "{$f} = :{$f}", self::FIELDS));
        $stmt = $this->db->prepare("UPDATE submissions SET {$set} WHERE id = :id");
        $stmt->execute($this->onlyFields($data) + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM submissions WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Транзакция: утверждение заявки + запись итоговой оценки в reviews.
     * Условный UPDATE защищает от повторного утверждения.
     */
    public function approveWithReview(int $submissionId, int $reviewerId, int $score): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE submissions
                SET status = 'approved'
                WHERE id = :id AND status IN ('pending', 'under_review')
            ");
            $stmt->execute([':id' => $submissionId]);

            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }

            $ins = $this->db->prepare('
                INSERT INTO reviews (submission_id, reviewer_id, score)
                VALUES (:submission_id, :reviewer_id, :score)
            ');
            $ins->execute([
                ':submission_id' => $submissionId,
                ':reviewer_id'   => $reviewerId,
                ':score'         => $score,
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function onlyFields(array $data): array
    {
        $result = [];
        foreach (self::FIELDS as $field) {
            $result[$field] = $data[$field] ?? null;
        }
        return $result;
    }
}