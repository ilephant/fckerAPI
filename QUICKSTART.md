# 🚀 Быстрый старт FckerAPI

## Вариант 1: Docker (Рекомендуется)

```bash
# 1. Клонируйте репозиторий
git clone <repository-url>
cd fckerAPI

# 2. Запустите приложение
docker-compose up -d

# 3. Готово! API доступен по адресу:
# http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

## Вариант 2: Локальная установка

```bash
# 1. Клонируйте репозиторий
git clone <repository-url>
cd fckerAPI

# 2. Установите Composer (если не установлен)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 3. Настройте проект
make setup-dev

# 4. Создайте .env файл
cp env.example .env
# Отредактируйте .env с вашими настройками БД

# 5. Создайте базу данных
mysql -u root -p -e "CREATE DATABASE fcker_api;"
mysql -u root -p fcker_api < database/schema.sql

# 6. Запустите сервер
php -S localhost:8000

# 7. Готово! API доступен по адресу:
# http://localhost:8000
```

## 🧪 Тестирование API

```bash
# Проверка работоспособности
curl http://localhost:8000/

# Регистрация пользователя
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# Вход в систему
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Создание поста (замените YOUR_TOKEN на полученный токен)
curl -X POST http://localhost:8000/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"title":"Мой пост","content":"Содержимое"}'
```

## 📁 Основные файлы

- `index.php` - точка входа
- `composer.json` - зависимости
- `.env` - конфигурация
- `docker-compose.yml` - Docker конфигурация
- `Makefile` - команды для разработки

## 🛠 Полезные команды

```bash
make help          # Справка
make setup         # Настройка для продакшена
make setup-dev     # Настройка для разработки
make test          # Запуск тестов
make optimize      # Оптимизация автозагрузчика
```

## 📚 Документация

- [Полная документация](README.md)
- [Инструкция по настройке](SETUP.md)
- [Развертывание в продакшене](DEPLOYMENT.md)

## 🆘 Поддержка

Если что-то не работает:

1. Проверьте логи: `docker-compose logs` или `tail -f error.log`
2. Убедитесь, что все зависимости установлены
3. Проверьте настройки в `.env` файле
4. Создайте Issue в репозитории

## 🎯 Что дальше?

1. Изучите [API endpoints](README.md#api-endpoints)
2. Настройте интеграцию с фронтендом
3. Добавьте новые контроллеры и модели
4. Настройте CI/CD для автоматического развертывания 