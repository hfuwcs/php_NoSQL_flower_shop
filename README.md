# 🌸 Flower Shop - Laravel NoSQL Project

A modern e-commerce flower shop built with Laravel 12, MongoDB, and Redis.

**Dự án website bán hoa hiện đại được xây dựng bằng Laravel 12, MongoDB và Redis.**

---

## 📋 Table of Contents / Mục lục

- [English Documentation](#english)
  - [Features](#features)
  - [Tech Stack](#tech-stack)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Running the Application](#running-the-application)
  - [Project Structure](#project-structure)
  - [Additional Documentation](#additional-documentation)
- [Tài liệu Tiếng Việt](#tiếng-việt)
  - [Tính năng](#tính-năng)
  - [Công nghệ sử dụng](#công-nghệ-sử-dụng)
  - [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
  - [Cài đặt](#cài-đặt)
  - [Chạy ứng dụng](#chạy-ứng-dụng)
  - [Cấu trúc dự án](#cấu-trúc-dự-án)
  - [Tài liệu bổ sung](#tài-liệu-bổ-sung)

---

<a name="english"></a>
# 🇬🇧 English Documentation

<a name="features"></a>
## ✨ Features

- 🛒 **Shopping Cart** - Add/remove products with real-time updates
- 📦 **Order Management** - Place and track orders
- ⭐ **Product Reviews** - Rate and review products with voting system
- 🏆 **Leaderboard** - Top products and users ranking
- 🔐 **Authentication** - Secure user registration and login (Laravel Breeze)
- 📊 **Redis Caching** - Fast data access with Redis integration
- 🗄️ **MongoDB Database** - NoSQL database for flexible data storage
- ⚡ **Queue Jobs** - Background processing for product statistics updates

<a name="tech-stack"></a>
## 🛠️ Tech Stack

- **Backend Framework:** Laravel 12
- **Frontend:** Blade Templates, Alpine.js, Tailwind CSS
- **Database:** MongoDB (via mongodb/laravel-mongodb)
- **Cache/Session:** Redis (via predis/predis)
- **Authentication:** Laravel Breeze
- **Build Tool:** Vite
- **Queue:** Database driver
- **PHP Version:** ^8.2

<a name="prerequisites"></a>
## 📦 Prerequisites

Before you begin, ensure you have the following installed on your system:

1. **PHP 8.2 or higher**
   - Download from: https://www.php.net/downloads
   - Required extensions: `mongodb`, `redis`, `pdo_sqlite`

2. **Composer** (PHP Package Manager)
   - Download from: https://getcomposer.org/

3. **Node.js & NPM** (v18 or higher)
   - Download from: https://nodejs.org/

4. **MongoDB** (v5.0 or higher)
   - Download from: https://www.mongodb.com/try/download/community
   - Make sure MongoDB service is running

5. **Redis** (v6.0 or higher)
   - Windows: https://github.com/microsoftarchive/redis/releases
   - Linux/Mac: https://redis.io/download
   - Make sure Redis service is running

<a name="installation"></a>
## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/hfuwcs/php_NoSQL_flower_shop.git
cd flower-shop
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

1. Copy the example environment file:
```bash
# Windows PowerShell
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

2. Generate application key:
```bash
php artisan key:generate
```

3. Configure your `.env` file with your database and Redis settings:

```env
APP_NAME="Flower Shop"
APP_URL=http://localhost:8000

# MongoDB Configuration
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=flower_shop
DB_USERNAME=
DB_PASSWORD=

# Redis Configuration
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=database

# Queue
QUEUE_CONNECTION=database
```

### Step 5: Database Setup

1. Make sure MongoDB is running
2. Run migrations:
```bash
php artisan migrate
```

3. (Optional) Seed the database with sample data:
```bash
php artisan db:seed
```

### Step 6: Build Frontend Assets

```bash
npm run build
```

<a name="running-the-application"></a>
## 🎯 Running the Application

### Option 1: Using Composer Scripts (Recommended)

Run all services concurrently (server, queue worker, logs, and Vite):

```bash
composer dev
```

This command will start:
- Laravel development server (http://localhost:8000)
- Queue worker for background jobs
- Laravel Pail for real-time logs
- Vite development server for hot module replacement

### Option 2: Manual Setup (Separate Terminals)

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 3 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 4 - (Optional) Logs:**
```bash
php artisan pail
```

### Accessing the Application

Open your browser and navigate to:
- **Application:** http://localhost:8000
- **Register/Login:** http://localhost:8000/register

<a name="project-structure"></a>
## 📁 Project Structure

```
flower-shop/
├── app/
│   ├── Models/          # MongoDB Models (Product, Order, Review, Cart, User)
│   ├── Services/        # Business Logic (CartService)
│   ├── Jobs/            # Queue Jobs (UpdateProductStatsJob)
│   └── Http/Controllers/
├── database/
│   ├── factories/       # Model Factories
│   ├── migrations/      # Database Migrations
│   └── seeders/         # Database Seeders
├── resources/
│   ├── views/           # Blade Templates
│   ├── js/              # JavaScript Files
│   └── css/             # Stylesheets
├── routes/
│   ├── web.php          # Web Routes
│   └── auth.php         # Authentication Routes
└── config/
    ├── database.php     # Database Configuration
    └── cache.php        # Cache Configuration
```

<a name="additional-documentation"></a>
## 📚 Additional Documentation

- [Redis Prefix Explanation](REDIS_PREFIX_EXPLAINED.md) - Detailed guide about Redis prefix handling in this project

## 🧪 Testing

Run the test suite:
```bash
composer test
```

Or:
```bash
php artisan test
```

## 🔧 Troubleshooting

**MongoDB Connection Error:**
- Ensure MongoDB service is running
- Check connection details in `.env`

**Redis Connection Error:**
- Ensure Redis service is running
- Verify `REDIS_HOST` and `REDIS_PORT` in `.env`

**Queue Jobs Not Processing:**
- Make sure queue worker is running: `php artisan queue:work`
- Check `QUEUE_CONNECTION=database` in `.env`

---

<a name="tiếng-việt"></a>
# 🇻🇳 Tài liệu Tiếng Việt

<a name="tính-năng"></a>
## ✨ Tính năng

- 🛒 **Giỏ hàng** - Thêm/xóa sản phẩm với cập nhật thời gian thực
- 📦 **Quản lý đơn hàng** - Đặt hàng và theo dõi đơn hàng
- ⭐ **Đánh giá sản phẩm** - Đánh giá và bình luận sản phẩm với hệ thống vote
- 🏆 **Bảng xếp hạng** - Xếp hạng sản phẩm và người dùng hàng đầu
- 🔐 **Xác thực người dùng** - Đăng ký và đăng nhập an toàn (Laravel Breeze)
- 📊 **Redis Cache** - Truy xuất dữ liệu nhanh với Redis
- 🗄️ **MongoDB Database** - Cơ sở dữ liệu NoSQL linh hoạt
- ⚡ **Queue Jobs** - Xử lý nền cho cập nhật thống kê sản phẩm

<a name="công-nghệ-sử-dụng"></a>
## 🛠️ Công nghệ sử dụng

- **Backend Framework:** Laravel 12
- **Frontend:** Blade Templates, Alpine.js, Tailwind CSS
- **Database:** MongoDB (qua mongodb/laravel-mongodb)
- **Cache/Session:** Redis (qua predis/predis)
- **Authentication:** Laravel Breeze
- **Build Tool:** Vite
- **Queue:** Database driver
- **Phiên bản PHP:** ^8.2

<a name="yêu-cầu-hệ-thống"></a>
## 📦 Yêu cầu hệ thống

Trước khi bắt đầu, hãy đảm bảo bạn đã cài đặt các phần mềm sau:

1. **PHP 8.2 trở lên**
   - Tải từ: https://www.php.net/downloads
   - Extension cần thiết: `mongodb`, `redis`, `pdo_sqlite`

2. **Composer** (Trình quản lý package PHP)
   - Tải từ: https://getcomposer.org/

3. **Node.js & NPM** (v18 trở lên)
   - Tải từ: https://nodejs.org/

4. **MongoDB** (v5.0 trở lên)
   - Tải từ: https://www.mongodb.com/try/download/community
   - Đảm bảo dịch vụ MongoDB đang chạy

5. **Redis** (v6.0 trở lên)
   - Windows: https://github.com/microsoftarchive/redis/releases
   - Linux/Mac: https://redis.io/download
   - Đảm bảo dịch vụ Redis đang chạy

<a name="cài-đặt"></a>
## 🚀 Cài đặt

### Bước 1: Clone Repository

```bash
git clone https://github.com/hfuwcs/php_NoSQL_flower_shop.git
cd flower-shop
```

### Bước 2: Cài đặt PHP Dependencies

```bash
composer install
```

### Bước 3: Cài đặt Node Dependencies

```bash
npm install
```

### Bước 4: Cấu hình môi trường

1. Sao chép file môi trường mẫu:
```bash
# Windows PowerShell
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

2. Tạo application key:
```bash
php artisan key:generate
```

3. Cấu hình file `.env` với thông tin database và Redis:

```env
APP_NAME="Flower Shop"
APP_URL=http://localhost:8000

# Cấu hình MongoDB
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=flower_shop
DB_USERNAME=
DB_PASSWORD=

# Cấu hình Redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=database

# Queue
QUEUE_CONNECTION=database
```

### Bước 5: Thiết lập Database

1. Đảm bảo MongoDB đang chạy
2. Chạy migrations:
```bash
php artisan migrate
```

3. (Tùy chọn) Seed dữ liệu mẫu:
```bash
php artisan db:seed
```

### Bước 6: Build Frontend Assets

```bash
npm run build
```

<a name="chạy-ứng-dụng"></a>
## 🎯 Chạy ứng dụng

### Cách 1: Sử dụng Composer Scripts (Khuyến nghị)

Chạy tất cả các dịch vụ cùng lúc (server, queue worker, logs, và Vite):

```bash
composer dev
```

Lệnh này sẽ khởi động:
- Laravel development server (http://localhost:8000)
- Queue worker cho các background jobs
- Laravel Pail cho real-time logs
- Vite development server cho hot module replacement

### Cách 2: Chạy thủ công (Nhiều terminal riêng biệt)

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 3 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 4 - (Tùy chọn) Logs:**
```bash
php artisan pail
```

### Truy cập ứng dụng

Mở trình duyệt và truy cập:
- **Ứng dụng:** http://localhost:8000
- **Đăng ký/Đăng nhập:** http://localhost:8000/register

<a name="cấu-trúc-dự-án"></a>
## 📁 Cấu trúc dự án

```
flower-shop/
├── app/
│   ├── Models/          # MongoDB Models (Product, Order, Review, Cart, User)
│   ├── Services/        # Business Logic (CartService)
│   ├── Jobs/            # Queue Jobs (UpdateProductStatsJob)
│   └── Http/Controllers/
├── database/
│   ├── factories/       # Model Factories
│   ├── migrations/      # Database Migrations
│   └── seeders/         # Database Seeders
├── resources/
│   ├── views/           # Blade Templates
│   ├── js/              # JavaScript Files
│   └── css/             # Stylesheets
├── routes/
│   ├── web.php          # Web Routes
│   └── auth.php         # Authentication Routes
└── config/
    ├── database.php     # Cấu hình Database
    └── cache.php        # Cấu hình Cache
```

<a name="tài-liệu-bổ-sung"></a>
## 📚 Tài liệu bổ sung

- [Giải thích Redis Prefix](REDIS_PREFIX_EXPLAINED.md) - Hướng dẫn chi tiết về xử lý Redis prefix trong dự án

## 🧪 Testing

Chạy test suite:
```bash
composer test
```

Hoặc:
```bash
php artisan test
```

## 🔧 Khắc phục sự cố

**Lỗi kết nối MongoDB:**
- Đảm bảo dịch vụ MongoDB đang chạy
- Kiểm tra thông tin kết nối trong `.env`

**Lỗi kết nối Redis:**
- Đảm bảo dịch vụ Redis đang chạy
- Xác minh `REDIS_HOST` và `REDIS_PORT` trong `.env`

**Queue Jobs không xử lý:**
- Đảm bảo queue worker đang chạy: `php artisan queue:work`
- Kiểm tra `QUEUE_CONNECTION=database` trong `.env`

---

## 📄 License / Giấy phép

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Dự án này là phần mềm mã nguồn mở được cấp phép theo [giấy phép MIT](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author / Tác giả

**GitHub:** [@hfuwcs](https://github.com/hfuwcs)

---

## 🤝 Contributing / Đóng góp

Contributions, issues, and feature requests are welcome!

Rất hoan nghênh các đóng góp, báo cáo lỗi và đề xuất tính năng mới!
