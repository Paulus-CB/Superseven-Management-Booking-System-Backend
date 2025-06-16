# 🚀 Laravel Installation Guide

Follow these steps to set up the Laravel project locally.

## 1. Clone the Repository
```bash
git clone https://github.com/Paulus-CB/Superseven-Backend.git
cd Superseven-Backend
```

## 2. Install Dependencies
```bash
composer install
```

## 3. Copy .env Example
```bash
cp .env.example .env
```

## 4. Generate Application Key
```bash
php artisan key:generate
```

## 5. Run Database Migrations
```bash
php artisan migrate --seed
```

## 6. Start the Development Server
```bash
php artisan serve
```

Now, you can access the application at [http://localhost:8000](http://localhost:8000).

## Optimizations

To optimize your application, you can run the following commands:
```bash
php artisan optimize:clear
```

## To run the Project
```bash
php artisan serve
php artisan queue:work
npm run dev
```