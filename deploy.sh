#!/bin/bash

# Script deploy nhanh lên Heroku
echo "🚀 Đang chuẩn bị deploy lên Heroku..."

# Kiểm tra Git status
if [[ -n $(git status --porcelain) ]]; then
    echo "📝 Commit các thay đổi..."
    git add .
    git commit -m "Deploy to Heroku - $(date)"
fi

# Deploy
echo "🌟 Đang deploy..."
git push heroku main

# Chạy migrations
echo "🗄️ Chạy migrations..."
heroku run php artisan migrate --force

# Tạo storage link
echo "🔗 Tạo storage link..."
heroku run php artisan storage:link

# Clear cache
echo "🧹 Clear cache..."
heroku run php artisan config:clear
heroku run php artisan cache:clear
heroku run php artisan route:clear
heroku run php artisan view:clear

echo "✅ Deploy hoàn thành!"
echo "🌐 Mở app: heroku open"
echo "📋 Xem logs: heroku logs --tail"