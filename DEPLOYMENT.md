# Инструкция по развертыванию в продакшене

## Подготовка к продакшену

### 1. Настройка сервера

#### Требования к серверу
- Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- Минимум 2GB RAM
- 20GB свободного места
- PHP 8.2+
- MySQL 8.0+ или MariaDB 10.5+
- Nginx или Apache
- SSL сертификат

#### Установка зависимостей
```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка PHP и расширений
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring php8.2-xml php8.2-zip

# Установка MySQL
sudo apt install -y mysql-server

# Установка Nginx
sudo apt install -y nginx

# Установка Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Настройка базы данных

```bash
# Безопасная настройка MySQL
sudo mysql_secure_installation

# Создание базы данных и пользователя
sudo mysql -u root -p
```

```sql
CREATE DATABASE fcker_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fcker_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON fcker_api.* TO 'fcker_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Развертывание приложения

#### Клонирование и настройка
```bash
# Создание директории для приложения
sudo mkdir -p /var/www/fcker-api
sudo chown $USER:$USER /var/www/fcker-api

# Клонирование репозитория
git clone <repository-url> /var/www/fcker-api
cd /var/www/fcker-api

# Установка зависимостей для продакшена
composer install --no-dev --optimize-autoloader

# Настройка прав доступа
sudo chown -R www-data:www-data /var/www/fcker-api
sudo chmod -R 755 /var/www/fcker-api
sudo chmod 644 /var/www/fcker-api/.env
```

#### Настройка конфигурации
```bash
# Создание .env файла
cp env.example .env
nano .env
```

Продакшен `.env`:
```env
# Application
APP_NAME=FckerAPI
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=fcker_api
DB_USERNAME=fcker_user
DB_PASSWORD=strong_password_here

# JWT
JWT_SECRET=very-long-random-secret-key-at-least-32-characters
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

### 4. Настройка Nginx

```bash
# Создание конфигурации сайта
sudo nano /etc/nginx/sites-available/fcker-api
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    # SSL конфигурация
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Корневая директория
    root /var/www/fcker-api;
    index index.php;
    
    # Логи
    access_log /var/log/nginx/fcker-api.access.log;
    error_log /var/log/nginx/fcker-api.error.log;
    
    # Безопасность
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Скрываем версию nginx
    server_tokens off;
    
    # Обработка PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Маршрутизация API
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Запрещаем доступ к скрытым файлам
    location ~ /\. {
        deny all;
    }
    
    # Запрещаем доступ к composer файлам
    location ~ composer\.(json|lock)$ {
        deny all;
    }
    
    # Запрещаем доступ к .env файлам
    location ~ \.env {
        deny all;
    }
    
    # Статические файлы
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Gzip сжатие
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
}
```

```bash
# Активация сайта
sudo ln -s /etc/nginx/sites-available/fcker-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. SSL сертификат (Let's Encrypt)

```bash
# Установка Certbot
sudo apt install -y certbot python3-certbot-nginx

# Получение SSL сертификата
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Автоматическое обновление
sudo crontab -e
# Добавьте строку:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. Настройка PHP-FPM

```bash
# Оптимизация PHP-FPM
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
; Основные настройки
memory_limit = 256M
max_execution_time = 30
upload_max_filesize = 10M
post_max_size = 10M

; Ошибки (продакшен)
display_errors = Off
log_errors = On
error_log = /var/log/php8.2-fpm.log

; Оптимизация
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

```bash
# Перезапуск PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 7. Настройка firewall

```bash
# Установка UFW
sudo apt install -y ufw

# Настройка правил
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443

# Активация firewall
sudo ufw enable
```

### 8. Мониторинг и логирование

#### Настройка logrotate
```bash
sudo nano /etc/logrotate.d/fcker-api
```

```
/var/log/nginx/fcker-api.*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}
```

#### Настройка мониторинга
```bash
# Установка htop для мониторинга
sudo apt install -y htop

# Создание скрипта мониторинга
sudo nano /usr/local/bin/monitor-api.sh
```

```bash
#!/bin/bash
# Мониторинг API

# Проверка доступности API
if curl -f -s https://your-domain.com/ > /dev/null; then
    echo "$(date): API is running"
else
    echo "$(date): API is down!" | mail -s "API Alert" admin@your-domain.com
fi

# Проверка места на диске
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): Disk usage is ${DISK_USAGE}%" | mail -s "Disk Alert" admin@your-domain.com
fi
```

```bash
# Делаем скрипт исполняемым
sudo chmod +x /usr/local/bin/monitor-api.sh

# Добавляем в cron
sudo crontab -e
# Добавьте строку:
# */5 * * * * /usr/local/bin/monitor-api.sh
```

### 9. Резервное копирование

```bash
# Создание скрипта резервного копирования
sudo nano /usr/local/bin/backup-api.sh
```

```bash
#!/bin/bash
# Резервное копирование API

BACKUP_DIR="/var/backups/fcker-api"
DATE=$(date +%Y%m%d_%H%M%S)

# Создание директории для бэкапов
mkdir -p $BACKUP_DIR

# Бэкап базы данных
mysqldump -u fcker_user -p'strong_password_here' fcker_api > $BACKUP_DIR/db_$DATE.sql

# Бэкап файлов приложения
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/fcker-api

# Удаление старых бэкапов (старше 30 дней)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# Делаем скрипт исполняемым
sudo chmod +x /usr/local/bin/backup-api.sh

# Добавляем в cron (ежедневно в 2:00)
sudo crontab -e
# Добавьте строку:
# 0 2 * * * /usr/local/bin/backup-api.sh
```

### 10. Обновление приложения

```bash
# Создание скрипта обновления
sudo nano /usr/local/bin/update-api.sh
```

```bash
#!/bin/bash
# Обновление API

cd /var/www/fcker-api

# Создание бэкапа перед обновлением
/usr/local/bin/backup-api.sh

# Получение обновлений
git pull origin main

# Обновление зависимостей
composer install --no-dev --optimize-autoloader

# Очистка кэша
composer dump-autoload --optimize

# Перезапуск сервисов
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "API updated successfully"
```

```bash
# Делаем скрипт исполняемым
sudo chmod +x /usr/local/bin/update-api.sh
```

## Проверка развертывания

### Тестирование API
```bash
# Проверка доступности
curl -I https://your-domain.com/

# Тестирование endpoints
curl https://your-domain.com/
curl -X POST https://your-domain.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123"}'
```

### Проверка производительности
```bash
# Тест нагрузки с Apache Bench
ab -n 1000 -c 10 https://your-domain.com/

# Мониторинг ресурсов
htop
```

## Безопасность

### Дополнительные меры безопасности
1. **Регулярные обновления системы**
2. **Мониторинг логов на подозрительную активность**
3. **Настройка fail2ban для защиты от брутфорса**
4. **Регулярное резервное копирование**
5. **Мониторинг SSL сертификатов**

### Настройка fail2ban
```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

## Поддержка

### Полезные команды для диагностики
```bash
# Проверка статуса сервисов
sudo systemctl status nginx php8.2-fpm mysql

# Просмотр логов
sudo tail -f /var/log/nginx/fcker-api.error.log
sudo tail -f /var/log/php8.2-fpm.log

# Проверка конфигурации
sudo nginx -t
sudo php-fpm8.2 -t
```

### Контакты для поддержки
- Email: admin@your-domain.com
- Документация: https://your-domain.com/docs
- Мониторинг: https://your-domain.com/status 