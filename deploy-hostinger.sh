#!/bin/bash

# Hostinger Post-Deployment Script
# Run this script ON HOSTINGER via SSH after uploading files

echo "🚀 Setting up Laravel on Hostinger..."

# Navigate to backend directory
cd ~/public_html/backend

# 1. Copy environment file
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    cp .env.hostinger .env
    echo "⚠️  IMPORTANT: Edit .env with your database credentials!"
    echo "Run: nano .env"
    exit 1
fi

# 2. Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force

# 3. Clear and cache config
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# 5. Seed database (optional)
read -p "Do you want to seed the database? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --force
fi

# 6. Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# 7. Set permissions
echo "🔒 Setting permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📋 Final checklist:"
echo "1. ✓ Application key generated"
echo "2. ✓ Database migrated"
echo "3. ✓ Storage linked"
echo "4. ✓ Permissions set"
echo ""
echo "🌐 Next steps:"
echo "1. Set document root to: public_html/backend/public"
echo "2. Enable SSL certificate in Hostinger panel"
echo "3. Test your API: https://yourdomain.com/api/health"
echo ""
echo "🎉 Your Laravel app is live!"
