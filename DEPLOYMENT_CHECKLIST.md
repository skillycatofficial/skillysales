# Hostinger Deployment Checklist

## Before Upload

- [ ] Run `./deploy-prepare.sh` to create deployment package
- [ ] Get Pusher credentials from [pusher.com](https://pusher.com)
- [ ] Get Google OAuth credentials from Google Cloud Console
- [ ] Note your Hostinger database credentials

## Upload to Hostinger

- [ ] Login to Hostinger control panel
- [ ] Go to File Manager
- [ ] Upload `backend_hostinger_XXXXXX.zip`
- [ ] Extract to `public_html/backend`

## Configure Hostinger

### 1. Database Setup
- [ ] Create MySQL database
- [ ] Create database user
- [ ] Assign user to database
- [ ] Note: DB name, username, password

### 2. Environment Configuration
```bash
# SSH into Hostinger
ssh username@yourdomain.com

# Navigate to backend
cd ~/public_html/backend

# Copy and edit .env
cp .env.hostinger .env
nano .env
```

**Update these values in `.env`:**
```env
APP_URL=https://yourdomain.com
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
PUSHER_APP_ID=your_pusher_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
GOOGLE_CLIENT_ID=your_google_id
GOOGLE_CLIENT_SECRET=your_google_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/api/auth/google/callback
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

### 3. Run Deployment Script
```bash
chmod +x deploy-hostinger.sh
./deploy-hostinger.sh
```

### 4. Set Document Root
- [ ] Go to Hostinger → Advanced → PHP Configuration
- [ ] Set document root: `public_html/backend/public`
- [ ] Save changes

### 5. Enable SSL
- [ ] Go to Hostinger → SSL
- [ ] Enable Free SSL (Let's Encrypt)
- [ ] Wait 5-10 minutes for activation

## Testing

### Test API Endpoints
```bash
# Health check
curl https://yourdomain.com/api/health

# Get cars
curl https://yourdomain.com/api/cars

# Test auth
curl https://yourdomain.com/api/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Check Logs
```bash
# SSH into server
tail -f ~/public_html/backend/storage/logs/laravel.log
```

## Troubleshooting

### 500 Error
```bash
# Check permissions
cd ~/public_html/backend
chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Database Connection Error
- Verify `.env` database credentials
- Check database exists in Hostinger panel
- Ensure user has permissions

### CORS Issues
- Verify `APP_URL` in `.env`
- Check `SANCTUM_STATEFUL_DOMAINS`
- Clear config cache: `php artisan config:clear`

## Post-Deployment

- [ ] Test all API endpoints
- [ ] Test Google OAuth login
- [ ] Test file uploads
- [ ] Monitor error logs
- [ ] Set up backups in Hostinger

## Maintenance

### Update Application
```bash
# SSH into server
cd ~/public_html/backend

# Pull latest changes (if using git)
git pull origin main

# Update dependencies
composer install --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan optimize
```

### Monitor Performance
- Check Hostinger resource usage
- Monitor database size
- Review error logs regularly

## Support

- Hostinger Support: [support.hostinger.com](https://support.hostinger.com)
- Laravel Docs: [laravel.com/docs](https://laravel.com/docs)
- Pusher Docs: [pusher.com/docs](https://pusher.com/docs)
