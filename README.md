# Effective Mobile ToDo

## 📌 Описание
Effmo TODO — это учебное ToDo-приложение, разработанное на **Laravel 12**, которое демонстрирует работу с задачами, пользователями, JWT-авторизацией и базовыми CRUD-операциями через REST API.  
Проект разворачивается с помощью **Docker Compose**, что позволяет быстро поднять окружение для разработки или тестирования.

---

## 🚀 Развёртывание через Docker Compose

### 1. Клонируйте репозиторий
```bash
git clone https://github.com/nikita1007/effective-mobile-test
```

### 2. Перейдите в директорию с проектом
```bash
cd effective-mobile-test
```

### 3. Копируем и настраиваем .env
```bash
cp .env.example .env
```

Устанавливаем ключи `APP_KEY` и `APP_JWT_KEY`. <br>
Указываем настройки для БД:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=root
DB_ROOT_PASSWORD=root
```

### 4. Запустите инициализацию и сборку проекта через **Docker Compose**
```bash
docker compose up -d --build
```

### 5. Копируем vendor из контейнера (через sudo)
```bash
sudo docker cp effmo_laravel_app:/app/vendor ./
```

### 6. Запускаем миграции
```bash
# Я делаю через sail, поэтому указываю alias
alias sail='./vendor/bin/sail'
sail artisan migrate --seed
```

Теперь после проделанных действий, можно перейти по ссылке <a href="http://localhost:8081/api/v1/documentation">swagger documentation</a>.

---

## 🛠️ Технологии

- PHP 8.3
- Laravel 12
- Docker + Docker Compose
- MySQL
