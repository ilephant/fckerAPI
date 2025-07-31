<?php

namespace Fcker\Framework\Utils;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
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
                // Создаем экземпляр модели для проверки уникальности
                $modelClass = $this->getModelClass($table);
                if ($modelClass) {
                    $model = new $modelClass();
                    
                    // Проверяем существование записи с таким значением
                    $existingRecord = $model->findBy($column, $value);
                    
                    // Если запись существует и это не исключаемый ID
                    if ($existingRecord && $except && $existingRecord['id'] != $except) {
                        $this->errors[$field][] = $message ?? "The {$field} has already been taken.";
                    } elseif ($existingRecord && !$except) {
                        $this->errors[$field][] = $message ?? "The {$field} has already been taken.";
                    }
                }
            } catch (\Exception $e) {
                // В случае ошибки БД, считаем что валидация не прошла
                $this->errors[$field][] = $message ?? "The {$field} validation failed.";
            }
        }
        
        return $this;
    }

    private function getModelClass(string $table): ?string
    {
        // Сначала проверяем хардкодированные модели
        $modelMap = [
            'users' => \Fcker\Application\Models\UserModel::class,
            'posts' => \Fcker\Application\Models\PostModel::class,
        ];
        
        if (isset($modelMap[$table])) {
            return $modelMap[$table];
        }
        
        // Пытаемся найти модель по соглашению именования
        $modelName = ucfirst(rtrim($table, 's')) . 'Model';
        $modelClass = "\\Fcker\\Application\\Models\\{$modelName}";
        
        if (class_exists($modelClass)) {
            return $modelClass;
        }
        
        return null;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}
