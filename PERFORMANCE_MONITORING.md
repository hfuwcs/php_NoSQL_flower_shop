# Performance Monitoring Guide

## Đo lường Performance

Code đã được thêm timing cho các phần:

### 1. ReviewController::vote()
- **Redis Read**: Thời gian đọc current vote và pending votes
- **Redis Write**: Thời gian ghi vote mới
- **Code Processing**: Thời gian xử lý logic, validation

### 2. ProductController::index()
- **MongoDB Products Query**: Thời gian query products với filter
- **MongoDB Categories Query**: Thời gian query distinct categories
- **View Render**: Thời gian render view

### 3. ProductController::show()
- **Cache/MongoDB**: Thời gian lấy từ cache hoặc query MongoDB
- **View Render**: Thời gian render view

## Cách xem kết quả

### Option 1: Xem real-time
```powershell
.\watch-performance.ps1
```
Sau đó thực hiện các requests trên website

### Option 2: Phân tích logs
```powershell
.\analyze-performance.ps1
```

### Option 3: Xem raw logs
```powershell
Get-Content storage\logs\laravel.log -Tail 30 | Select-String "Performance"
```

## Phân tích kết quả

### Nếu **Redis Read/Write chậm** (>100ms):
- ✓ Kiểm tra Redis connection (localhost vs remote)
- ✓ Check Redis memory/CPU
- ✓ Network latency

### Nếu **MongoDB Query chậm** (>500ms):
- ✓ Thiếu index trên `category`, `price`
- ✓ Query không tối ưu
- ✓ Distinct() load toàn bộ documents

### Nếu **View Render chậm** (>300ms):
- ✓ N+1 queries trong view
- ✓ View quá phức tạp
- ✓ Blade compilation

### Nếu **Code Processing chậm** (>50ms):
- ✓ Logic phức tạp
- ✓ Nhiều operations không cần thiết

## Thresholds
- ⚠ Vote request > 500ms
- ⚠ Product index > 1500ms  
- ⚠ Product show > 1500ms
