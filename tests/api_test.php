<?php

/**
 * Простой тест API
 * Запуск: php tests/api_test.php
 */

class ApiTest
{
    private string $baseUrl;
    private ?string $accessToken = null;
    private ?string $refreshToken = null;

    public function __construct(string $baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = $baseUrl;
    }

    public function run(): void
    {
        echo "🧪 Запуск тестов API...\n\n";

        $this->testIndex();
        $this->testRegister();
        $this->testLogin();
        $this->testMe();
        $this->testCreatePost();
        $this->testGetPosts();
        $this->testGetPost();
        $this->testUpdatePost();
        $this->testDeletePost();
        $this->testLogout();

        echo "\n✅ Все тесты завершены!\n";
    }

    private function makeRequest(string $endpoint, array $options = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $method = $options['method'] ?? 'GET';
        $data = $options['data'] ?? null;
        $headers = $options['headers'] ?? [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json'
        ], $headers));

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }

    private function testIndex(): void
    {
        echo "📋 Тест главной страницы... ";
        $response = $this->makeRequest('/');
        
        if ($response['code'] === 200 && isset($response['data']['success'])) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testRegister(): void
    {
        echo "📝 Тест регистрации... ";
        $response = $this->makeRequest('/auth/register', [
            'method' => 'POST',
            'data' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ]
        ]);

        if ($response['code'] === 201 && isset($response['data']['data']['tokens'])) {
            $this->accessToken = $response['data']['data']['tokens']['access_token'];
            $this->refreshToken = $response['data']['data']['tokens']['refresh_token'];
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testLogin(): void
    {
        echo "🔐 Тест входа... ";
        $response = $this->makeRequest('/auth/login', [
            'method' => 'POST',
            'data' => [
                'email' => 'test@example.com',
                'password' => 'password123'
            ]
        ]);

        if ($response['code'] === 200 && isset($response['data']['data']['tokens'])) {
            $this->accessToken = $response['data']['data']['tokens']['access_token'];
            $this->refreshToken = $response['data']['data']['tokens']['refresh_token'];
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testMe(): void
    {
        echo "👤 Тест получения профиля... ";
        $response = $this->makeRequest('/auth/me', [
            'headers' => [
                'Authorization: Bearer ' . $this->accessToken
            ]
        ]);

        if ($response['code'] === 200 && isset($response['data']['data']['user'])) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testCreatePost(): void
    {
        echo "📝 Тест создания поста... ";
        $response = $this->makeRequest('/posts', [
            'method' => 'POST',
            'headers' => [
                'Authorization: Bearer ' . $this->accessToken
            ],
            'data' => [
                'title' => 'Тестовый пост',
                'content' => 'Это тестовое содержимое поста для проверки API.'
            ]
        ]);

        if ($response['code'] === 201 && isset($response['data']['data']['post'])) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testGetPosts(): void
    {
        echo "📋 Тест получения постов... ";
        $response = $this->makeRequest('/posts');

        if ($response['code'] === 200 && isset($response['data']['data']['posts'])) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testGetPost(): void
    {
        echo "📄 Тест получения поста... ";
        $response = $this->makeRequest('/posts/1');

        if ($response['code'] === 200 && isset($response['data']['data']['post'])) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testUpdatePost(): void
    {
        echo "✏️ Тест обновления поста... ";
        $response = $this->makeRequest('/posts/1', [
            'method' => 'PUT',
            'headers' => [
                'Authorization: Bearer ' . $this->accessToken
            ],
            'data' => [
                'title' => 'Обновленный пост',
                'content' => 'Это обновленное содержимое поста.'
            ]
        ]);

        if ($response['code'] === 200 && isset($response['data']['data']['post'])) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testDeletePost(): void
    {
        echo "🗑️ Тест удаления поста... ";
        $response = $this->makeRequest('/posts/1', [
            'method' => 'DELETE',
            'headers' => [
                'Authorization: Bearer ' . $this->accessToken
            ]
        ]);

        if ($response['code'] === 200) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }

    private function testLogout(): void
    {
        echo "🚪 Тест выхода... ";
        $response = $this->makeRequest('/auth/logout', [
            'method' => 'POST',
            'headers' => [
                'Authorization: Bearer ' . $this->accessToken
            ]
        ]);

        if ($response['code'] === 200) {
            echo "✅ Успешно\n";
        } else {
            echo "❌ Ошибка: " . $response['code'] . "\n";
        }
    }
}

// Запуск тестов
if (php_sapi_name() === 'cli') {
    $test = new ApiTest();
    $test->run();
} 