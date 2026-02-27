# Flower Shop - Laravel NoSQL Project

A modern e-commerce platform built with Laravel 12, MongoDB, and Redis, designed to demonstrate high-performance data handling in a NoSQL environment.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Redis and MongoDB Architecture](#redis-and-mongodb-architecture)
- [Detailed Use Cases](#detailed-use-cases)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Running the Application](#running-the-application)
- [Project Structure](#project-structure)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

---

## Features

- Shopping Cart: Real-time product management with persistent storage.
- Order Management: Seamless checkout process and order tracking.
- Product Reviews: Advanced rating system with a community voting mechanism.
- Leaderboard: Dynamic ranking for top-rated products and active users.
- Authentication: Secure user access powered by Laravel Breeze.
- Redis Caching: Optimized data retrieval for high-traffic endpoints.
- MongoDB Integration: Schema-less storage for flexible e-commerce data structures.
- Queue Workers: Efficient background processing for statistical updates.

---

## Tech Stack

- Backend Framework: Laravel 12
- Frontend: Blade Templates, Alpine.js, Tailwind CSS
- Database: MongoDB (via mongodb/laravel-mongodb)
- Cache/Session: Redis (via predis/predis)
- Authentication: Laravel Breeze
- Build Tool: Vite
- Queue: Database driver
- PHP Version: 8.2 or higher

---

## Redis and MongoDB Architecture

This project implements a hybrid architecture that balances persistence with performance.

### MongoDB - Primary Data Store
- Document Storage: Stores core entities including Users, Products, Orders, and Reviews as JSON-like documents.
- Schema-less Design: Allows for rapid iterations of product attributes without complex migrations.
- Aggregation Pipeline: Executes complex analytical queries, such as calculating average ratings across thousands of reviews.

### Redis - Performance Layer
- Vote Tracking: Manages real-time review interaction using Redis Hashes.
- Leaderboard System: Utilizes Sorted Sets for real-time global product rankings based on user feedback.
- Data Caching: Implements Time-To-Live (TTL) caching for product details to minimize database load.
- Session Management: High-speed session handling for improved user experience.

### Data Synchronization Flow
1. Write Operations: User interactions are first captured in Redis for instant feedback, then synchronized to MongoDB via background Queue Jobs.
2. Read Operations: The application attempts to fetch data from the Redis cache. On a cache miss, it queries MongoDB and repopulates the cache.
3. Consistency: Scheduled tasks ensure that Redis counters and MongoDB records remain eventually consistent.

---

## Detailed Use Cases

### MongoDB Collections
| Feature | Collection | Description |
|---------|-----------|-------------|
| User Management | users | Profiles and authentication credentials. |
| Product Catalog | products | Detailed product data with flexible attributes. |
| Shopping Cart | carts | Items stored as embedded documents within user records. |
| Orders | orders | Comprehensive history including payment and delivery status. |
| Reviews | reviews | Feedback data including ratings and comments. |

### Redis Data Structures
| Feature | Data Structure | Purpose |
|---------|---------------|---------|
| Voting System | Hash | Stores pending upvote/downvote counts. |
| User Tracking | Hash | Prevents duplicate voting by tracking user IDs. |
| Leaderboards | Sorted Set | Maintains real-time rankings for the most popular products. |
| Product Cache | String | Stores serialized product objects to reduce DB queries. |

---

## Prerequisites

Ensure your system meets the following requirements:
1. PHP 8.2 or higher (Extensions: mongodb, redis, pdo_sqlite)
2. Composer (PHP Package Manager)
3. Node.js and NPM (v18 or higher)
4. MongoDB (v5.0 or higher)
5. Redis (v6.0 or higher)

---

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/hfuwcs/php_NoSQL_flower_shop.git
cd php_NoSQL_flower_shop
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
Copy the example configuration and generate the application key:
```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with your specific credentials:
```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=flower_shop

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=database
```

### 4. Database Initialization
```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets
```bash
npm run build
```

---

## Running the Application

### Automated Development (Recommended)
Launch the server, queue worker, and frontend compiler simultaneously:
```bash
composer dev
```

### Manual Setup
If you prefer separate terminals:
- Web Server: `php artisan serve`
- Queue Worker: `php artisan queue:work`
- Frontend: `npm run dev`

Access the application at: `http://localhost:8000`

---

## Project Structure

```
flower-shop/
├── app/
│   ├── Models/          # MongoDB Document Models
│   ├── Services/        # Cart and Business Logic
│   ├── Jobs/            # Redis-to-MongoDB Sync Jobs
│   └── Http/Controllers/
├── database/
│   ├── migrations/      # MongoDB Collection Definitions
│   └── seeders/         # Sample Data
├── resources/
│   ├── views/           # Blade Components
│   └── js/              # Alpine.js Logic
└── routes/
    └── web.php          # Application Routes
```

---

## Testing

Execute the test suite using the following command:
```bash
php artisan test
```

---

## Troubleshooting

- MongoDB Connection: Ensure the MongoDB service is active and the port (27017) is not blocked.
- Redis Connection: Verify that the Redis server is running and matches the `REDIS_HOST` configuration.
- Missing Updates: If statistics are not updating, verify that the queue worker is running: `php artisan queue:work`.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Author

Developed by [@hfuwcs](https://github.com/hfuwcs)
