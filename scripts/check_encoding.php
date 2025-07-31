<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Загружаем переменные окружения
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: 3306;
    $database = getenv('DB_DATABASE') ?: 'fcker_api';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "=== Проверка кодировки базы данных ===\n\n";
    
    // Проверяем настройки сервера
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
    echo "Настройки character_set:\n";
    while ($row = $stmt->fetch()) {
        echo "  {$row['Variable_name']}: {$row['Value']}\n";
    }
    
    echo "\n";
    
    // Проверяем настройки collation
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'collation%'");
    echo "Настройки collation:\n";
    while ($row = $stmt->fetch()) {
        echo "  {$row['Variable_name']}: {$row['Value']}\n";
    }
    
    echo "\n";
    
    // Проверяем кодировку таблиц
    $stmt = $pdo->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$database}'");
    echo "Кодировка таблиц:\n";
    while ($row = $stmt->fetch()) {
        echo "  {$row['TABLE_NAME']}: {$row['TABLE_COLLATION']}\n";
    }
    
    echo "\n";
    
    // Тестируем вставку русского текста
    echo "Тестирование вставки русского текста:\n";
    $testText = "Первый пост - тест кодировки с русскими символами: ё, й, щ, ъ, ь, э, ю, я";
    
    // Создаем временную таблицу для теста
    $pdo->exec("CREATE TEMPORARY TABLE test_encoding (id INT AUTO_INCREMENT PRIMARY KEY, text_content TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $stmt = $pdo->prepare("INSERT INTO test_encoding (text_content) VALUES (?)");
    $stmt->execute([$testText]);
    
    $stmt = $pdo->query("SELECT * FROM test_encoding");
    $result = $stmt->fetch();
    
    echo "  Вставленный текст: {$result['text_content']}\n";
    echo "  Длина строки: " . strlen($result['text_content']) . " байт\n";
    echo "  Длина в символах: " . mb_strlen($result['text_content'], 'UTF-8') . " символов\n";
    
    echo "\n✅ Кодировка настроена правильно!\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
} 