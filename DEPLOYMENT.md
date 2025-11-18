# Deployment Guide - Narrative Game Platform V4.0

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 8.0+ or PostgreSQL 13+
- Redis 6.0+
- Node.js 18+
- NPM or Yarn
- AWS S3 account (for media storage)
- OpenAI API key
- Stripe account (for payments)

---

## 🚀 Installation Steps

### 1. Clone and Install Dependencies

```bash
# Clone repository
git clone <your-repo-url>
cd misterygame

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install
npm run build
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment Variables

Edit `.env` file:

```env
# Application
APP_NAME="Narrative Game Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=narrative_games
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

# AWS S3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# OpenAI
OPENAI_API_KEY=sk-your-key-here
OPENAI_MODEL=gpt-4-turbo-preview

# Stripe
STRIPE_KEY=pk_live_your-key
STRIPE_SECRET=sk_live_your-secret
STRIPE_WEBHOOK_SECRET=whsec_your-webhook-secret

# Admin
ADMIN_EMAIL=admin@yourdomain.com
ADMIN_PASSWORD=secure-password-here
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed
```

### 5. Storage Setup

```bash
# Link storage
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 6. Cache and Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 🔧 Queue Workers

The platform requires queue workers for async tasks (AI generation, multimedia processing):

```bash
# Start queue worker (production)
php artisan queue:work redis --queue=default,multimedia,ai-generation --tries=3 --timeout=300

# Using Supervisor (recommended)
```

### Supervisor Configuration

Create `/etc/supervisor/conf.d/narrative-games-worker.conf`:

```ini
[program:narrative-games-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=default,multimedia,ai-generation --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start narrative-games-worker:*
```

---

## 🌐 Web Server Configuration

### Nginx

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com;
    root /var/www/narrative-games/public;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 📊 Monitoring & Logging

### Log Files

```bash
# Application logs
tail -f storage/logs/laravel.log

# Worker logs
tail -f storage/logs/worker.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Health Check Endpoint

```bash
# Check API health
curl https://yourdomain.com/api/health
```

---

## 🔐 Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong `APP_KEY` (generated automatically)
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Configure CORS properly
- [ ] Set up firewall rules
- [ ] Use strong database passwords
- [ ] Enable Redis password authentication
- [ ] Restrict S3 bucket permissions
- [ ] Configure rate limiting
- [ ] Set up backups (database + files)
- [ ] Enable Stripe webhook signature verification
- [ ] Review and configure `security.php` settings

---

## 🗄️ Backup Strategy

### Database Backup

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p narrative_games > /backups/db_$DATE.sql
gzip /backups/db_$DATE.sql

# Keep only last 30 days
find /backups -name "db_*.sql.gz" -mtime +30 -delete
```

### Storage Backup

```bash
# Backup uploaded media
aws s3 sync s3://your-bucket /backups/media --delete
```

---

## 📈 Scaling Recommendations

### Horizontal Scaling

1. **Load Balancer**: Use nginx or AWS ALB
2. **Multiple App Servers**: Run identical instances
3. **Shared Sessions**: Use Redis for session storage
4. **Shared Cache**: Central Redis instance
5. **Queue Workers**: Scale separately

### Vertical Scaling

- **PHP-FPM**: Increase `pm.max_children`
- **Database**: Optimize queries, add indexes
- **Redis**: Increase memory allocation
- **Queue Workers**: Add more processes

---

## 🧪 Testing Deployment

```bash
# Run tests
php artisan test

# Test API endpoints
curl https://yourdomain.com/api/health
curl https://yourdomain.com/api/games

# Test authentication
curl -X POST https://yourdomain.com/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@test.com","password":"password","password_confirmation":"password"}'
```

---

## 🔄 Updates & Maintenance

### Updating Code

```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and recache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart narrative-games-worker:*

# Restart PHP-FPM (if needed)
sudo systemctl restart php8.1-fpm
```

### Scheduled Tasks

Add to crontab:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🐛 Troubleshooting

### Common Issues

**Queue not processing:**
```bash
# Check queue workers
sudo supervisorctl status narrative-games-worker:*

# Restart workers
sudo supervisorctl restart narrative-games-worker:*
```

**Permission errors:**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Database connection failed:**
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

**Redis connection failed:**
```bash
# Test Redis
php artisan tinker
>>> Cache::put('test', 'value');
>>> Cache::get('test');
```

---

## 📞 Support

For issues or questions:
- GitHub Issues: [repository]/issues
- Email: support@yourdomain.com
- Documentation: https://docs.yourdomain.com

---

## ✅ Post-Deployment Checklist

- [ ] All environment variables configured
- [ ] Database migrations completed
- [ ] Admin user created
- [ ] Queue workers running
- [ ] SSL certificate installed
- [ ] CORS configured
- [ ] S3 bucket accessible
- [ ] OpenAI API working
- [ ] Stripe webhooks configured
- [ ] Monitoring set up
- [ ] Backups configured
- [ ] Logs rotating properly
- [ ] Health check returns OK
- [ ] Sample games seeded
- [ ] Documentation updated

---

**Deployment Date:** ___________
**Deployed By:** ___________
**Version:** V4.0
