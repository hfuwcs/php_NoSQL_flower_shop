Write-Host "`n=== PHP Configuration Check ===" -ForegroundColor Green

# Check PHP version
Write-Host "`n[PHP Version]" -ForegroundColor Cyan
php -v

# Check PHP-FPM status (if running)
Write-Host "`n[PHP Extensions]" -ForegroundColor Cyan
php -m | Select-String -Pattern "redis|mongodb|opcache"

# Check OPcache status
Write-Host "`n[OPcache Status]" -ForegroundColor Cyan
php -r "echo opcache_get_status() ? 'Enabled' : 'Disabled'; echo PHP_EOL;"

# Check memory limit
Write-Host "`n[Memory & Execution]" -ForegroundColor Cyan
php -r "echo 'Memory Limit: ' . ini_get('memory_limit') . PHP_EOL;"
php -r "echo 'Max Execution Time: ' . ini_get('max_execution_time') . 's' . PHP_EOL;"
php -r "echo 'Upload Max Filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"

Write-Host "`n[Recommendations]" -ForegroundColor Yellow
Write-Host "1. Enable OPcache for production (caches compiled PHP code)"
Write-Host "2. Use php artisan optimize (combines config/routes caching)"
Write-Host "3. Check if running php artisan serve (use nginx/Apache for production)`n"
