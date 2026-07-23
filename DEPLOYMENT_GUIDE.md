# BridgeEdu Rwanda Deployment Guide

This guide helps you take the project live step by step.

## 1. Project overview
This app is a Laravel backend with a Vite/React frontend.

- Backend: Laravel API
- Frontend: Vite + React
- Database: MySQL-compatible database recommended for production
- Hosting plan: Render for Laravel API, Vercel for frontend

---

## 2. Prerequisites
Make sure you have:
- GitHub account
- Render account
- Vercel account (optional for frontend)
- A MySQL-compatible database provider such as:
  - Railway
  - Aiven
  - TiDB Cloud
- Docker installed locally (optional but recommended)

---

## 3. Prepare the project locally
Run these commands locally:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

If you want to test locally with Docker, build and run:

```bash
docker compose up --build
```

If you do not yet have a Docker setup, create one before deployment.

---

## 4. Docker setup
Create a Dockerfile for the Laravel app.

Example Dockerfile:

```dockerfile
FROM php:8.3-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath

COPY . /var/www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

Example docker-compose.yml:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    env_file:
      - .env
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3306:3306"
```

Important:
- Laravel does not run like a pure React app.
- The backend must be served by PHP, not only by Vite.
- Docker should run the Laravel app, not just the frontend assets.

---

## 5. Deploy the Laravel backend to Render
### Recommended approach
- Render hosts the Laravel API
- Vercel hosts the React/Vite frontend

### Render setup
1. Push the project to GitHub.
2. In Render, create a New Web Service.
3. Connect your GitHub repository.
4. Choose the service type: Web Service.
5. Set the build command:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

6. Set the start command:

```bash
php artisan serve --host 0.0.0.0 --port $PORT
```

7. Add environment variables:
- APP_ENV=production
- APP_DEBUG=false
- APP_KEY=generate one automatically
- APP_URL=https://your-app-name.onrender.com
- DB_CONNECTION=mysql
- DB_HOST=your-db-host
- DB_PORT=3306
- DB_DATABASE=your-db-name
- DB_USERNAME=your-db-user
- DB_PASSWORD=your-db-password

8. Connect your database.
9. Deploy.

---

## 6. Deploy the frontend to Vercel
If you want the React/Vite frontend live:
1. Push the frontend repo or the frontend folder to GitHub.
2. In Vercel, import the project.
3. Set the build command:

```bash
npm install
npm run build
```

4. Set the output directory:

```bash
dist
```

5. Add environment variables if needed:
- VITE_API_URL=https://your-laravel-api.onrender.com

---

## 7. Important production notes
### Laravel-specific settings
Make sure your app uses:
- A real database, not SQLite in production
- Proper APP_URL
- A generated APP_KEY
- Cache commands during deployment

### CORS
If your frontend and API are on different domains, update CORS settings to allow your frontend origin.

### Storage
If you upload files, use a cloud storage provider such as Cloudinary or S3.

---

## 8. Common problems
### Problem: app shows 500 error
Check:
- APP_KEY is set
- database connection is correct
- migrations ran successfully

### Problem: frontend cannot reach API
Check:
- API base URL is correct
- CORS allows the frontend domain

### Problem: Vite build fails
Check:
- Node dependencies are installed
- package.json is correct
- vite.config.js is correct

---

## 9. Final checklist
Before going live, confirm:
- [ ] GitHub repo is updated
- [ ] Render service is connected
- [ ] Database is connected
- [ ] APP_KEY is generated
- [ ] Migrations ran
- [ ] Frontend points to the live API URL
- [ ] CORS is configured

---

## 10. Recommended hosting split
- Laravel API: Render
- React/Vite frontend: Vercel
- Database: Railway or other MySQL provider
- File uploads: Cloudinary

If you want, I can next create a real Dockerfile and docker-compose.yml for this repo so you can test everything locally before deploying.
