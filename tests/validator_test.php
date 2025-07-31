<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Fcker\Framework\Utils\Validator;

echo "Testing Validator methods...\n";

// Тест 1: Базовая валидация
echo "\n1. Testing basic validation:\n";
$data1 = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123'
];

$validator1 = new Validator($data1);
$validator1
    ->required('name')
    ->min('name', 3)
    ->required('email')
    ->email('email')
    ->required('password')
    ->min('password', 6);

if ($validator1->fails()) {
    echo "Validation failed:\n";
    print_r($validator1->getErrors());
} else {
    echo "✓ Basic validation passed!\n";
}

// Тест 2: Валидация с ошибками
echo "\n2. Testing validation with errors:\n";
$data2 = [
    'name' => 'Jo', // Слишком короткое
    'email' => 'invalid-email', // Неверный email
    'password' => '123' // Слишком короткий пароль
];

$validator2 = new Validator($data2);
$validator2
    ->required('name')
    ->min('name', 3)
    ->required('email')
    ->email('email')
    ->required('password')
    ->min('password', 6);

if ($validator2->fails()) {
    echo "Validation failed (expected):\n";
    print_r($validator2->getErrors());
} else {
    echo "✗ Validation should have failed!\n";
}

// Тест 3: Проверка метода in()
echo "\n3. Testing 'in' validation:\n";
$data3 = [
    'status' => 'active',
    'role' => 'invalid_role'
];

$validator3 = new Validator($data3);
$validator3
    ->in('status', ['active', 'inactive', 'pending'])
    ->in('role', ['admin', 'user', 'moderator']);

if ($validator3->fails()) {
    echo "Validation failed (expected for role):\n";
    print_r($validator3->getErrors());
} else {
    echo "✗ Role validation should have failed!\n";
}

// Тест 4: Проверка numeric
echo "\n4. Testing numeric validation:\n";
$data4 = [
    'age' => '25',
    'price' => 'invalid_price'
];

$validator4 = new Validator($data4);
$validator4
    ->numeric('age')
    ->numeric('price');

if ($validator4->fails()) {
    echo "Validation failed (expected for price):\n";
    print_r($validator4->getErrors());
} else {
    echo "✗ Price validation should have failed!\n";
}

echo "\nValidator tests completed!\n"; 