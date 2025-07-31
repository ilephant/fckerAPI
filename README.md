# FckerAPI

Современный PHP 8.2+ REST API фреймворк с поддержкой Composer, Docker и PSR-4 автозагрузки.

## 🚀 Возможности

- ✅ **Composer** - современное управление зависимостями
- ✅ **PSR-4 автозагрузка** - стандартизированная загрузка классов
- ✅ **Docker** - контейнеризация для продакшена
- ✅ **JWT авторизация** с refresh токенами
- ✅ **REST API** архитектура
- ✅ **MySQL 8** поддержка
- ✅ **CORS** поддержка
- ✅ **Валидация данных**
- ✅ **Пагинация**
- ✅ **Безопасность** (хеширование паролей, SQL инъекции защита)
- ✅ **Конфигурация через .env**

## 📦 Установка

### Вариант 1: Локальная установка

1. **Клонируйте репозиторий**
```bash
git clone <repository-url>
cd fckerAPI
```

2. **Установите зависимости**
```bash
# Для продакшена
make setup

# Для разработки
make setup-dev
```

3. **Настройте .env файл**
```bash
cp env.example .env
# Отредактируйте .env файл с вашими настройками
```

4. **Настройте базу данных**
```sql
CREATE DATABASE fcker_api;
mysql -u root -p fcker_api < database/schema.sql
```

### Вариант 2: Docker (Рекомендуется)

1. **Клонируйте репозиторий**
```bash
git clone <repository-url>
cd fckerAPI
```

2. **Запустите с Docker Compose**
```bash
docker-compose up -d
```

3. **Доступ к приложению**
- API: http://localhost:8080
- phpMyAdmin: http://localhost:8081

## 🛠 Команды Make

```bash
make help          # Показать справку
make setup         # Настройка для продакшена
make setup-dev     # Настройка для разработки
make install       # Установить зависимости
make update        # Обновить зависимости
make test          # Запустить тесты
make optimize      # Оптимизировать автозагрузчик
make check         # Проверить код на ошибки
make clean         # Очистить кэш
```

## 🔧 Конфигурация

### Переменные окружения (.env)

```env
# Application
APP_NAME=FckerAPI
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=fcker_api
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_ACCESS_EXPIRY=3600
JWT_REFRESH_EXPIRY=86400
JWT_ISSUER=fcker-api
JWT_AUDIENCE=fcker-clients

# Timezone
TIMEZONE=Europe/Moscow

# Pagination
DEFAULT_PAGE_SIZE=10
MAX_PAGE_SIZE=100
```

## 📁 Структура проекта

```
fckerAPI/
├── application/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── IndexController.php
│   │   └── PostsController.php
│   └── Models/
│       ├── PostModel.php
│       └── UserModel.php
├── framework/
│   ├── Core/
│   │   ├── Config.php
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Router.php
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   └── Services/
│       └── JwtService.php
├── database/
│   └── schema.sql
├── docker/
│   ├── nginx.conf
│   └── supervisord.conf
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── Makefile
└── index.php
```

## 🔌 API Endpoints

### Аутентификация

#### Регистрация
```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
}
```

#### Вход
```http
POST /auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Обновление токенов
```http
POST /auth/refresh
Content-Type: application/json

{
    "refresh_token": "your_refresh_token"
}
```

#### Получение текущего пользователя
```http
GET /auth/me
Authorization: Bearer your_access_token
```

#### Выход
```http
POST /auth/logout
Authorization: Bearer your_access_token
```

### Посты

#### Получение всех постов
```http
GET /posts?page=1&limit=10
```

#### Получение поста по ID
```http
GET /posts/1
```

#### Создание поста
```http
POST /posts
Authorization: Bearer your_access_token
Content-Type: application/json

{
    "title": "Новый пост",
    "content": "Содержимое поста"
}
```

#### Обновление поста
```http
PUT /posts/1
Authorization: Bearer your_access_token
Content-Type: application/json

{
    "title": "Обновленный заголовок",
    "content": "Обновленное содержимое"
}
```

#### Удаление поста
```http
DELETE /posts/1
Authorization: Bearer your_access_token
```

#### Мои посты
```http
GET /posts/my
Authorization: Bearer your_access_token
```

## 🐳 Docker

### Сборка образа
```bash
docker build -t fcker-api .
```

### Запуск с Docker Compose
```bash
# Запуск всех сервисов
docker-compose up -d

# Просмотр логов
docker-compose logs -f

# Остановка
docker-compose down
```

### Переменные окружения для Docker
```bash
# В docker-compose.yml или через .env файл
APP_ENV=production
DB_HOST=db
DB_PORT=3306
DB_DATABASE=fcker_api
DB_USERNAME=fcker_user
DB_PASSWORD=fcker_password
```

## 🧪 Тестирование

```bash
# Запуск тестов
make test

# Или напрямую
composer test
```

## 🔒 Безопасность

- Все пароли хешируются с помощью `password_hash()`
- Защита от SQL инъекций через PDO prepared statements
- JWT токены с настраиваемым временем жизни
- CORS заголовки для безопасности
- Валидация всех входящих данных

## 📈 Производительность

- Оптимизированный автозагрузчик Composer
- Кэширование путей к классам
- Gzip сжатие для статических файлов
- Оптимизированные SQL запросы

## 🤝 Вклад в проект

1. Fork репозитория
2. Создайте ветку для новой функции
3. Внесите изменения
4. Добавьте тесты
5. Создайте Pull Request

## 📄 Лицензия

MIT License - см. файл [LICENSE](LICENSE)

## 🆘 Поддержка

Если у вас есть вопросы или проблемы:

1. Проверьте [Issues](https://github.com/your-repo/issues)
2. Создайте новый Issue с подробным описанием
3. Убедитесь, что проблема воспроизводится в последней версии
