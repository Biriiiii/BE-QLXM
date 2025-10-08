# Hướng dẫn Deploy Laravel lên Heroku

## Chuẩn bị

1. **Cài đặt Heroku CLI**
   - Tải từ: https://devcenter.heroku.com/articles/heroku-cli
   - Đăng nhập: `heroku login`

2. **Tạo ứng dụng Heroku**
   ```bash
   heroku create your-app-name
   ```

## Cấu hình Environment Variables

Thiết lập các biến môi trường trên Heroku:

```bash
# Cơ bản
heroku config:set APP_NAME="BE-QLXM"
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_URL=https://your-app-name.herokuapp.com

# Tạo APP_KEY
php artisan key:generate --show
heroku config:set APP_KEY=base64:your-generated-key

# Database (Heroku sẽ tự động cấu hình PostgreSQL)
heroku addons:create heroku-postgresql:essential-0

# Cache & Session
heroku config:set CACHE_DRIVER=database
heroku config:set SESSION_DRIVER=database
heroku config:set QUEUE_CONNECTION=database

# Log
heroku config:set LOG_CHANNEL=errorlog
heroku config:set LOG_DEPRECATIONS_CHANNEL=null
heroku config:set LOG_LEVEL=debug
```

## Storage trên Heroku

**QUAN TRỌNG**: Heroku có filesystem tạm thời, nghĩa là:
- Mọi file upload sẽ bị mất khi app restart
- Storage link vẫn hoạt động nhưng chỉ trong phiên làm việc

### Giải pháp cho Production:

1. **Sử dụng AWS S3** (Khuyến nghị):
   ```bash
   heroku config:set FILESYSTEM_DISK=s3
   heroku config:set AWS_ACCESS_KEY_ID=your-access-key
   heroku config:set AWS_SECRET_ACCESS_KEY=your-secret-key
   heroku config:set AWS_DEFAULT_REGION=us-east-1
   heroku config:set AWS_BUCKET=your-bucket-name
   ```

2. **Hoặc sử dụng Cloudinary**:
   ```bash
   heroku addons:create cloudinary:starter
   ```

## Deploy

1. **Commit code**:
   ```bash
   git add .
   git commit -m "Prepare for Heroku deployment"
   ```

2. **Deploy**:
   ```bash
   git push heroku main
   ```

3. **Chạy migration**:
   ```bash
   heroku run php artisan migrate --force
   ```

4. **Tạo storage link**:
   ```bash
   heroku run php artisan storage:link
   ```

## Kiểm tra

- Mở app: `heroku open`
- Xem log: `heroku logs --tail`
- Kết nối database: `heroku pg:psql`

## Lưu ý quan trọng

1. **Storage Link**: Sẽ được tạo tự động qua Procfile, nhưng files sẽ mất khi restart
2. **Database**: Sử dụng PostgreSQL thay vì MySQL
3. **Files tĩnh**: Đặt trong `public/` để truy cập trực tiếp
4. **Environment**: Luôn set `APP_ENV=production` và `APP_DEBUG=false`

## Troubleshooting

- Nếu gặp lỗi 500: `heroku logs --tail`
- Nếu storage link lỗi: `heroku run php artisan storage:link`
- Nếu migration lỗi: `heroku run php artisan migrate:fresh --force`