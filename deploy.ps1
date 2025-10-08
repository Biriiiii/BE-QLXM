# Script deploy nhanh lên Heroku (PowerShell)
Write-Host "🚀 Đang chuẩn bị deploy lên Heroku..." -ForegroundColor Green

# Kiểm tra Git status
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "📝 Commit các thay đổi..." -ForegroundColor Yellow
    git add .
    git commit -m "Deploy to Heroku - $(Get-Date)"
}

# Deploy
Write-Host "🌟 Đang deploy..." -ForegroundColor Cyan
git push heroku main

# Chạy migrations
Write-Host "🗄️ Chạy migrations..." -ForegroundColor Blue
heroku run php artisan migrate --force

# Tạo storage link
Write-Host "🔗 Tạo storage link..." -ForegroundColor Magenta
heroku run php artisan storage:link

# Clear cache
Write-Host "🧹 Clear cache..." -ForegroundColor DarkYellow
heroku run php artisan config:clear
heroku run php artisan cache:clear
heroku run php artisan route:clear
heroku run php artisan view:clear

Write-Host "✅ Deploy hoàn thành!" -ForegroundColor Green
Write-Host "🌐 Mở app: heroku open" -ForegroundColor White
Write-Host "📋 Xem logs: heroku logs --tail" -ForegroundColor White