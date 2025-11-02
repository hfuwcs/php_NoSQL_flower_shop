# Watch Performance Logs
Write-Host "=== Monitoring Performance Logs ===" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop`n" -ForegroundColor Yellow

Get-Content "d:\HK7\School\NoSQL\Project\flower-shop\storage\logs\laravel.log" -Wait | Where-Object {
    $_ -match "Performance"
} | ForEach-Object {
    if ($_ -match "Vote Performance") {
        Write-Host $_ -ForegroundColor Cyan
    } elseif ($_ -match "Product.*Performance") {
        Write-Host $_ -ForegroundColor Magenta
    } else {
        Write-Host $_
    }
}
