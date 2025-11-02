# Analyze Performance from Logs
Write-Host "`n=== Performance Analysis ===" -ForegroundColor Green
Write-Host "Analyzing last 50 performance logs...`n" -ForegroundColor Yellow

$logFile = "d:\HK7\School\NoSQL\Project\flower-shop\storage\logs\laravel.log"

# Get performance logs
$perfLogs = Select-String -Path $logFile -Pattern "Performance" | Select-Object -Last 50

if ($perfLogs.Count -eq 0) {
    Write-Host "No performance logs found. Make some requests first!" -ForegroundColor Red
    exit
}

# Analyze Vote Performance
Write-Host "=== Vote Performance ===" -ForegroundColor Cyan
$voteLogs = $perfLogs | Where-Object { $_ -match "Vote Performance" }
if ($voteLogs) {
    $voteLogs | ForEach-Object {
        if ($_ -match '"total_time_ms":(\d+\.?\d*).*"redis_read_ms":(\d+\.?\d*).*"redis_write_ms":(\d+\.?\d*)') {
            $total = [math]::Round([double]$matches[1], 2)
            $read = [math]::Round([double]$matches[2], 2)
            $write = [math]::Round([double]$matches[3], 2)
            $code = [math]::Round($total - $read - $write, 2)
            
            Write-Host "  Total: ${total}ms | Redis Read: ${read}ms | Redis Write: ${write}ms | Code: ${code}ms"
            
            if ($total -gt 500) {
                Write-Host "    ⚠ SLOW REQUEST!" -ForegroundColor Red
            }
        }
    }
} else {
    Write-Host "  No vote logs found"
}

# Analyze Product Performance
Write-Host "`n=== Product Index Performance ===" -ForegroundColor Magenta
$indexLogs = $perfLogs | Where-Object { $_ -match "Product Index Performance" }
if ($indexLogs) {
    $indexLogs | ForEach-Object {
        if ($_ -match '"total_time_ms":(\d+\.?\d*).*"mongo_products_query_ms":(\d+\.?\d*).*"mongo_categories_query_ms":(\d+\.?\d*)') {
            $total = [math]::Round([double]$matches[1], 2)
            $products = [math]::Round([double]$matches[2], 2)
            $categories = [math]::Round([double]$matches[3], 2)
            $view = [math]::Round($total - $products - $categories, 2)
            
            Write-Host "  Total: ${total}ms | Products Query: ${products}ms | Categories Query: ${categories}ms | View: ${view}ms"
            
            if ($total -gt 1500) {
                Write-Host "    ⚠ SLOW REQUEST!" -ForegroundColor Red
            }
        }
    }
} else {
    Write-Host "  No index logs found"
}

# Analyze Product Show Performance
Write-Host "`n=== Product Show Performance ===" -ForegroundColor Magenta
$showLogs = $perfLogs | Where-Object { $_ -match "Product Show Performance" }
if ($showLogs) {
    $showLogs | ForEach-Object {
        if ($_ -match '"total_time_ms":(\d+\.?\d*).*"cache_or_mongo_ms":(\d+\.?\d*).*"view_render_ms":(\d+\.?\d*)') {
            $total = [math]::Round([double]$matches[1], 2)
            $cache = [math]::Round([double]$matches[2], 2)
            $view = [math]::Round([double]$matches[3], 2)
            
            Write-Host "  Total: ${total}ms | Cache/Mongo: ${cache}ms | View: ${view}ms"
            
            if ($total -gt 1500) {
                Write-Host "    ⚠ SLOW REQUEST!" -ForegroundColor Red
            }
        }
    }
} else {
    Write-Host "  No show logs found"
}

Write-Host "`n=== Summary ===" -ForegroundColor Green
Write-Host "Total performance logs analyzed: $($perfLogs.Count)"
Write-Host "`nTip: Run .\watch-performance.ps1 to monitor in real-time`n"
