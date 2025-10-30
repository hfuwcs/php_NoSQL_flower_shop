# 📘 GIẢI THÍCH CHI TIẾT: REDIS PREFIX TRONG LARAVEL

## 🎯 PHẦN 1: REDIS PREFIX LÀ GÌ?

### 1.1 Định nghĩa
Redis Prefix là một chuỗi ký tự được **tự động thêm vào đầu mọi key** khi Laravel lưu vào Redis.

### 1.2 Tại sao cần Prefix?
```
Tưởng tượng bạn có nhiều ứng dụng Laravel dùng chung 1 Redis server:
- App 1: Flower Shop  
- App 2: Book Store
- App 3: Music Store

Nếu KHÔNG có prefix:
❌ App 1 tạo key: "user:1" 
❌ App 2 tạo key: "user:1"  --> XUNG ĐỘT!
❌ App 3 tạo key: "user:1"  --> XUNG ĐỘT!

Nếu CÓ prefix:
✅ App 1 tạo key: "flower-shop-database-user:1"
✅ App 2 tạo key: "book-store-database-user:1"
✅ App 3 tạo key: "music-store-database-user:1"  --> KHÔNG XUNG ĐỘT!
```

---

## 🔧 PHẦN 2: CẤU HÌNH PREFIX

### 2.1 File cấu hình: `config/database.php`

```php
'redis' => [
    'options' => [
        'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
    ],
]
```

**Giải thích từng bước:**

```php
// Bước 1: Lấy APP_NAME từ file .env
env('APP_NAME', 'laravel')  
// Kết quả: "Laravel"

// Bước 2: Chuyển thành chuỗi
(string) "Laravel"
// Kết quả: "Laravel"

// Bước 3: Slug hóa (chuyển thành chữ thường, thay space bằng -)
Str::slug("Laravel")
// Kết quả: "laravel"

// Bước 4: Thêm '-database-' vào cuối
"laravel" . "-database-"
// Kết quả: "laravel-database-"
```

### 2.2 Kiểm tra Prefix trong project

```bash
php artisan tinker
>>> config('database.redis.options.prefix')
=> "laravel-database-"
```

---

## 💾 PHẦN 3: LƯU DỮ LIỆU VÀO REDIS

### 3.1 Code mẫu (trong Controller hoặc Command)

```php
use Illuminate\Support\Facades\Redis;

// Bạn viết code:
Redis::hIncrBy('review:votes:69035212a150066473044174', 'upvotes', 1);
```

### 3.2 Quá trình xử lý của Laravel

```
┌─────────────────────────────────────────────────────────────┐
│ BƯỚC 1: Code của bạn                                        │
│ Redis::hIncrBy('review:votes:69035212a150066473044174', ...) │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ BƯỚC 2: Laravel Redis Facade nhận lệnh                      │
│ - Key bạn truyền vào: "review:votes:69035212a150066473044174" │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ BƯỚC 3: Laravel TỰ ĐỘNG THÊM PREFIX                         │
│ $prefix = config('database.redis.options.prefix');         │
│ // $prefix = "laravel-database-"                            │
│                                                              │
│ $fullKey = $prefix . 'review:votes:69035212a150066473044174' │
│ // $fullKey = "laravel-database-review:votes:69035212..."   │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ BƯỚC 4: Gửi lệnh xuống Redis Server                         │
│ HINCRBY "laravel-database-review:votes:69035212..." upvotes 1 │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ BƯỚC 5: Lưu vào Redis                                       │
│ Key thực tế trong Redis:                                    │
│ "laravel-database-review:votes:69035212a150066473044174"   │
│                                                              │
│ Value (Hash):                                               │
│ {                                                            │
│   "upvotes": "1"                                            │
│ }                                                            │
└─────────────────────────────────────────────────────────────┘
```

### 3.3 Minh họa bằng hình ảnh

```
BẠN VIẾT CODE:          LARAVEL XỬ LÝ:              REDIS LƯU:
┌──────────────┐        ┌──────────────┐        ┌─────────────────────┐
│review:votes: │  -->   │laravel-      │  -->   │laravel-database-    │
│69035212a...  │  +     │database-     │  +     │review:votes:        │
└──────────────┘        └──────────────┘        │69035212a150066473.. │
                                                 └─────────────────────┘
     (1)                      (2)                        (3)
  Key ngắn              Thêm prefix            Key đầy đủ trong Redis
```

