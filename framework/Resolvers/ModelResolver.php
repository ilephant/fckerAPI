<?php

namespace Fcker\Framework\Resolvers;

class ModelResolver
{
    public static function resolve(string $table): ?object
    {
        // 1) Конфиг маппинга таблиц на модели
        $configPath = __ROOT__ . 'config/models.php';
        if (is_file($configPath)) {
            $map = require $configPath;
            if (isset($map[$table]) && class_exists($map[$table])) {
                return new $map[$table]();
            }
        }

        // 2) Конвенция имён: posts -> PostModel, users -> UserModel
        $modelName = ucfirst(rtrim($table, 's')) . 'Model';
        $modelClass = "\\Fcker\\Application\\Models\\{$modelName}";
        if (class_exists($modelClass)) {
            return new $modelClass();
        }

        return null;
    }
}
