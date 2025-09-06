# Базовый образ PHP 8.3 CLI
FROM php:8.3-cli

# Устанавливаем системные пакеты и PHP-расширения
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring bcmath gd

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Устанавливаем Node.js 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Создаем системного пользователя `app` с UID 1000
RUN useradd -G www-data,root -u 1000 -d /home/app app

# Создаем системного пользователя `sail` с UID 1000 (чтобы можно было запускать команды в контейнере)
RUN useradd -G www-data,root -u 1000 -o -d /home/app sail

# Создаем домашнюю директорию для пользователя `app`
RUN mkdir -p /home/app/.composer && \
    chown -R app:app /home/app

# Рабочая директория
WORKDIR /app

# Копируем проект
COPY . .

# Устанавливаем зависимости
RUN composer install --optimize-autoloader \
    && npm install \
    && npm run build || true

# Добавляем `sail`
RUN composer require laravel/sail --dev

# Права на storage и bootstrap/cache
RUN chown -R app:app /app/storage /app/bootstrap/cache

# Запускаем встроенный сервер
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

EXPOSE 8000
