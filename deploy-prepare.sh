#!/bin/bash

# Hostinger Deployment Script
# Run this script BEFORE uploading to Hostinger

echo "🚀 Preparing Laravel backend for Hostinger deployment..."

# Navigate to backend directory
cd "$(dirname "$0")"

# 1. Install production dependencies
echo "📦 Installing production dependencies..."
composer install --optimize-autoloader --no-dev

# 2. Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 3. Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Create production .env template
echo "📝 Creating production .env template..."
if [ ! -f ".env.hostinger" ]; then
    cp env.production.example .env.hostinger
    echo "✅ Created .env.hostinger - Edit this file with your Hostinger credentials"
else
    echo "⚠️  .env.hostinger already exists, skipping..."
fi

# 5. Set proper permissions
echo "🔒 Setting file permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

# 6. Create deployment package
echo "📦 Creating deployment package..."
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
PACKAGE_NAME="backend_hostinger_${TIMESTAMP}.zip"

# Exclude unnecessary files
zip -r "../${PACKAGE_NAME}" . \
    -x "*.git*" \
    -x "*node_modules*" \
    -x "*.env" \
    -x "*.env.local" \
    -x "*storage/logs/*" \
    -x "*storage/framework/cache/*" \
    -x "*storage/framework/sessions/*" \
    -x "*storage/framework/views/*" \
    -x "*tests/*" \
    -x "*.DS_Store"

echo ""
echo "✅ Deployment package created: ${PACKAGE_NAME}"
echo ""
echo "📋 Next steps:"
echo "1. Upload ${PACKAGE_NAME} to Hostinger File Manager"
echo "2. Extract in public_html directory"
echo "3. Copy .env.hostinger to .env and edit with your credentials"
echo "4. Run: php artisan key:generate"
echo "5. Run: php artisan migrate --force"
echo "6. Run: php artisan storage:link"
echo "7. Set document root to: public_html/backend/public"
echo ""
echo "🎉 Ready for deployment!"
