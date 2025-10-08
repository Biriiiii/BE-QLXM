# Script deploy nhanh len Heroku (PowerShell)
Write-Host "[DEPLOY] Dang chuan bi deploy len Heroku..." -ForegroundColor Green

# Kiem tra Git status
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "[GIT] Commit cac thay doi..." -ForegroundColor Yellow
    git add .
    git commit -m "Deploy to Heroku - $(Get-Date)"
}

# Deploy
Write-Host "[DEPLOY] Dang deploy..." -ForegroundColor Cyan
git push heroku main

# Chay migrations
Write-Host "[DB] Chay migrations..." -ForegroundColor Blue
heroku run php artisan migrate --force

# Tao storage link va kiem tra
Write-Host "[STORAGE] Tao storage link..." -ForegroundColor Magenta
heroku run php artisan storage:link
heroku run "ls -la public/ | grep storage"

# Clear cache
Write-Host "[CACHE] Clear cache..." -ForegroundColor DarkYellow
heroku run php artisan config:clear
heroku run php artisan cache:clear
heroku run php artisan route:clear
heroku run php artisan view:clear

Write-Host "[SUCCESS] Deploy hoan thanh!" -ForegroundColor Green
Write-Host "[INFO] Mo app: heroku open" -ForegroundColor White
Write-Host "[INFO] Xem logs: heroku logs --tail" -ForegroundColor White