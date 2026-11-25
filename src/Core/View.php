<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;
use RuntimeException;

final class View
{
    private const LABELS = [
        'submission_status' => [
            'pending' => 'На рецензии',
            'accepted' => 'Принят',
            'revision' => 'На доработку',
            'rejected' => 'Отклонён',
        ],
    ];

    public function __construct(
        private readonly string $viewsPath
    ) {}

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        $content = $this->renderFile($template, $data);

        if ($layout === null) {
            return $content;
        }

        return $this->renderFile($layout, $data + ['content' => $content]);
    }

    /** @param array<string, mixed> $data */
    private function renderFile(string $template, array $data): string
    {
        $file = $this->viewsPath . '/' . ltrim($template, '/') . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Шаблон не найден: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function url(string $path): string
    {
        $cleanPath = '/' . ltrim($path, '/');
        return Router::basePath() . $cleanPath;
    }

    /** @param array<string, mixed> $data */
    public function partial(string $name, array $data = []): string
    {
        return $this->renderFile('partials/' . ltrim($name, '/'), $data);
    }

    public function date(?string $value, string $format = 'd.m.Y'): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($dt === false) {
            $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        }
        return $dt instanceof DateTimeImmutable ? $dt->format($format) : $this->e($value);
    }

    public function label(string $group, string $value): string
    {
        return self::LABELS[$group][$value] ?? $value;
    }

    /** @return array<string, string> */
    public function labels(string $group): array
    {
        return self::LABELS[$group] ?? [];
    }
}
