# 🚀 Hướng Dẫn Khởi Động Demo

## Trước khi demo (Checklist)

### Bước 1: Khởi động Laragon
```
1. Mở Laragon
2. Click "Start All" hoặc đảm bảo các services đang chạy:
   - Apache/Nginx (nếu dùng)
   - MongoDB ✓
   - Redis ✓
```

### Bước 2: Kiểm tra services (PowerShell)
```powershell
# Kiểm tra MongoDB
Test-NetConnection -ComputerName 127.0.0.1 -Port 27017

# Kiểm tra Redis  
Test-NetConnection -ComputerName 127.0.0.1 -Port 6380

# Cả 2 phải hiện: TcpTestSucceeded : True
```

### Bước 3: Khởi động Laravel
```powershell
cd D:\HK7\School\NoSQL\Project\flower-shop

# Clear và optimize cache
php artisan optimize

# Khởi động server
php artisan serve
```

### Bước 4: Warm-up cache (QUAN TRỌNG!)
Mở browser và truy cập các trang sau **1-2 lần** để warm-up JIT và cache:

1. http://127.0.0.1:8000 (Homepage)
2. http://127.0.0.1:8000/products/[any-product-id]
3. http://127.0.0.1:8000/leaderboard
4. http://127.0.0.1:8000/search?q=flower

**Sau warm-up, trang sẽ load trong ~100-150ms thay vì 500ms+**

---

## 🎯 Script tự động (Chạy 1 lần)

Lưu và chạy file `start-demo.ps1`:

```powershell
.\start-demo.ps1
```

---

## ⚡ Performance mong đợi

| Metric | Cold Start | Sau Warm-up |
|--------|------------|-------------|
| Total | ~500-700ms | ~100-150ms |
| Laravel Bootstrap | ~50ms | ~13ms |
| Application | ~200ms | ~100-136ms |

---

## 🔧 Troubleshooting

### Lỗi: "Connection refused" 
→ Laragon chưa start, mở Laragon và click "Start All"

### Lỗi: "Class not found"
```powershell
composer dump-autoload -o
php artisan optimize:clear
php artisan optimize
```

### Trang load chậm (>1s)
```powershell
# Restart PHP server
Get-Process -Name "php" | Stop-Process -Force
php artisan serve

# Warm-up lại bằng cách refresh trang 2-3 lần
```

### Xem logs nếu có lỗi
```powershell
Get-Content storage\logs\laravel.log -Tail 50
```

---

## 📝 Lưu ý quan trọng

1. **OPcache JIT đã được bật** - Lần đầu load sẽ chậm hơn (~2s) do JIT compile, các lần sau sẽ rất nhanh.

2. **validate_timestamps=0** - Nếu sửa code PHP, cần restart server:
   ```powershell
   Get-Process -Name "php" | Stop-Process -Force
   php artisan serve
   ```

3. **Dùng 127.0.0.1** thay vì localhost để tránh DNS lookup delay.

---

## 🎬 Checklist trước Demo

- [ ] Laragon đang chạy (MongoDB + Redis)
- [ ] Chạy `php artisan optimize`
- [ ] Chạy `php artisan serve`
- [ ] Warm-up các trang chính (2-3 lần mỗi trang)
- [ ] Test thử 1 flow hoàn chỉnh
- [ ] Mở Chrome DevTools > Network > Timings để show performance

Good luck với demo! 🍀
