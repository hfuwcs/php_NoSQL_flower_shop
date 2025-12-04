# Performance Test Script for Flower Shop
# Test Cold Start vs Warm-up

$baseUrl = "http://127.0.0.1:8000"

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url
    )
    try {
        $time = Measure-Command { 
            $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 120 -ErrorAction Stop
        }
        $status = $response.StatusCode
        return @{
            Name = $Name
            Time = [math]::Round($time.TotalMilliseconds)
            Status = $status
        }
    } catch {
        return @{
            Name = $Name
            Time = "ERROR"
            Status = $_.Exception.Message
        }
    }
}

Write-Host ""
Write-Host "=" * 70 -ForegroundColor Cyan
Write-Host "  FLOWER SHOP - PERFORMANCE TEST" -ForegroundColor Cyan
Write-Host "=" * 70 -ForegroundColor Cyan

# Stop existing PHP processes
Write-Host "`n[*] Stopping existing PHP processes..." -ForegroundColor Yellow
Get-Process -Name "php" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Start fresh server
Write-Host "[*] Starting fresh Laravel server..." -ForegroundColor Yellow
Start-Process -FilePath "php" -ArgumentList "artisan","serve","--port=8000" -WorkingDirectory "D:\HK7\School\NoSQL\Project\flower-shop" -WindowStyle Hidden
Start-Sleep -Seconds 3

# Get a product ID first
Write-Host "[*] Getting product ID..." -ForegroundColor Yellow
try {
    $html = (Invoke-WebRequest -Uri "$baseUrl/" -UseBasicParsing -TimeoutSec 120).Content
    if ($html -match 'href="/products/([a-f0-9]+)"') {
        $productId = $matches[1]
        Write-Host "    Found product: $productId" -ForegroundColor Gray
    }
} catch {
    Write-Host "    Could not get product ID" -ForegroundColor Red
}

# Restart server for clean cold start test
Write-Host "[*] Restarting server for cold start test..." -ForegroundColor Yellow
Get-Process -Name "php" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2
Start-Process -FilePath "php" -ArgumentList "artisan","serve","--port=8000" -WorkingDirectory "D:\HK7\School\NoSQL\Project\flower-shop" -WindowStyle Hidden
Start-Sleep -Seconds 3

Write-Host ""
Write-Host "-" * 70
Write-Host "  COLD START TEST (First request after server restart)" -ForegroundColor Yellow
Write-Host "-" * 70

$coldResults = @()

# Test 1: Homepage Cold
$r = Test-Endpoint -Name "Homepage" -Url "$baseUrl/"
$coldResults += $r
Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)

# Test 2: Product Detail Cold (first access)
if ($productId) {
    $r = Test-Endpoint -Name "Product Detail" -Url "$baseUrl/products/$productId"
    $coldResults += $r
    Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)
}

# Test 3: Leaderboard Cold
$r = Test-Endpoint -Name "Leaderboard" -Url "$baseUrl/leaderboard"
$coldResults += $r
Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)

# Test 4: Search Cold
$r = Test-Endpoint -Name "Search" -Url "$baseUrl/search?q=flower"
$coldResults += $r
Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)

Write-Host ""
Write-Host "-" * 70
Write-Host "  WARM-UP TEST (Subsequent requests - cache warmed)" -ForegroundColor Green
Write-Host "-" * 70

$warmResults = @()

# Run each test 3 times for warm-up average
foreach ($i in 1..3) {
    $r = Test-Endpoint -Name "Homepage (Run $i)" -Url "$baseUrl/"
    $warmResults += $r
    Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)
}

if ($productId) {
    foreach ($i in 1..3) {
        $r = Test-Endpoint -Name "Product Detail (Run $i)" -Url "$baseUrl/products/$productId"
        $warmResults += $r
        Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)
    }
}

Write-Host ""
Write-Host "-" * 70
Write-Host "  DIFFERENT PRODUCTS TEST (Cache miss for new products)" -ForegroundColor Magenta
Write-Host "-" * 70

# Get multiple product IDs
$html = (Invoke-WebRequest -Uri "$baseUrl/" -UseBasicParsing).Content
$productIds = [regex]::Matches($html, 'href="/products/([a-f0-9]+)"') | ForEach-Object { $_.Groups[1].Value } | Select-Object -Unique -First 5

foreach ($pid in $productIds) {
    $r = Test-Endpoint -Name "Product $($pid.Substring(0,8))..." -Url "$baseUrl/products/$pid"
    Write-Host ("  {0,-25} {1,8}ms  [{2}]" -f $r.Name, $r.Time, $r.Status)
}

Write-Host ""
Write-Host "=" * 70 -ForegroundColor Cyan
Write-Host "  TEST COMPLETED" -ForegroundColor Cyan
Write-Host "=" * 70 -ForegroundColor Cyan
Write-Host ""
