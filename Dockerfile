# Используем официальный PHP образ
FROM php:8.2-fpm-alpine

# Устанавливаем системные зависимости
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Устанавливаем PHP расширения
RUN docker-php-ext-install pdo pdo_mysql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем composer файлы
COPY composer.json composer.lock* ./

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Копируем исходный код
COPY . .

# Создаем пользователя для безопасности
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Создаем необходимые директории для supervisord
RUN mkdir -p /var/log/supervisor /var/run/supervisor

# Устанавливаем права доступа
RUN chown -R www:www /var/www/html && \
    chown -R root:root /var/log/supervisor /var/run/supervisor

# Копируем конфигурацию nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Копируем конфигурацию supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Открываем порт
EXPOSE 80

# Запускаем supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
