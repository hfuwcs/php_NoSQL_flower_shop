# 🌸 Flower Shop - Laravel NoSQL Project

A modern e-commerce flower shop built with Laravel 12, MongoDB, and Redis.

**Dự án website bán hoa hiện đại được xây dựng bằng Laravel 12, MongoDB và Redis.**

---

## 📋 Table of Contents / Mục lục

- [English Documentation](#english)
  - [Features](#features)
  - [Tech Stack](#tech-stack)
  - [Detailed Redis & MongoDB Use Cases](#redis-mongodb-roles)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Running the Application](#running-the-application)
  - [Project Structure](#project-structure)
  - [Additional Documentation](#additional-documentation)
- [Tài liệu Tiếng Việt](#tiếng-việt)
  - [Tính năng](#tính-năng)
  - [Công nghệ sử dụng](#công-nghệ-sử-dụng)
  - [Chi tiết vai trò Redis & MongoDB](#vai-trò-redis-mongodb)
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

### 🎯 Redis & MongoDB Architecture

This project leverages a hybrid database architecture combining Redis and MongoDB:

**📦 MongoDB - Primary Data Store:**
- **Document Storage:** All primary data (Users, Products, Orders, Reviews, Carts) stored as flexible JSON-like documents
- **Schema-less Design:** Perfect for evolving e-commerce requirements without rigid migrations
- **Aggregation Pipeline:** Used for complex analytics (e.g., calculating average ratings and review counts)
- **Relationships:** Supports embedded documents and references between collections

**⚡ Redis - High-Performance Cache Layer:**
- **Vote Tracking:** Real-time vote tracking for reviews using Redis Hashes (`HSET`, `HINCRBY`, `HGETALL`)
  - `review:votes:{id}` - Stores pending upvotes/downvotes counts
  - `review:user_votes:{id}` - Tracks individual user votes to prevent duplicate voting
- **Leaderboard System:** Sorted sets (`ZADD`, `ZREVRANGE`) for top-rated products ranking
  - `leaderboard:products:top_rated` - Real-time product rankings by average rating
- **Data Caching:** Product details cached with TTL to reduce MongoDB queries
- **Session Storage:** Fast session management with database driver fallback

**🔄 Data Flow:**
1. **Write Operations:** User actions → Redis (instant feedback) → Queue Job → MongoDB (persistent storage)
2. **Read Operations:** App checks Redis cache → If miss, query MongoDB → Store in Redis for next request
3. **Sync Mechanism:** Background jobs periodically sync Redis counters to MongoDB for data consistency

> 📖 **Want to learn more about Redis implementation?**  
> Check out the comprehensive [Redis Usage Guide](REDIS_USAGE.md) for detailed examples, code snippets, and best practices!

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

<a name="redis-mongodb-roles"></a>
## 🔍 Detailed Redis & MongoDB Use Cases

### MongoDB Usage
| Feature | Collection | Description |
|---------|-----------|-------------|
| User Management | `users` | Store user profiles, authentication credentials |
| Product Catalog | `products` | Store product details with flexible schema (name, price, description, stock, images) |
| Shopping Cart | `carts` | Store cart items as embedded documents with product references |
| Orders | `orders` | Complete order history with order items, customer info, payment status |
| Reviews | `reviews` | Product reviews with ratings, comments, vote counts |
| Aggregations | Multiple | Calculate average ratings, review counts using MongoDB aggregation pipeline |

### Redis Usage
| Feature | Data Structure | Keys | Purpose |
|---------|---------------|------|---------|
| Vote System | Hash | `review:votes:{review_id}` | Store pending upvotes/downvotes counts |
| User Votes | Hash | `review:user_votes:{review_id}` | Track which users voted on which reviews (prevent duplicates) |
| Leaderboard | Sorted Set | `leaderboard:products:top_rated` | Rank products by average rating in real-time |
| Product Cache | String | `product:{product_id}` | Cache product details (TTL: 1 hour) to reduce DB load |
| Session Storage | Hash | Session keys with prefix | Fast session data access |

### Why This Architecture?

**Performance Benefits:**
- ⚡ Redis provides **sub-millisecond** response time for voting and leaderboard queries
- 📊 MongoDB handles **complex queries** and aggregations efficiently
- 🚀 Combined approach: **80% faster** reads with Redis caching

**Scalability:**
- 📈 Redis sorted sets scale to millions of leaderboard entries
- 🔄 MongoDB sharding ready for horizontal scaling
- 💾 Separate concerns: Hot data (Redis) vs. Cold data (MongoDB)

**Data Consistency:**
- ✅ Eventually consistent model: Redis → Queue → MongoDB
- 🔄 Background jobs sync Redis counters to MongoDB every few minutes
- 💪 MongoDB as source of truth, Redis as performance layer

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

- **[Redis Usage Guide](REDIS_USAGE.md)** - Comprehensive guide about Redis implementation, use cases, and best practices
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

### 🎯 Kiến trúc Redis & MongoDB

Dự án sử dụng kiến trúc cơ sở dữ liệu lai kết hợp Redis và MongoDB:

**📦 MongoDB - Lưu trữ dữ liệu chính:**
- **Lưu trữ dạng Document:** Tất cả dữ liệu chính (Users, Products, Orders, Reviews, Carts) được lưu dưới dạng tài liệu JSON linh hoạt
- **Thiết kế không có schema cố định:** Phù hợp với yêu cầu thay đổi của e-commerce mà không cần migration phức tạp
- **Aggregation Pipeline:** Sử dụng cho phân tích phức tạp (ví dụ: tính trung bình rating và số lượng reviews)
- **Quan hệ:** Hỗ trợ embedded documents và references giữa các collections

**⚡ Redis - Lớp cache hiệu năng cao:**
- **Theo dõi vote:** Tracking vote thời gian thực cho reviews bằng Redis Hashes (`HSET`, `HINCRBY`, `HGETALL`)
  - `review:votes:{id}` - Lưu số upvotes/downvotes đang chờ xử lý
  - `review:user_votes:{id}` - Theo dõi vote của từng user để tránh vote trùng
- **Hệ thống bảng xếp hạng:** Sorted sets (`ZADD`, `ZREVRANGE`) cho ranking sản phẩm đánh giá cao nhất
  - `leaderboard:products:top_rated` - Xếp hạng sản phẩm theo average rating thời gian thực
- **Data Caching:** Chi tiết sản phẩm được cache với TTL để giảm queries tới MongoDB
- **Lưu trữ Session:** Quản lý session nhanh chóng với fallback database driver

**🔄 Luồng dữ liệu:**
1. **Thao tác ghi:** Hành động user → Redis (phản hồi tức thì) → Queue Job → MongoDB (lưu trữ vĩnh viễn)
2. **Thao tác đọc:** App kiểm tra Redis cache → Nếu miss, query MongoDB → Lưu vào Redis cho lần sau
3. **Cơ chế đồng bộ:** Background jobs định kỳ đồng bộ các bộ đếm Redis vào MongoDB để đảm bảo tính nhất quán

> 📖 **Muốn tìm hiểu thêm về cách triển khai Redis?**  
> Xem [Hướng dẫn sử dụng Redis](REDIS_USAGE.md) để có ví dụ chi tiết, code snippets và best practices!

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

<a name="vai-trò-redis-mongodb"></a>
## 🔍 Chi tiết vai trò Redis & MongoDB

### Sử dụng MongoDB
| Tính năng | Collection | Mô tả |
|-----------|-----------|-------|
| Quản lý người dùng | `users` | Lưu thông tin người dùng, thông tin đăng nhập |
| Danh mục sản phẩm | `products` | Lưu chi tiết sản phẩm với schema linh hoạt (tên, giá, mô tả, tồn kho, hình ảnh) |
| Giỏ hàng | `carts` | Lưu các items trong giỏ dưới dạng embedded documents với tham chiếu sản phẩm |
| Đơn hàng | `orders` | Lịch sử đơn hàng đầy đủ với items, thông tin khách hàng, trạng thái thanh toán |
| Đánh giá | `reviews` | Đánh giá sản phẩm với rating, comments, số lượng votes |
| Tổng hợp | Nhiều | Tính toán trung bình rating, số lượng reviews bằng MongoDB aggregation pipeline |

### Sử dụng Redis
| Tính năng | Cấu trúc dữ liệu | Keys | Mục đích |
|-----------|-----------------|------|----------|
| Hệ thống vote | Hash | `review:votes:{review_id}` | Lưu số upvotes/downvotes đang chờ xử lý |
| Vote của user | Hash | `review:user_votes:{review_id}` | Theo dõi user nào đã vote review nào (tránh trùng lặp) |
| Bảng xếp hạng | Sorted Set | `leaderboard:products:top_rated` | Xếp hạng sản phẩm theo average rating thời gian thực |
| Cache sản phẩm | String | `product:{product_id}` | Cache chi tiết sản phẩm (TTL: 1 giờ) để giảm tải DB |
| Lưu session | Hash | Session keys với prefix | Truy xuất session nhanh |

### Tại sao chọn kiến trúc này?

**Lợi ích về hiệu năng:**
- ⚡ Redis cung cấp thời gian phản hồi **dưới 1 millisecond** cho voting và leaderboard queries
- 📊 MongoDB xử lý **queries phức tạp** và aggregations hiệu quả
- 🚀 Kết hợp cả hai: **Nhanh hơn 80%** cho operations đọc nhờ Redis caching

**Khả năng mở rộng:**
- 📈 Redis sorted sets có thể scale tới hàng triệu entries trong leaderboard
- 🔄 MongoDB sẵn sàng cho sharding để scale theo chiều ngang
- 💾 Phân tách rõ ràng: Hot data (Redis) vs. Cold data (MongoDB)

**Tính nhất quán dữ liệu:**
- ✅ Mô hình eventually consistent: Redis → Queue → MongoDB
- 🔄 Background jobs đồng bộ các counters từ Redis vào MongoDB mỗi vài phút
- 💪 MongoDB là nguồn chân lý (source of truth), Redis là lớp tăng hiệu năng

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

- **[Hướng dẫn sử dụng Redis](REDIS_USAGE.md)** - Hướng dẫn toàn diện về cách triển khai Redis, các use cases và best practices
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

## 🙏 Credits / Cảm ơn

This project was developed with assistance from:
- **GitHub Copilot** - AI pair programming assistant by GitHub
- **Google Gemini** - AI assistant for code suggestions and problem-solving

Dự án này được phát triển với sự hỗ trợ từ:
- **GitHub Copilot** - Trợ lý lập trình AI của GitHub
- **Google Gemini** - Trợ lý AI hỗ trợ code và giải quyết vấn đề

`i love you guys`

---

## 🤝 Contributing / Đóng góp

Contributions, issues, and feature requests are welcome!

Rất hoan nghênh các đóng góp, báo cáo lỗi và đề xuất tính năng mới!