---

## 🔍 PHẦN 4: ĐỌC DỮ LIỆU TỪ REDIS

### 4.1 Có 2 CÁCH ĐỌC khác nhau - QUAN TRỌNG!

#### Cách 1: Dùng method thông thường (Laravel TỰ ĐỘNG xử lý prefix)

```php
// Bạn viết:
$keys = Redis::keys('review:votes:*');

// Laravel xử lý:
// Bước 1: Thêm prefix vào pattern
$pattern = 'laravel-database-' . 'review:votes:*';
// $pattern = 'laravel-database-review:votes:*'

// Bước 2: Tìm keys
// Kết quả: ["laravel-database-review:votes:69035212a150066473044174"]

// Bước 3: TỰ ĐỘNG BỎ PREFIX khi trả về cho bạn
// Kết quả bạn nhận: ["review:votes:69035212a150066473044174"]
```

**⚠️ NHƯNG trong thực tế, cách này KHÔNG hoạt động với một số method!**

#### Cách 2: Dùng command() hoặc keys() - Laravel KHÔNG tự động xử lý

```php
// Bạn viết:
$keys = Redis::keys('*');

// Laravel GỬI TRỰC TIẾP xuống Redis, KHÔNG thêm prefix
// Kết quả: ["laravel-database-review:votes:69035212a150066473044174"]
// Bạn nhận được key ĐẦY ĐỦ PREFIX!
```

### 4.2 Vấn đề với hGetAll()

```php
// ❌ SAI - Sẽ không lấy được data
$votes = Redis::hGetAll('laravel-database-review:votes:69035212a150066473044174');
// Kết quả: []  (rỗng)

// ✅ ĐÚNG - Để Laravel tự thêm prefix
$votes = Redis::hGetAll('review:votes:69035212a150066473044174');
// Kết quả: ["upvotes" => "1", "downvotes" => "2"]
```

**Tại sao?**

```
Khi bạn truyền: "laravel-database-review:votes:69035212..."
Laravel xử lý:  "laravel-database-" + "laravel-database-review:votes:69035212..."
Kết quả tìm:    "laravel-database-laravel-database-review:votes:69035212..."
                ^^^^^^^^^^^^^^^^ THÊM 2 LẦN PREFIX! --> KHÔNG TÌM THẤY!
```

---

## 🔄 PHẦN 5: SYNC TỪ REDIS VÀO MONGODB

### 5.1 Toàn bộ Flow của Command

