# Demo Startup Script for Flower Shop
# Run this before demo to warm-up the application

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  FLOWER SHOP - DEMO STARTUP SCRIPT" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check services
Write-Host "[1/5] Checking services..." -ForegroundColor Yellow

$mongoOk = (Test-NetConnection -ComputerName 127.0.0.1 -Port 27017 -WarningAction SilentlyContinue).TcpTestSucceeded
$redisOk = (Test-NetConnection -ComputerName 127.0.0.1 -Port 6380 -WarningAction SilentlyContinue).TcpTestSucceeded

if ($mongoOk) {
    Write-Host "      MongoDB (27017): OK" -ForegroundColor Green
} else {
    Write-Host "      MongoDB (27017): FAILED - Start Laragon first!" -ForegroundColor Red
    exit 1
}

if ($redisOk) {
    Write-Host "      Redis (6380): OK" -ForegroundColor Green
} else {
    Write-Host "      Redis (6380): FAILED - Start Laragon first!" -ForegroundColor Red
    exit 1
}

# Step 2: Optimize Laravel
Write-Host ""
Write-Host "[2/5] Optimizing Laravel..." -ForegroundColor Yellow
php artisan optimize 2>$null
Write-Host "      Done!" -ForegroundColor Green

# Step 3: Start server
Write-Host ""
Write-Host "[3/5] Starting Laravel server..." -ForegroundColor Yellow
Get-Process -Name "php" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Process -FilePath "php" -ArgumentList "artisan","serve","--port=8000" -WindowStyle Hidden
Start-Sleep -Seconds 3
Write-Host "      Server running at http://127.0.0.1:8000" -ForegroundColor Green

# Step 4: Warm-up
Write-Host ""
Write-Host "[4/5] Warming up cache & JIT..." -ForegroundColor Yellow

$urls = @(
    "http://127.0.0.1:8000/",
    "http://127.0.0.1:8000/",
    "http://127.0.0.1:8000/leaderboard"
)

foreach ($url in $urls) {
    try {
        $time = Measure-Command { Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 30 } 
        Write-Host "      $url - $([math]::Round($time.TotalMilliseconds))ms" -ForegroundColor Gray
    } catch {
        Write-Host "      $url - Error" -ForegroundColor Red
    }
}

# Extra warm-up
1..5 | ForEach-Object { 
    Invoke-WebRequest -Uri "http://127.0.0.1:8000/" -UseBasicParsing -TimeoutSec 30 | Out-Null
}
Write-Host "      JIT warm-up complete!" -ForegroundColor Green

# Step 5: Final test
Write-Host ""
Write-Host "[5/5] Final performance test..." -ForegroundColor Yellow
$finalTime = Measure-Command { Invoke-WebRequest -Uri "http://127.0.0.1:8000/" -UseBasicParsing }
Write-Host "      Homepage: $([math]::Round($finalTime.TotalMilliseconds))ms" -ForegroundColor Green

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  READY FOR DEMO!" -ForegroundColor Green
Write-Host "  Open: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
