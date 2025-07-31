# Инструкция по настройке FckerAPI

## Требования

- PHP 8.2+
- MySQL 8.0+
- Composer (для локальной установки)
- Docker & Docker Compose (для контейнеризации)

## Варианты установки

### Вариант 1: Docker (Рекомендуется)

#### 1. Установка Docker
```bash
# macOS
brew install --cask docker

# Ubuntu/Debian
sudo apt update
sudo apt install docker.io docker-compose

# Windows
# Скачайте Docker Desktop с официального сайта
```

#### 2. Запуск приложения
```bash
# Клонируйте репозиторий
git clone <repository-url>
cd fckerAPI

# Запустите все сервисы
docker-compose up -d

# Проверьте статус
docker-compose ps
```

#### 3. Доступ к приложению
- **API**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Пользователь: `fcker_user`
  - Пароль: `fcker_password`

#### 4. Полезные команды Docker
```bash
# Просмотр логов
docker-compose logs -f

# Остановка сервисов
docker-compose down

# Пересборка образов
docker-compose up -d --build

# Очистка данных
docker-compose down -v
```

### Вариант 2: Локальная установка

#### 1. Установка Composer

**macOS:**
```bash
brew install composer
```

**Ubuntu/Debian:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Windows:**
```bash
# Скачайте Composer-Setup.exe с https://getcomposer.org/
```

#### 2. Установка зависимостей
```bash
# Клонируйте репозиторий
git clone <repository-url>
cd fckerAPI

# Установите зависимости для продакшена
make setup

# Или для разработки
make setup-dev
```

#### 3. Настройка базы данных
```bash
# Создайте базу данных
mysql -u root -p -e "CREATE DATABASE fcker_api;"

# Импортируйте схему
mysql -u root -p fcker_api < database/schema.sql
```

#### 4. Настройка конфигурации
```bash
# Скопируйте файл конфигурации
cp env.example .env

# Отредактируйте .env файл
nano .env
```

Пример `.env` файла:
```env
# Application
APP_NAME=FckerAPI
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=fcker_api
DB_USERNAME=root
DB_PASSWORD=your_password

# JWT
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_ACCESS_EXPIRY=3600
JWT_REFRESH_EXPIRY=86400
JWT_ISSUER=fcker-api
JWT_AUDIENCE=fcker-clients

# Timezone
TIMEZONE=Europe/Moscow
```

#### 5. Настройка веб-сервера

**Apache (.htaccess уже настроен):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/fckerAPI;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Встроенный сервер PHP (для разработки):**
```bash
php -S localhost:8000
```

## Команды Make

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

## Тестирование API

### 1. Проверка работоспособности
```bash
# Проверьте, что API отвечает
curl http://localhost:8000/

# Ожидаемый ответ:
# {"success":true,"message":"FckerAPI is running","data":{"version":"1.0.0"}}
```

### 2. Регистрация пользователя
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 3. Вход в систему
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 4. Создание поста
```bash
curl -X POST http://localhost:8000/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "title": "Мой первый пост",
    "content": "Содержимое поста"
  }'
```

## Устранение неполадок

### Проблема: Composer не найден
```bash
# Установите Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Проблема: Ошибка подключения к БД
```bash
# Проверьте настройки в .env файле
# Убедитесь, что MySQL запущен
sudo systemctl start mysql

# Проверьте подключение
mysql -u root -p -e "SHOW DATABASES;"
```

### Проблема: Права доступа
```bash
# Установите правильные права
chmod 755 .
chmod 644 *.php
chmod 644 .env
```

### Проблема: Docker не запускается
```bash
# Проверьте, что Docker запущен
docker --version
docker-compose --version

# Перезапустите Docker
sudo systemctl restart docker
```

## Безопасность

### Продакшен настройки
```env
APP_ENV=production
APP_DEBUG=false
JWT_SECRET=very-long-random-secret-key
```

### Рекомендации
1. Измените JWT_SECRET на уникальный ключ
2. Используйте HTTPS в продакшене
3. Настройте firewall
4. Регулярно обновляйте зависимости
5. Используйте сильные пароли для БД

## Поддержка

Если у вас возникли проблемы:

1. Проверьте логи: `docker-compose logs` или `tail -f /var/log/nginx/error.log`
2. Убедитесь, что все требования выполнены
3. Проверьте настройки в `.env` файле
4. Создайте Issue в репозитории с подробным описанием проблемы 