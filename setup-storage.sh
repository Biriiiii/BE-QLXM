#!/bin/bash
# Heroku post-deploy script để setup storage

echo "🔗 Setting up storage link..."
php artisan storage:link

echo "📁 Creating necessary directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Storage setup completed!"

echo "📋 Storage structure:"
ls -la storage/
ls -la public/ | grep storage