```php
class SyncReviewVotesToDB extends Command
{
    public function handle()
    {
        // ============================================
        // BƯỚC 1: LẤY PREFIX TỪ CONFIG
        // ============================================
        $prefix = config('database.redis.options.prefix');
        // Kết quả: "laravel-database-"
        
        Log::info("Using Redis prefix: '{$prefix}'");
        
        
        // ============================================
        // BƯỚC 2: TÌM TẤT CẢ KEYS TRONG REDIS
        // ============================================
        $allKeys = Redis::keys('*');
        // Redis::keys('*') KHÔNG tự động xử lý prefix
        // Kết quả: 
        // [
        //   "laravel-database-review:votes:69035212a150066473044174",
        //   "laravel-database-laravel-cache-HacDIiCE...",
        //   ...
        // ]
        
        Log::info("All keys in Redis: " . json_encode($allKeys));
        
        
        // ============================================
        // BƯỚC 3: LỌC CHỈ LẤY KEYS CỦA VOTES
        // ============================================
        $pattern = $prefix . 'review:votes:';
        // $pattern = "laravel-database-review:votes:"
        
        $voteKeys = array_filter($allKeys, function($key) use ($prefix) {
            return str_starts_with($key, $prefix . 'review:votes:');
        });
        
        // Kết quả: 
        // [
        //   "laravel-database-review:votes:69035212a150066473044174"
        // ]
        
        Log::info(count($voteKeys) . " vote key(s) found");
        
        
        // ============================================
        // BƯỚC 4: XỬ LÝ TỪNG KEY
        // ============================================
        foreach ($voteKeys as $redisKeyWithPrefix) {
            // $redisKeyWithPrefix = "laravel-database-review:votes:69035212..."
            
            Log::info("Processing: {$redisKeyWithPrefix}");
            
            
            // ----------------------------------------
            // BƯỚC 4.1: BỎ PREFIX RA
            // ----------------------------------------
            $keyWithoutPrefix = Str::after($redisKeyWithPrefix, $prefix);
            // Str::after("laravel-database-review:votes:69035...", "laravel-database-")
            // Kết quả: "review:votes:69035212a150066473044174"
            
            
            // ----------------------------------------
            // BƯỚC 4.2: LẤY DỮ LIỆU TỪ REDIS
            // ----------------------------------------
            // ✅ PHẢI DÙNG KEY KHÔNG CÓ PREFIX
            $votes = Redis::hGetAll($keyWithoutPrefix);
            // Laravel sẽ TỰ ĐỘNG thêm prefix:
            // "laravel-database-" + "review:votes:69035212..."
            // = "laravel-database-review:votes:69035212..."
            
            // Kết quả:
            // [
            //   "upvotes" => "1",
            //   "downvotes" => "2"
            // ]
            
            Log::info("Votes data: " . json_encode($votes));
            
            
            // ----------------------------------------
            // BƯỚC 4.3: EXTRACT REVIEW ID
            // ----------------------------------------
            $reviewId = str_replace('review:votes:', '', $keyWithoutPrefix);
            // str_replace('review:votes:', '', 'review:votes:69035212a150066473044174')
            // Kết quả: "69035212a150066473044174"
            
            
            // ----------------------------------------
            // BƯỚC 4.4: CHUYỂN ĐỔI DỮ LIỆU
            // ----------------------------------------
            $upvotes = (int) ($votes['upvotes'] ?? 0);
            $downvotes = (int) ($votes['downvotes'] ?? 0);
            // $upvotes = 1
            // $downvotes = 2
            
            Log::info("Review ID: {$reviewId}, Upvotes: {$upvotes}, Downvotes: {$downvotes}");
            
            
            // ----------------------------------------
            // BƯỚC 4.5: TÌM REVIEW TRONG MONGODB
            // ----------------------------------------
            $review = Review::where('_id', $reviewId)->first();
            // Tìm document trong collection 'reviews' có _id = "69035212a150066473044174"
            
            if ($review) {
                // Review tìm thấy!
                Log::info("Found review in MongoDB. Current upvotes: {$review->upvotes}");
                
                
                // ----------------------------------------
                // BƯỚC 4.6: CẬP NHẬT VOTES TRONG MONGODB
                // ----------------------------------------
                $review->increment('upvotes', $upvotes);
                // Tăng upvotes thêm 1
                // Trước: upvotes = 2
                // Sau:   upvotes = 3
                
                $review->increment('downvotes', $downvotes);
                // Tăng downvotes thêm 2
                // Trước: downvotes = 0
                // Sau:   downvotes = 2
                
                Log::info("Updated! New upvotes: {$review->upvotes}, New downvotes: {$review->downvotes}");
                
                
                // ----------------------------------------
                // BƯỚC 4.7: XÓA KEY TRONG REDIS
                // ----------------------------------------
                // ✅ PHẢI DÙNG KEY KHÔNG CÓ PREFIX
                Redis::del($keyWithoutPrefix);
                // Laravel sẽ TỰ ĐỘNG thêm prefix:
                // "laravel-database-" + "review:votes:69035212..."
                // = "laravel-database-review:votes:69035212..."
                
                Log::info("Successfully deleted Redis key");
                
            } else {
                // Review KHÔNG tìm thấy trong MongoDB
                Log::error("Review ID {$reviewId} not found in MongoDB!");
            }
        }
        
        
        // ============================================
        // BƯỚC 5: HOÀN THÀNH
        // ============================================
        $this->info("\nSync completed!");
        return 0;
    }
}
```

---

## 📊 PHẦN 6: SO SÁNH CÁC TRƯỜNG HỢP

### 6.1 Bảng so sánh Key với/không có Prefix

