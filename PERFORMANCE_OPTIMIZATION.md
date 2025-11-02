# Performance Optimization Steps

## ⚠️ Vấn đề phát hiện:

### 1. **Xdebug đang bật** (làm chậm 2-10x)
```
PHP 8.3.16 with Xdebug v3.4.5
```

### 2. **OPcache không bật** (không cache compiled PHP)

### 3. **Đang dùng built-in server?** (`php artisan serve`)

---

## 🚀 Giải pháp:

### 1. Tắt Xdebug khi không debug

**File:** `D:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.ini`

Tìm dòng:
```ini
zend_extension=xdebug
```

Comment lại (thêm `;` ở đầu):
```ini
;zend_extension=xdebug
```

**Hoặc chỉ tắt khi không cần:**
```ini
xdebug.mode=off
```

### 2. Bật OPcache

Thêm vào `php.ini`:
```ini
[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 3. Tối ưu Laravel

```powershell
# Cache config, routes, views
php artisan optimize

# Clear cache nếu cần
php artisan optimize:clear
```

### 4. Restart PHP/Web Server

**Nếu dùng Laragon:**
- Menu → Apache → Restart
- Menu → PHP → Reload

**Hoặc restart terminal PHP:**
```powershell
# Stop current php artisan serve
# Start lại
php artisan serve
```

---

## 📊 Kết quả mong đợi:

### Trước:
```
Waiting: 750ms
Server-Timing: 277ms
Laravel Log: 24ms
```

### Sau (khi tắt Xdebug):
```
Waiting: 50-100ms     ← Giảm 80%
Server-Timing: 30-50ms ← Giảm 80%
Laravel Log: 24ms      ← Không đổi (đã nhanh)
```

---

## ✅ Checklist:

- [ ] Tắt Xdebug trong php.ini
- [ ] Bật OPcache trong php.ini  
- [ ] Chạy `php artisan optimize`
- [ ] Restart PHP/Web Server
- [ ] Test lại với Chrome DevTools
- [ ] Chạy `.\analyze-performance.ps1`

---

## 🎯 Tại sao chậm:

```
Total Waiting: 750ms
├─ Web Server: ~10ms
├─ PHP Bootstrap (với Xdebug): ~400ms  ← Xdebug overhead!
├─ Laravel Middleware: ~253ms           ← Session, Views compile
└─ Controller: ~24ms                    ← Đã tối ưu!
```

**Xdebug** profile mọi function call → chậm rất nhiều!
