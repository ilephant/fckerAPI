# Валидация данных

## Обзор

Фреймворк предоставляет мощную систему валидации данных через класс `Validator`.

## Основные методы

### required($field, $message = null)
Проверяет, что поле обязательно заполнено.

```php
$validator = new Validator($data);
$validator->required('email');
```

### email($field, $message = null)
Проверяет корректность email адреса.

```php
$validator->email('email');
```

### min($field, $length, $message = null)
Проверяет минимальную длину строки.

```php
$validator->min('password', 6);
```

### max($field, $length, $message = null)
Проверяет максимальную длину строки.

```php
$validator->max('title', 255);
```

### numeric($field, $message = null)
Проверяет, что значение является числом.

```php
$validator->numeric('age');
```

### in($field, $values, $message = null)
Проверяет, что значение находится в списке допустимых значений.

```php
$validator->in('status', ['active', 'inactive', 'pending']);
```

### unique($field, $table, $column = null, $except = null, $message = null)
Проверяет уникальность значения в базе данных.

```php
// Простая проверка уникальности
$validator->unique('email', 'users');

// Проверка с исключением (для обновления записи)
$validator->unique('email', 'users', 'email', $userId);

// Проверка уникальности в другой колонке
$validator->unique('username', 'users', 'username');
```

## Примеры использования

### Регистрация пользователя

```php
$validator = new Validator($data);
$validator
    ->required('name')
    ->min('name', 3)
    ->required('email')
    ->email('email')
    ->unique('email', 'users')
    ->required('password')
    ->min('password', 6);

if ($validator->fails()) {
    $errors = $validator->getErrors();
    // Обработка ошибок
}
```

### Обновление пользователя

```php
$validator = new Validator($data);
$validator
    ->required('name')
    ->min('name', 3)
    ->required('email')
    ->email('email')
    ->unique('email', 'users', 'email', $userId); // Исключаем текущего пользователя

if ($validator->fails()) {
    $errors = $validator->getErrors();
    // Обработка ошибок
}
```

### Создание поста

```php
$validator = new Validator($data);
$validator
    ->required('title')
    ->max('title', 255)
    ->required('content')
    ->min('content', 10)
    ->in('status', ['draft', 'published', 'archived']);

if ($validator->fails()) {
    $errors = $validator->getErrors();
    // Обработка ошибок
}
```

## Получение ошибок

```php
// Все ошибки
$errors = $validator->getErrors();

// Первая ошибка для конкретного поля
$firstError = $validator->getFirstError('email');

// Проверка наличия ошибок
if ($validator->fails()) {
    // Есть ошибки
}

if ($validator->passes()) {
    // Нет ошибок
}
```

## Поддерживаемые модели

Валидатор автоматически поддерживает следующие модели:
- `users` → `UserModel`
- `posts` → `PostModel`

Для добавления новых моделей отредактируйте метод `getModelClass()` в классе `Validator`. 