| Tình huống | Code bạn viết | Laravel xử lý | Key thực tế trong Redis |
|-----------|--------------|---------------|------------------------|
| **Lưu vào Redis** | `Redis::hSet('review:votes:123', ...)` | Thêm prefix | `laravel-database-review:votes:123` |
| **Đọc từ Redis (đúng)** | `Redis::hGetAll('review:votes:123')` | Thêm prefix | Tìm `laravel-database-review:votes:123` ✅ |
| **Đọc từ Redis (sai)** | `Redis::hGetAll('laravel-database-review:votes:123')` | Thêm prefix | Tìm `laravel-database-laravel-database-review:votes:123` ❌ |
| **Tìm keys** | `Redis::keys('*')` | KHÔNG thêm prefix | Trả về `laravel-database-review:votes:123` (có prefix) |
| **Xóa key (đúng)** | `Redis::del('review:votes:123')` | Thêm prefix | Xóa `laravel-database-review:votes:123` ✅ |
| **Xóa key (sai)** | `Redis::del('laravel-database-review:votes:123')` | Thêm prefix | Xóa `laravel-database-laravel-database-review:votes:123` ❌ |

### 6.2 Quy tắc vàng

```
┌─────────────────────────────────────────────────────────────┐
│  QUY TẮC 1: KHI LƯU/ĐỌC/XÓA DỮ LIỆU                         │
│  --> Dùng key KHÔNG có prefix                               │
│  --> Laravel tự động thêm prefix                            │
│                                                              │
│  ✅ Đúng:  Redis::hGetAll('review:votes:123')              │
│  ❌ Sai:   Redis::hGetAll('laravel-database-review:votes:123') │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  QUY TẮC 2: KHI TÌM KEYS (Redis::keys)                      │
│  --> Kết quả trả về CÓ prefix                               │
│  --> Phải xử lý để bỏ prefix ra trước khi dùng             │
│                                                              │
│  $keys = Redis::keys('*');                                  │
│  // ["laravel-database-review:votes:123"]  <-- CÓ PREFIX    │
│                                                              │
│  $keyWithoutPrefix = Str::after($key, $prefix);            │
│  // "review:votes:123"  <-- BỎ PREFIX                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎓 PHẦN 7: VÍ DỤ THỰC TẾ HOÀN CHỈNH

### Tình huống: User vote cho một review

```php
// ============================================
// 1. USER CLICK UPVOTE TRÊN WEBSITE
// ============================================
Route::post('/reviews/{id}/vote', function($id) {
    // Controller nhận request
    $reviewId = $id; // "69035212a150066473044174"
    
    
    // ============================================
    // 2. LƯU VÀO REDIS (NHANH)
    // ============================================
    Redis::hIncrBy('review:votes:' . $reviewId, 'upvotes', 1);
    
    // Laravel xử lý:
    // - Key: "laravel-database-review:votes:69035212a150066473044174"
    // - Field: "upvotes"
    // - Tăng thêm: 1
    
    // Trong Redis giờ có:
    // Key: "laravel-database-review:votes:69035212a150066473044174"
    // Value: {"upvotes": "1"}
    
    return response()->json(['success' => true]);
});


// ============================================
// 3. SCHEDULE CHẠY SYNC (MỖI 5 PHÚT)
// ============================================
// File: app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('app:sync-review-votes-to-db')
             ->everyFiveMinutes();
}


// ============================================
// 4. COMMAND SYNC CHẠY
// ============================================
// File: app/Console/Commands/SyncReviewVotesToDB.php

// Bước 4.1: Tìm tất cả keys
$allKeys = Redis::keys('*');
// ["laravel-database-review:votes:69035212a150066473044174", ...]

// Bước 4.2: Lọc chỉ lấy vote keys
$prefix = "laravel-database-";
$voteKeys = array_filter($allKeys, function($key) use ($prefix) {
    return str_starts_with($key, $prefix . 'review:votes:');
});
// ["laravel-database-review:votes:69035212a150066473044174"]

// Bước 4.3: Xử lý từng key
foreach ($voteKeys as $fullKey) {
    // $fullKey = "laravel-database-review:votes:69035212a150066473044174"
    
    // Bỏ prefix
    $key = Str::after($fullKey, $prefix);
    // $key = "review:votes:69035212a150066473044174"
    
    // Lấy dữ liệu (Laravel tự thêm prefix)
    $votes = Redis::hGetAll($key);
    // {"upvotes": "1"}
    
    // Extract review ID
    $reviewId = str_replace('review:votes:', '', $key);
    // "69035212a150066473044174"
    
    // Cập nhật MongoDB
    $review = Review::find($reviewId);
    $review->increment('upvotes', (int) $votes['upvotes']);
    
    // Xóa khỏi Redis (Laravel tự thêm prefix)
    Redis::del($key);
}


