# 📚 Flower Shop - Chỉ Mục Toàn Bộ Files & Functions

> **Project:** Laravel Flower Shop với MongoDB, Redis, MeiliSearch  
> **Generated:** December 4, 2025

---

## 📁 Mục Lục

- [1. Controllers](#1-controllers)
- [2. Models](#2-models)
- [3. Services](#3-services)
- [4. Jobs](#4-jobs)
- [5. Middleware](#5-middleware)
- [6. Console Commands](#6-console-commands)
- [7. Query Filters](#7-query-filters)
- [8. Form Requests](#8-form-requests)
- [9. Routes](#9-routes)
- [10. Filament Resources (Admin Panel)](#10-filament-resources-admin-panel)
- [11. Database](#11-database)
- [12. Views Structure](#12-views-structure)

---

## 1. Controllers

### 📂 `app/Http/Controllers/`

#### `CartController.php`
Controller quản lý giỏ hàng.

| Function | Mô tả |
|----------|-------|
| `__construct(CartService $cartService)` | Dependency Injection CartService |
| `index(Request $request)` | Hiển thị trang giỏ hàng |
| `add(Request $request, Product $product)` | Thêm sản phẩm vào giỏ hàng |
| `update(Request $request, string $productId)` | Cập nhật số lượng sản phẩm |
| `remove(Request $request, string $productId)` | Xóa sản phẩm khỏi giỏ hàng |
| `applyCoupon(Request $request)` | Áp dụng mã giảm giá |

---

#### `CheckoutController.php`
Controller xử lý thanh toán với Stripe.

| Function | Mô tả |
|----------|-------|
| `__construct(CartService, OrderService)` | DI CartService & OrderService |
| `index(Request $request)` | Hiển thị trang checkout, tạo Stripe PaymentIntent |
| `process(Request $request)` | Lưu địa chỉ giao hàng cho đơn hàng pending |
| `success()` | Hiển thị trang thanh toán thành công |

---

#### `ProductController.php`
Controller quản lý sản phẩm.

| Function | Mô tả |
|----------|-------|
| `index(Request $request, Pipeline $pipeline)` | Danh sách sản phẩm với filter (category, price, sort) |
| `show(Request $request, Product $product)` | Chi tiết sản phẩm với reviews phân trang |

**Performance Logging:** Đo thời gian query MongoDB, cache, render view.

---

#### `ReviewController.php`
Controller quản lý đánh giá sản phẩm.

| Function | Mô tả |
|----------|-------|
| `__construct(PointService $pointService)` | DI PointService |
| `store(StoreReviewRequest $request)` | Tạo review mới, cộng điểm thưởng |
| `vote(Request $request, Review $review)` | Vote up/down review (lưu Redis) |
| `create(OrderItem $orderItem)` | Form tạo review cho order item |
| `clearReviewsCache($productId)` | Xóa cache reviews của sản phẩm |

---

#### `RewardController.php`
Controller quản lý đổi thưởng.

| Function | Mô tả |
|----------|-------|
| `index()` | Hiển thị cửa hàng đổi thưởng |
| `redeem(Reward $reward)` | Đổi điểm lấy phần thưởng (tạo Coupon) |
| `myRewards()` | Danh sách phần thưởng đã đổi |
| `processRewardByType(User, Reward)` | Phân luồng xử lý theo loại reward |
| `processCouponReward(User, Reward)` | Xử lý reward loại coupon |
| `processPhysicalGiftReward(User, Reward)` | Xử lý reward loại quà vật lý |
| `generateUniqueCouponForReward(User, Reward)` | Tạo mã coupon unique |

---

#### `SearchController.php`
Controller tìm kiếm sản phẩm với MeiliSearch.

| Function | Mô tả |
|----------|-------|
| `index(Request $request)` | Tìm kiếm sản phẩm với filter (category, price) |

---

#### `LeaderboardController.php`
Controller bảng xếp hạng sản phẩm.

| Function | Mô tả |
|----------|-------|
| `index()` | Top 10 sản phẩm đánh giá cao nhất từ Redis ZSET |

---

#### `OrderHistoryController.php`
Controller lịch sử đơn hàng.

| Function | Mô tả |
|----------|-------|
| `index()` | Danh sách đơn hàng của user (phân trang) |

---

#### `OrderItemController.php`
Controller quản lý item trong đơn hàng.

| Function | Mô tả |
|----------|-------|
| `confirmDelivery(OrderItem $orderItem)` | Xác nhận đã nhận hàng, mở cửa sổ review 7 ngày |

---

#### `ProfileController.php`
Controller quản lý hồ sơ người dùng.

| Function | Mô tả |
|----------|-------|
| `edit(Request $request)` | Form chỉnh sửa profile + lịch sử điểm |
| `update(ProfileUpdateRequest $request)` | Cập nhật thông tin profile |
| `destroy(Request $request)` | Xóa tài khoản |

---

#### `LanguageController.php`
Controller đa ngôn ngữ.

| Function | Mô tả |
|----------|-------|
| `switch(string $locale)` | Chuyển đổi ngôn ngữ (en/vi) |

---

#### `StripeWebhookController.php`
Controller xử lý webhook từ Stripe.

| Function | Mô tả |
|----------|-------|
| `__construct(PointService $pointService)` | DI PointService |
| `handleWebhook(Request $request)` | Xử lý webhook (payment_intent.succeeded) |

---

### 📂 `app/Http/Controllers/Auth/`

| File | Functions | Mô tả |
|------|-----------|-------|
| `AuthenticatedSessionController.php` | `create()`, `store()`, `destroy()` | Login/Logout |
| `RegisteredUserController.php` | `create()`, `store()` | Đăng ký tài khoản |
| `PasswordController.php` | `update()` | Cập nhật mật khẩu |
| `PasswordResetLinkController.php` | `create()`, `store()` | Gửi link reset password |
| `NewPasswordController.php` | `create()`, `store()` | Đặt mật khẩu mới |
| `ConfirmablePasswordController.php` | `show()`, `store()` | Xác nhận mật khẩu |
| `EmailVerificationPromptController.php` | `__invoke()` | Prompt xác thực email |
| `EmailVerificationNotificationController.php` | `store()` | Gửi lại email xác thực |
| `VerifyEmailController.php` | `__invoke()` | Xác thực email |

---

## 2. Models

### 📂 `app/Models/`

#### `User.php`
Model người dùng (MongoDB + Auth).

| Property | Type | Mô tả |
|----------|------|-------|
| `name` | string | Tên người dùng |
| `email` | string | Email |
| `password` | hashed | Mật khẩu |
| `points_total` | integer | Tổng điểm thưởng |
| `membership` | array | Thông tin membership tier |

| Relationship | Type | Mô tả |
|--------------|------|-------|
| `reviews()` | hasMany | Reviews của user |
| `cart()` | hasOne | Giỏ hàng |
| `orders()` | hasMany | Đơn hàng |

---

#### `Product.php`
Model sản phẩm (MongoDB + Scout Search).

| Property | Type | Mô tả |
|----------|------|-------|
| `name` | string | Tên sản phẩm |
| `description` | string | Mô tả |
| `category` | string | Danh mục |
| `price` | decimal | Giá |
| `stock_quantity` | integer | Số lượng tồn kho |
| `images` | array | Hình ảnh |
| `average_rating` | double | Điểm đánh giá TB |
| `review_count` | integer | Số lượng reviews |

| Function | Mô tả |
|----------|-------|
| `inStock()` | Kiểm tra còn hàng |
| `toSearchableArray()` | Data cho MeiliSearch |
| `scopeFilterByCategory()` | Scope lọc theo category |
| `scopeFilterByPriceRange()` | Scope lọc theo giá |

---

#### `Order.php`
Model đơn hàng.

| Property | Type | Mô tả |
|----------|------|-------|
| `user_id` | ObjectId | ID người dùng |
| `status` | string | Trạng thái (pending, paid, shipped...) |
| `total_amount` | decimal | Tổng tiền |
| `shipping_address` | array | Địa chỉ giao hàng |
| `payment_details` | array | Chi tiết thanh toán |

| Relationship | Type | Mô tả |
|--------------|------|-------|
| `user()` | belongsTo | Người đặt hàng |
| `items()` | hasMany | Các item trong đơn |

---

#### `OrderItem.php`
Model item trong đơn hàng.

| Property | Type | Mô tả |
|----------|------|-------|
| `order_id` | ObjectId | ID đơn hàng |
| `product_id` | ObjectId | ID sản phẩm |
| `quantity` | integer | Số lượng |
| `price_at_purchase` | decimal | Giá lúc mua |
| `delivery_status` | string | Trạng thái giao hàng |
| `review_deadline_at` | datetime | Hạn review |
| `review_id` | ObjectId | ID review đã viết |

---

#### `Cart.php`
Model giỏ hàng.

| Property | Type | Mô tả |
|----------|------|-------|
| `user_id` | ObjectId | ID người dùng |
| `items` | array | Danh sách sản phẩm |
| `applied_coupon` | array | Coupon đã áp dụng |
| `points_total` | integer | Tổng điểm |
| `membership` | array | Thông tin membership |

---

#### `Review.php`
Model đánh giá sản phẩm.

| Property | Type | Mô tả |
|----------|------|-------|
| `product_id` | ObjectId | ID sản phẩm |
| `user_id` | ObjectId | ID người viết |
| `rating` | integer | Điểm đánh giá (1-5) |
| `title` | string | Tiêu đề |
| `content` | string | Nội dung |
| `upvotes` | integer | Số vote up |
| `downvotes` | integer | Số vote down |
| `comments` | array | Bình luận |

---

#### `Coupon.php`
Model mã giảm giá.

| Property | Type | Mô tả |
|----------|------|-------|
| `code` | string | Mã coupon |
| `type` | string | Loại (fixed/percent) |
| `value` | decimal | Giá trị |
| `expires_at` | datetime | Ngày hết hạn |
| `usage_limit` | integer | Giới hạn sử dụng |
| `usage_count` | integer | Số lần đã dùng |

| Function | Mô tả |
|----------|-------|
| `isValid()` | Kiểm tra coupon còn hiệu lực |

---

#### `Reward.php`
Model phần thưởng.

| Property | Type | Mô tả |
|----------|------|-------|
| `name` | string | Tên phần thưởng |
| `description` | string | Mô tả |
| `type` | string | Loại (coupon, physical_gift...) |
| `point_cost` | integer | Số điểm cần đổi |
| `reward_details` | object | Chi tiết phần thưởng |
| `is_active` | boolean | Trạng thái kích hoạt |

---

#### `UserReward.php`
Model phần thưởng đã đổi của user.

| Property | Type | Mô tả |
|----------|------|-------|
| `user_id` | ObjectId | ID người dùng |
| `reward_id` | ObjectId | ID phần thưởng |
| `status` | string | Trạng thái (claimed/used) |
| `claimed_at` | datetime | Ngày đổi |
| `reward_data` | array | Data cụ thể (coupon_code...) |

---

#### `PointTransaction.php`
Model giao dịch điểm thưởng.

| Property | Type | Mô tả |
|----------|------|-------|
| `user_id` | ObjectId | ID người dùng |
| `points_awarded` | integer | Số điểm (+/-) |
| `action_type` | string | Loại hành động |
| `metadata` | array | Dữ liệu bổ sung |

---

#### `MembershipTier.php`
Model cấp độ membership.

| Property | Type | Mô tả |
|----------|------|-------|
| `name` | string | Tên tier (Bronze, Silver...) |
| `min_points` | integer | Điểm tối thiểu |
| `benefits` | array | Quyền lợi |

---

## 3. Services

### 📂 `app/Services/`

#### `CartService.php`
Service xử lý logic giỏ hàng.

| Function | Mô tả |
|----------|-------|
| `addProduct(User, Product, int)` | Thêm sản phẩm vào giỏ |
| `getCartContent(User)` | Lấy nội dung giỏ hàng với tính toán |
| `updateItemQuantity(User, productId, quantity)` | Cập nhật số lượng |
| `removeItem(User, productId)` | Xóa item khỏi giỏ |
| `applyCoupon(User, Coupon)` | Áp dụng coupon |
| `removeCoupon(User)` | Gỡ coupon |

---

#### `OrderService.php`
Service xử lý logic đơn hàng.

| Function | Mô tả |
|----------|-------|
| `__construct(CartService)` | DI CartService |
| `createOrderFromCart(User, shippingDetails)` | Tạo đơn hàng từ giỏ (transaction) |
| `createPendingOrderFromCart(User)` | Tạo đơn hàng pending (cho checkout) |

---

#### `PointService.php`
Service xử lý logic điểm thưởng.

| Function | Mô tả |
|----------|-------|
| `addPointsForAction(User, actionType, Model?)` | Cộng điểm cho hành động |
| `calculatePoints(actionType, Model?)` | Tính số điểm cần cộng |
| `calculateOrderPoints(Order)` | Tính điểm cho đơn hàng |

---

## 4. Jobs

### 📂 `app/Jobs/`

#### `UpdateProductStatsJob.php`
Job cập nhật thống kê sản phẩm (Queue).

| Function | Mô tả |
|----------|-------|
| `__construct(Product)` | Nhận Product cần update |
| `handle()` | Tính average_rating, review_count bằng MongoDB Aggregation, cập nhật Redis Leaderboard |

---

#### `UpdateUserPointsAndTierJob.php`
Job cập nhật điểm và tier của user (Queue).

| Function | Mô tả |
|----------|-------|
| `__construct(string userId)` | Nhận userId |
| `handle()` | Tính tổng điểm, xác định tier mới, cập nhật Redis Leaderboard |

---

## 5. Middleware

### 📂 `app/Http/Middleware/`

#### `AddServerTimingHeader.php`
Middleware đo performance.

| Function | Mô tả |
|----------|-------|
| `handle(Request, Closure)` | Thêm Server-Timing header (total, bootstrap, app time) |

---

#### `SetLocale.php`
Middleware đa ngôn ngữ.

| Function | Mô tả |
|----------|-------|
| `handle(Request, Closure)` | Set locale từ session |

---

## 6. Console Commands

### 📂 `app/Console/Commands/`

#### `SyncReviewVotesToDB.php`
```bash
php artisan app:sync-review-votes-to-db
```
Đồng bộ vote counts từ Redis về MongoDB.

---

#### `PopulateLeaderboardCommand.php`
```bash
php artisan app:populate-leaderboard
```
Khởi tạo Redis Leaderboard từ dữ liệu hiện có.

---

#### `AutoCloseReviewWindows.php`
```bash
php artisan app:auto-close-review-windows
```
Đóng cửa sổ review đã hết hạn (7 ngày sau delivery).

---

#### `ConfigureSearchEngine.php`
```bash
php artisan app:configure-search-engine
```
Cấu hình MeiliSearch settings.

---

#### `FixOrderItemsDeliveryStatus.php`
```bash
php artisan app:fix-order-items-delivery-status
```
Sửa trạng thái delivery_status cho order items.

---

#### `MigrateReviewsToObjectId.php`
```bash
php artisan app:migrate-reviews-to-object-id
```
Chuyển đổi product_id/user_id sang ObjectId.

---

#### `UpdateExistingProductsWithPrice.php`
```bash
php artisan app:update-existing-products-with-price
```
Cập nhật trường price cho products cũ.

---

### Scheduled Commands (`routes/console.php`)
```php
Schedule::command('app:sync-review-votes-to-db')->everyFiveMinutes();
Schedule::command('app:auto-close-review-windows')->daily();
```

---

## 7. Query Filters

### 📂 `app/QueryFilters/`

Pipeline pattern cho filtering sản phẩm.

#### `CategoryFilter.php`
| Function | Mô tả |
|----------|-------|
| `handle(Builder, Closure)` | Lọc theo category từ request |

#### `PriceRangeFilter.php`
| Function | Mô tả |
|----------|-------|
| `handle(Builder, Closure)` | Lọc theo khoảng giá (price_min, price_max) |

#### `SortFilter.php`
| Function | Mô tả |
|----------|-------|
| `handle(Builder, Closure)` | Sắp xếp (price_asc, price_desc, created_at) |

---

## 8. Form Requests

### 📂 `app/Http/Requests/`

#### `StoreReviewRequest.php`
Validation cho tạo review mới.

#### `ProfileUpdateRequest.php`
Validation cho cập nhật profile.

#### `Auth/LoginRequest.php`
Validation cho đăng nhập.

---

## 9. Routes

### 📂 `routes/`

#### `web.php` - Web Routes

**Public Routes:**
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/` | GET | `ProductController@index` | `products.index` |
| `/products/{product}` | GET | `ProductController@show` | `products.show` |
| `/products/{product}/reviews` | POST | `ReviewController@store` | `reviews.store` |
| `/reviews/{review}/vote` | POST | `ReviewController@vote` | `reviews.vote` |
| `/leaderboard` | GET | `LeaderboardController@index` | `leaderboard.index` |
| `/search` | GET | `SearchController@index` | `search.index` |
| `/language/{locale}` | GET | `LanguageController@switch` | `language.switch` |
| `/stripe/webhook` | POST | `StripeWebhookController@handleWebhook` | `stripe.webhook` |

**Authenticated Routes (middleware: auth):**

*Cart:*
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/cart` | GET | `CartController@index` | `cart.index` |
| `/cart/add/{product}` | POST | `CartController@add` | `cart.add` |
| `/cart/update/{productId}` | PATCH | `CartController@update` | `cart.update` |
| `/cart/remove/{productId}` | DELETE | `CartController@remove` | `cart.remove` |
| `/cart/coupon` | POST | `CartController@applyCoupon` | `cart.applyCoupon` |

*Checkout:*
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/checkout` | GET | `CheckoutController@index` | `checkout.index` |
| `/checkout` | POST | `CheckoutController@process` | `checkout.process` |
| `/checkout/success` | GET | `CheckoutController@success` | `checkout.success` |

*Orders:*
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/my-orders` | GET | `OrderHistoryController@index` | `orders.history` |
| `/order-items/{orderItem}/confirm-delivery` | POST | `OrderItemController@confirmDelivery` | `order-item.confirm-delivery` |

*Reviews:*
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/reviews/create/{orderItem}` | GET | `ReviewController@create` | `reviews.create` |

*Rewards:*
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/rewards` | GET | `RewardController@index` | `rewards.index` |
| `/rewards/{reward}/redeem` | POST | `RewardController@redeem` | `rewards.redeem` |
| `/my-rewards` | GET | `RewardController@myRewards` | `rewards.my` |

*Profile:*
| Route | Method | Controller@Action | Name |
|-------|--------|-------------------|------|
| `/profile` | GET | `ProfileController@edit` | `profile.edit` |
| `/profile` | PATCH | `ProfileController@update` | `profile.update` |
| `/profile` | DELETE | `ProfileController@destroy` | `profile.destroy` |

---

#### `auth.php` - Authentication Routes

**Guest Routes:**
| Route | Method | Name |
|-------|--------|------|
| `/register` | GET/POST | `register` |
| `/login` | GET/POST | `login` |
| `/forgot-password` | GET/POST | `password.request` |
| `/reset-password/{token}` | GET/POST | `password.reset` |

**Authenticated Routes:**
| Route | Method | Name |
|-------|--------|------|
| `/verify-email` | GET | `verification.notice` |
| `/verify-email/{id}/{hash}` | GET | `verification.verify` |
| `/email/verification-notification` | POST | `verification.send` |
| `/confirm-password` | GET/POST | `password.confirm` |
| `/password` | PUT | `password.update` |
| `/logout` | POST | `logout` |

---

## 10. Filament Resources (Admin Panel)

### 📂 `app/Filament/Resources/`

| Resource | Mô tả |
|----------|-------|
| `Coupons/` | Quản lý mã giảm giá |
| `MembershipTiers/` | Quản lý cấp độ membership |
| `Orders/` | Quản lý đơn hàng |
| `Products/` | Quản lý sản phẩm |
| `Reviews/` | Quản lý đánh giá |
| `Rewards/` | Quản lý phần thưởng |
| `Users/` | Quản lý người dùng |

### 📂 `app/Filament/Widgets/`

| Widget | Mô tả |
|--------|-------|
| `LatestOrders.php` | Đơn hàng mới nhất |
| `SalesChart.php` | Biểu đồ doanh số |
| `StatsOverview.php` | Tổng quan thống kê |

---

## 11. Database

### 📂 `database/seeders/`

| File | Mô tả |
|------|-------|
| `DatabaseSeeder.php` | Seeder chính |

### 📂 `database/factories/`

| File | Mô tả |
|------|-------|
| `ProductFactory.php` | Factory tạo Product |
| `ReviewFactory.php` | Factory tạo Review |
| `UserFactory.php` | Factory tạo User |

---

## 12. Views Structure

### 📂 `resources/views/`

```
views/
├── layouts/
│   ├── app.blade.php         # Layout chính
│   ├── guest.blade.php       # Layout cho guest
│   └── navigation.blade.php  # Navigation bar
├── components/               # Blade components
├── auth/                     # Authentication views
├── products/
│   ├── index.blade.php       # Danh sách sản phẩm
│   └── show.blade.php        # Chi tiết sản phẩm
├── cart/
│   └── index.blade.php       # Giỏ hàng
├── checkout/
│   ├── index.blade.php       # Trang checkout
│   └── success.blade.php     # Thanh toán thành công
├── orders/
│   └── history.blade.php     # Lịch sử đơn hàng
├── reviews/
│   └── create.blade.php      # Form tạo review
├── rewards/
│   ├── index.blade.php       # Cửa hàng đổi thưởng
│   └── my-rewards.blade.php  # Phần thưởng đã đổi
├── leaderboard/
│   └── index.blade.php       # Bảng xếp hạng
├── search/
│   └── index.blade.php       # Kết quả tìm kiếm
├── profile/
│   └── edit.blade.php        # Chỉnh sửa profile
├── dashboard.blade.php       # Dashboard
└── welcome.blade.php         # Trang chủ welcome
```

---

## 📊 Technology Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Laravel 11 |
| **Database** | MongoDB (via laravel-mongodb) |
| **Cache/Queue** | Redis |
| **Search** | MeiliSearch (via Laravel Scout) |
| **Payment** | Stripe |
| **Admin Panel** | Filament |
| **Frontend** | Blade + Alpine.js + Tailwind CSS |
| **Asset Build** | Vite |

---

## 🔑 Redis Keys Used

| Key Pattern | Type | Mô tả |
|-------------|------|-------|
| `leaderboard:products:top_rated` | ZSET | Top sản phẩm theo rating |
| `leaderboard:users:by_points` | ZSET | Top users theo điểm |
| `review:votes:{reviewId}` | HASH | Vote counts (upvotes, downvotes) |
| `review:user_votes:{reviewId}` | HASH | User votes (userId -> vote value) |
| `product:{productId}` | STRING | Cache product data |
| `product:basic:{productId}` | STRING | Cache basic product data |
| `product:{productId}:reviews:page:{n}` | STRING | Cache reviews phân trang |
| `product_categories` | STRING | Cache danh sách categories |

---

## 📝 Configuration Files

| File | Mô tả |
|------|-------|
| `config/database.php` | MongoDB & Redis connection |
| `config/cache.php` | Redis cache config |
| `config/queue.php` | Redis queue config |
| `config/scout.php` | MeiliSearch config |
| `config/stripe.php` | Stripe API keys |
| `config/gamification.php` | Points & tier config |
| `config/csp.php` | Content Security Policy |

---

> **Note:** File này được tạo tự động. Cập nhật khi có thay đổi cấu trúc project.
