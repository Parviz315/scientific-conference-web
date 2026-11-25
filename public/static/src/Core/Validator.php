<?php
declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;

final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    private function value(string $field): string
    {
        return trim((string) ($this->data[$field] ?? ''));
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->addError($field, "Поле «{$label}» обязательно для заполнения.");
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        $val = $this->value($field);
        if ($val !== '' && mb_strlen($val) < $min) {
            $this->addError($field, "Поле «{$label}» должно содержать минимум {$min} символов.");
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $val = $this->value($field);
        if ($val !== '' && mb_strlen($val) > $max) {
            $this->addError($field, "Поле «{$label}» не должно превышать {$max} символов.");
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        $val = $this->value($field);
        if ($val !== '' && !in_array($val, array_map('strval', $allowed), true)) {
            $this->addError($field, "Выбрано недопустимое значение в поле «{$label}».");
        }
        return $this;
    }

    public function date(string $field, string $label): self
    {
        $val = $this->value($field);
        if ($val !== '') {
            $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $val);
            if ($dt === false || $dt->format('Y-m-d') !== $val) {
                $this->addError($field, "Поле «{$label}» должно содержать корректную дату (ГГГГ-ММ-ДД).");
            }
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] ??= $message;
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}