// ============================================
// 5. KẾT QUẢ CUỐI CÙNG
// ============================================
MongoDB:
  reviews collection > document 69035212a150066473044174
    - upvotes: 3 (đã tăng từ 2 lên 3)
    - downvotes: 2

Redis:
  Key "laravel-database-review:votes:69035212a150066473044174" đã bị XÓA
```

---

## ⚠️ PHẦN 8: NHỮNG LỖI THƯỜNG GẶP

### Lỗi 1: Dùng key có prefix khi đọc/xóa

```php
// ❌ SAI
$key = "laravel-database-review:votes:123";
$data = Redis::hGetAll($key);  // Trả về [] (rỗng)

// ✅ ĐÚNG
$key = "review:votes:123";
$data = Redis::hGetAll($key);  // Trả về data
```

### Lỗi 2: Quên filter keys khi dùng Redis::keys('*')

```php
// ❌ SAI - Lấy tất cả keys kể cả cache, session
$keys = Redis::keys('*');
foreach ($keys as $key) {
    // Xử lý cả cache keys, session keys --> LỖI!
}

// ✅ ĐÚNG - Filter chỉ lấy vote keys
$keys = Redis::keys('*');
$voteKeys = array_filter($keys, function($key) {
    return str_starts_with($key, 'laravel-database-review:votes:');
});
foreach ($voteKeys as $key) {
    // Chỉ xử lý vote keys
}
```

### Lỗi 3: Dùng scan() không đúng cách

```php
// ❌ SAI - scan() không hoạt động tốt với prefix
$cursor = 0;
do {
    [$cursor, $keys] = Redis::scan($cursor, 'match', 'laravel-database-review:votes:*');
} while ($cursor != 0);
// Có thể bỏ sót keys!

// ✅ ĐÚNG - Dùng keys() và filter
$allKeys = Redis::keys('*');
$voteKeys = array_filter($allKeys, function($key) {
    return str_starts_with($key, 'laravel-database-review:votes:');
});
```

---

## 🎯 PHẦN 9: TÓM TẮT QUAN TRỌNG

### Điều cần nhớ:

1. **Prefix tự động**: Laravel TỰ ĐỘNG thêm prefix khi bạn lưu/đọc/xóa
2. **Redis::keys('*')**: Trả về keys ĐẦY ĐỦ prefix, cần xử lý trước khi dùng
3. **Bỏ prefix**: Dùng `Str::after($fullKey, $prefix)` để lấy key ngắn
4. **Dùng key ngắn**: Khi gọi `hGetAll()`, `del()`, dùng key KHÔNG có prefix

### Flow đúng:

```
1. Redis::keys('*')  
   --> Nhận: "laravel-database-review:votes:123"

2. Str::after($key, 'laravel-database-')  
   --> Nhận: "review:votes:123"

3. Redis::hGetAll('review:votes:123')  
   --> Laravel thêm prefix: "laravel-database-review:votes:123"
   --> Lấy được data ✅

4. Redis::del('review:votes:123')  
   --> Laravel thêm prefix: "laravel-database-review:votes:123"
   --> Xóa thành công ✅
```

---

## 📝 PHẦN 10: CHECKLIST KHI DEBUG

Khi gặp lỗi Redis, check các điểm sau:

- [ ] Prefix có đúng không? (`config('database.redis.options.prefix')`)
- [ ] Key đang dùng có prefix chưa? (Nếu có --> SAI!)
- [ ] Dùng `Redis::keys('*')` để xem tất cả keys trong Redis
- [ ] Dùng `Str::after()` để bỏ prefix
- [ ] Check log để xem key thực tế Laravel gửi xuống Redis
- [ ] Test với `Redis::hGetAll()` xem có lấy được data không

---

**✨ Hy vọng giải thích này giúp bạn hiểu rõ hơn về Redis Prefix trong Laravel!**
