# Add PHP to PATH - Run as Administrator
# Right-click this file → Run with PowerShell → Select "Yes" when prompted

Write-Host "Adding PHP to System PATH..." -ForegroundColor Green

$phpPath = "C:\php"
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")

if ($currentPath -notlike "*$phpPath*") {
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$phpPath", "Machine")
    Write-Host "✅ PHP added to PATH successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Please close and reopen Command Prompt/PowerShell for changes to take effect." -ForegroundColor Yellow
} else {
    Write-Host "✅ PHP is already in PATH!" -ForegroundColor Green
}

Write-Host ""
Write-Host "To test, open a NEW Command Prompt and run: php -v" -ForegroundColor Cyan
Write-Host ""
pause
