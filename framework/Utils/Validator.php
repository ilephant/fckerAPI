<?php

namespace Fcker\Framework\Utils;

use Fcker\Framework\Resolvers\ModelResolver;

class Validator
{
    private array $errors = [];
    private array $data;
    private $modelResolver; // callable|null

    public function __construct(array $data, ?callable $modelResolver = null)
    {
        $this->data = $data;
        $this->modelResolver = $modelResolver;
    }

    public function required(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if (empty($value) && $value !== '0') {
            $this->errors[$field][] = $message ?? "The {$field} field is required.";
        }
        return $this;
    }

    public function email(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "The {$field} must be a valid email address.";
        }
        return $this;
    }

    public function min(string $field, int $length, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if (!empty($value) && strlen($value) < $length) {
            $this->errors[$field][] = $message ?? "The {$field} must be at least {$length} characters.";
        }
        return $this;
    }

    public function max(string $field, int $length, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if (!empty($value) && strlen($value) > $length) {
            $this->errors[$field][] = $message ?? "The {$field} may not be greater than {$length} characters.";
        }
        return $this;
    }

    public function numeric(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field][] = $message ?? "The {$field} must be a number.";
        }
        return $this;
    }

    public function in(string $field, array $values, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if (!empty($value) && !in_array($value, $values)) {
            $this->errors[$field][] = $message ?? "The {$field} must be one of: " . implode(', ', $values);
        }
        return $this;
    }

    public function unique(string $field, string $table, ?string $column = null, ?int $except = null, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        $column = $column ?: $field;

        if (!empty($value)) {
            try {
                $model = null;

                if (is_callable($this->modelResolver)) {
                    $model = call_user_func($this->modelResolver, $table);
                }

                if (!$model) {
                    $model = ModelResolver::resolve($table);
                }

                if ($model) {
                    $existingRecord = $model->findBy($column, $value);
                    if ($existingRecord && $except && $existingRecord['id'] != $except) {
                        $this->errors[$field][] = $message ?? "The {$field} has already been taken.";
                    } elseif ($existingRecord && !$except) {
                        $this->errors[$field][] = $message ?? "The {$field} has already been taken.";
                    }
                }
            } catch (\Exception $e) {
                $this->errors[$field][] = $message ?? "The {$field} validation failed.";
            }
        }
        return $this;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function getErrors(): array { return $this->errors; }
    public function getFirstError(string $field): ?string { return $this->errors[$field][0] ?? null; }
}
