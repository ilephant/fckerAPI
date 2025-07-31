# Решение проблем с кодировкой в базе данных

## Проблема
При работе с русским текстом в базе данных могут возникать проблемы с кодировкой, когда символы отображаются как "????" или "ÐŸÐµÑ€Ð²Ñ‹Ð¹ Ð¿Ð¾ÑÑ‚".

## Причины проблемы
1. Неправильная настройка кодировки в MySQL
2. Отсутствие инициализации кодировки при подключении PDO
3. Неправильные настройки в Docker контейнере

## Решение

### 1. Настройки MySQL в Docker
Добавлены переменные окружения в `docker-compose.yml`:
```yaml
environment:
  MYSQL_CHARACTER_SET_SERVER: utf8mb4
  MYSQL_COLLATION_SERVER: utf8mb4_unicode_ci
  MYSQL_DEFAULT_CHARSET: utf8mb4
  MYSQL_DEFAULT_COLLATION: utf8mb4_unicode_ci
```

### 2. Конфигурация MySQL
Создан файл `docker/mysql.cnf` с правильными настройками кодировки:
```ini
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
default-storage-engine = InnoDB
innodb_default_charset = utf8mb4
innodb_default_collation = utf8mb4_unicode_ci

[mysql]
default-character-set = utf8mb4

[client]
default-character-set = utf8mb4
```

### 3. Инициализация PDO
В `framework/Core/Model.php` добавлена инициализация кодировки:
```php
PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
```

### 4. Настройки схемы базы данных
В `database/schema.sql` добавлены команды для установки кодировки:
```sql
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET CHARACTER SET utf8mb4;
SET character_set_connection=utf8mb4;
```

## Проверка кодировки

### Запуск скрипта проверки
```bash
php scripts/check_encoding.php
```

Этот скрипт проверит:
- Настройки character_set в MySQL
- Настройки collation
- Кодировку таблиц
- Тестовую вставку русского текста

### Ручная проверка через MySQL
```sql
-- Проверка настроек сервера
SHOW VARIABLES LIKE 'character_set%';
SHOW VARIABLES LIKE 'collation%';

-- Проверка кодировки таблиц
SELECT TABLE_NAME, TABLE_COLLATION 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'fcker_api';

-- Тест вставки русского текста
CREATE TEMPORARY TABLE test_encoding (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    text_content TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO test_encoding (text_content) VALUES ('Первый пост - тест кодировки');
SELECT * FROM test_encoding;
```

## Перезапуск с новыми настройками

### 1. Остановить контейнеры
```bash
docker-compose down
```

### 2. Удалить данные MySQL (если нужно)
```bash
docker-compose down -v
```

### 3. Перезапустить с новыми настройками
```bash
docker-compose up -d
```

### 4. Проверить кодировку
```bash
php scripts/check_encoding.php
```

## Дополнительные рекомендации

### 1. Проверка в PHP
Убедитесь, что в PHP файлах используется UTF-8:
```php
// В начале файла
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
```

### 2. HTTP заголовки
Добавьте заголовок Content-Type в ответы API:
```php
header('Content-Type: application/json; charset=utf-8');
```

### 3. HTML мета-тег
Если есть веб-интерфейс, добавьте:
```html
<meta charset="UTF-8">
```

## Результат
После применения всех изменений:
- ✅ Русский текст будет корректно сохраняться в базе данных
- ✅ Символы будут отображаться правильно
- ✅ Поддержка эмодзи и других Unicode символов
- ✅ Правильная сортировка и поиск по русскому тексту 